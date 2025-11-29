<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function admins()
    {
        // Only return users where role is admin or superadmin
        $admins = User::whereIn('role', ['admin', 'superadmin'])->paginate(10);

        return view('admin.settings.admins', compact('admins'));
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,superadmin',
        ]);
   // debug test here
    //dd($request->all());
        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);



        return back()->with('success', 'Nouvel admin créé avec succès.');
    }

    public function deleteAdmin($id)
    {
        if ($id == auth()->id()) {
            return back()->with('error', "Vous ne pouvez pas vous supprimer vous-même.");
        }

        User::where('id', $id)->delete();

        return back()->with('success', 'Admin supprimé.');
    }

    public function index()
    {
        return view('admin.settings.index');
    }

    
}
