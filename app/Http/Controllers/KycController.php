<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KycController extends Controller
{
    public function uploadDocument(Request $request)
    {

        try {
        $request->validate([
            'type' => 'required|in:id_card,passport,selfie',
            'file' => 'required|image|max:4096',
        ]);

        $user = $request->user();

        // Store file
        $path = $request->file('file')->store("kyc/$user->id", 'public');

        // Save in DB
        $doc = \App\Models\Kyc::updateOrCreate(
            ['user_id' => $user->id, 'type' => $request->type],
            [
                'file_path' => $path,
                'status' => 'pending',
            ]
        );

        return response()->json([
            'success' => true,
            'document' => $doc,
        ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function getStatus(Request $request)
    {
        $docs = $request->user()->kyc()->get();

        return response()->json([
            'id_card' => $docs->where('type', 'id_card')->first(),
            'passport' => $docs->where('type', 'passport')->first(),
            'selfie' => $docs->where('type', 'selfie')->first(),
        ]);
    }


    public function submitKyc(Request $request)
    {
        $user = $request->user();

        $docs = $user->kyc()->get();

        if ($docs->count() < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Tous les documents ne sont pas encore téléchargés.'
            ], 422);
        }

        // On met "pending review"
        $user->kyc_status = "pending_review";
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'KYC soumis avec succès !',
        ]);
    }
}
