<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt([
            'phone' => $credentials['phone'],
            'password' => $credentials['password'],
            'is_admin' => true,
        ])) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'phone' => 'Incorrect credentials or not an admin.',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
