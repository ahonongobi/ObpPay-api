<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = (new Factory)
            ->withServiceAccount(config('firebase.projects.app.credentials'))
            ->createMessaging();
    }

    public function sendToToken($token, $title, $body, $data = [])
    {
        $notification = Notification::create($title, $body);

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification)
            ->withData($data);

        return $this->messaging->send($message);
    }
}
