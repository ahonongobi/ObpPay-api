<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Otp;
use App\Models\otps;
use Carbon\Carbon;

class OtpsController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        // Générer un OTP 4 chiffres
        $code = rand(1000, 9999);

        // Sauvegarde
        otps::create([
            'phone'      => $request->phone,
            'code'       => $code,
            'expires_at' => Carbon::now()->addMinutes(3)
        ]);

        return response()->json([
            'message' => 'OTP envoyé (mode développement).',
            'otp'     => $code,   // On retourne le code ici pour test
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code'  => 'required|string',
        ]);

        $otp = otps::where('phone', $request->phone)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return response()->json([
                'message' => 'OTP invalide ou expiré.'
            ], 422);
        }

        // Marquer comme utilisé
        $otp->update(['used' => true]);

        return response()->json([
            'message' => 'OTP vérifié avec succès.'
        ]);
    }
}
