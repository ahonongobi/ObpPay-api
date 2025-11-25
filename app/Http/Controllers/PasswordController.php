<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable'], 404);
        }

        // Générer OTP 4 chiffres
        $otp = rand(1000, 9999);

        // Supprimer anciens OTP
        DB::table('password_resets')->where('phone', $request->phone)->delete();

        // Enregistrer le nouvel OTP
        DB::table('password_resets')->insert([
            'phone' => $request->phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Envoyer OTP via notification
        $firebase = new \App\Services\FirebaseService();
        if ($user->fcm_token) {
            $firebase->sendToToken(
                $user->fcm_token,
                "Code de réinitialisation",
                "Votre code est : $otp",
                ["screen" => "otp"]
            );
        }

        return response()->json([
            'message' => 'OTP envoyé',
        ]);
    }

    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|string',
        ]);

        $reset = DB::table('password_resets')
            ->where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'Code OTP invalide'], 400);
        }

        if (now()->greaterThan($reset->expires_at)) {
            return response()->json(['message' => 'OTP expiré'], 400);
        }

        return response()->json([
            'message' => 'OTP valide',
        ]);
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate(['phone' => 'required']);

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable'], 404);
        }

        $otp = rand(1000, 9999);

        // supprimer anciens OTP
        DB::table('password_resets')
            ->where('phone', $request->phone)
            ->delete();

        DB::table('password_resets')->insert([
            'phone'      => $request->phone,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => "OTP envoyé",
            'otp' => $otp   //  enlever en production
        ]);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone'    => 'required',
            'otp'      => 'required',
            'password' => 'required|min:6',
        ]);

        // Vérifier OTP
        $reset = DB::table('password_resets')
            ->where('phone', $request->phone)
            ->where('otp',  $request->otp)
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'OTP invalide'], 400);
        }

        if (now()->greaterThan($reset->expires_at)) {
            return response()->json(['message' => 'OTP expiré'], 400);
        }

        // Modifier le mot de passe
        User::where('phone', $request->phone)->update([
            'password' => Hash::make($request->password),
        ]);

        // Supprimer OTP
        DB::table('password_resets')
            ->where('phone', $request->phone)
            ->delete();

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès']);
    }
}
