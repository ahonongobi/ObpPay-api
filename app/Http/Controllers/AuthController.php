<?php

namespace App\Http\Controllers;

use App\Models\otps;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Si un OTP est fourni → finaliser l'inscription
        if ($request->has('otp')) {
            return $this->completeRegistration($request);
        }

        // Sinon → début de l'inscription (envoi OTP)
        return $this->startRegistration($request);
    }

    private function startRegistration(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20|unique:users,phone|unique:pending_registrations,phone',
            'password' => 'required|string|min:6',
        ]);

        // enregistrer temporairement
        PendingRegistration::create([
            'name'     => $data['name'],
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        // générer OTP  4 chiffres 
        $otp = rand(1000, 9999);

        otps::create([
            'phone'      => $data['phone'],
            'code'       => $otp,
            'expires_at' => now()->addMinutes(3), // expire dans 3 min
        ]);

        return response()->json([
            'message' => 'OTP envoyé (mode dev).',
            'otp'     => $otp,  // visible pour test
            'otp_required' => true
        ]);
    }

    private function completeRegistration(Request $request)
    {
        $data = $request->validate([

            'phone' => 'required|string',
            'otp'   => 'required|string',
            'obp_id' => 'required|string|unique:users,obp_id',

        ]);

        // vérifier otp 
        $otp = otps::where('phone', $data['phone'])
            ->where('code', $data['otp'])
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'OTP invalide ou expiré'], 422);
        }

        $otp->update(['used' => true]);

        // récupérer pending registration
        $pending = PendingRegistration::where('phone', $data['phone'])->first();

        if (!$pending) {
            return response()->json(['message' => 'Enregistrement non trouvé'], 404);
        }

        // créer user
        $user = User::create([
            'name'     => $pending->name,
            'phone'    => $pending->phone,
            'password' => $pending->password,
            //'obp_id'   => $this->generateObpId(),
            'obp_id'   => $data['obp_id'],
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        $pending->delete();

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
            'message' => 'Compte créé avec succès.'
        ]);
    }



    private function generateObpId(): string
    {
        // Simple: 04-XXX-XXX
        $random = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        return '04-' . substr($random, 0, 3) . '-' . substr($random, 3, 3);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $data['phone'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Identifiants invalides.'],
            ]);
        }

        $pointsAdded = add_score($user, 1, "login");

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
            'points_added' => $pointsAdded,

        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('wallet'));
        // this mean

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }


    public function findByObp($obp_id)
    {
        $user = \App\Models\User::where('obp_id', $obp_id)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable'], 404);
        }

        return response()->json([
            'name' => $user->name,
            'obp_id' => $user->obp_id,
        ]);
    }
}
