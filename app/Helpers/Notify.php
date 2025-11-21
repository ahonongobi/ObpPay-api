<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Services\FirebaseService;
use FirebaseService as GlobalFirebaseService;

class Notify
{
    public static function send($user, $type, $title, $message)
    {
        // 1. Notification DB
        Notification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
        ]);

        // 2. Push FCM
        GlobalFirebaseService::sendPush($user->fcm_token, $title, $message);
    }
}
