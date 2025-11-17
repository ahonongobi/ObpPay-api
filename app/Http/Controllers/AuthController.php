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
use App\Helpers\SmsHelper;
use Illuminate\Validation\Rule;

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

        

        // générer OTP  4 chiffres 
        //$otp = rand(1000, 9999);
        $otp = rand(1000, 9999);
        $smsStatus = SmsHelper::sendOtp($data['phone'], $otp);

        if (!$smsStatus) {
            return response()->json([
                'message' => 'Impossible d’envoyer l\'OTP pour le moment.'
            ], 500);
        }

        // enregistrer temporairement
        PendingRegistration::create([
            'name'     => $data['name'],
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);
        // enregistrer OTP


        //L’utilisateur peut demander 2 OTP maximum par période (ex : 5 minutes).
        //Si le système détecte plus de 2 demandes, il bloque.
         // Message clair → "Usage suspect détecté. Veuillez réessayer plus tard."
        //Parfait pour éviter spam + bots.

        $otpCount = otps::where('phone', $data['phone'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($otpCount >= 2) {
            return response()->json([
                'message' => 'Usage suspect détecté. Veuillez réessayer plus tard.'
            ], 429);
        }

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
            'card_cvv' => str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT),
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


    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update([
            'name'  => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
        ]);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user'    => $user->fresh('wallet'),
        ]);
    }


    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Mot de passe actuel incorrect.',
            ], 422);
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès.',
        ]);
    }
}
