<?php

use Illuminate\Support\Facades\Http;
use Google\Client;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
class FirebaseService
{

    private static function getClient()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/obppay-b5336-firebase-adminsdk-fbsvc-4ef47d49ff.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        return $client;
    }
    public static function sendPush($fcmToken, $title, $body)
    {
        if (!$fcmToken) return;

        Http::withHeaders([
            'Authorization' => 'key=' . env('FIREBASE_SERVER_KEY'),
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
        ]);
    }






    //////////////// test function to get access token ////////////////////
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
