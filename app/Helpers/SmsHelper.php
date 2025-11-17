<?php

namespace App\Helpers;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;


class SmsHelper
{
    public static function sendOtp($phone, $code)
    {
        try {
            $client = new Client(
                env('TWILIO_SID'),
                env('TWILIO_AUTH_TOKEN')
            );

            $client->messages->create($phone, [
                'from' => env('TWILIO_FROM'),
                'body' => "Votre code OTP est : $code"
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("SMS ERROR: " . $e->getMessage());
            return false;
        }
    }
}
