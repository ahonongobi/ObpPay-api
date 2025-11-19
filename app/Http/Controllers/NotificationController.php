<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifs = Notification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        //return response()->json($notifs);
        return response()->json([
            'notifications' => $notifs,
        ]);
    }

    public function markRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(["success" => true]);
    }
}
