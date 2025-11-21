<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;

use Illuminate\Http\Request;

class FCMTestController extends Controller
{
    public function send()
    {
        $token = request('token');

        $firebase = new FirebaseService();

        $firebase->sendToToken(
            $token,
            "ObpPay Test 🔥",
            "La notification fonctionne parfaitement !",
            ["screen" => "dashboard"]
        );

        return response()->json(["success" => true]);
    }
}
