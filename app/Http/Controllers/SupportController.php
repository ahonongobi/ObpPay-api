<?php

namespace App\Http\Controllers;

use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = $request->user();

        // Send email
        Mail::raw(
            "Message de support envoyé par {$user->full_name} ({$user->phone}) :\n\n" .
                $request->message,
            function ($mail) {
                $mail->to('support@obppay.com')
                    ->subject('📩 Nouveau message de support');
            }
        );

        SupportMessage::create([
            'user_id' => $user->id,
            'message' => $request->message,
        ]);


        return response()->json([
            "message" => "Message envoyé avec succès"
        ]);
    }
}
