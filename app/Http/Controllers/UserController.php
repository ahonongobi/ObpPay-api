<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $path = $request->file('photo')->store('profiles', 'public');

        $user = $request->user();
        $user->avatar = $path;
        $user->save();

        return response()->json([
            'message' => 'Photo updated',
            'avatar_url' => asset('storage/' . $path)
        ]);
    }
}
