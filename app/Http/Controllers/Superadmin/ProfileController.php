<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * =========================
     * TAMPILKAN PROFILE
     * =========================
     */
    public function index()
    {
        $stats = [
            'total_users'   => \App\Models\User::count(),
            'total_arsip'   => \App\Models\Arsip::count(),
            'pending'       => \App\Models\Arsip::where('status', 'Check')->count(),
            'process'       => \App\Models\Arsip::where('status', 'Process')->count(),
            'done'          => \App\Models\Arsip::where('status', 'Done')->count(),
            'departments'   => \App\Models\Department::count(),
        ];

        return view('superadmin.profile.index', [
            'user' => Auth::user(),
            'stats' => $stats
        ]);
    }

    /**
     * =========================
     * UPDATE PROFILE
     * =========================
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        // update data dasar
        $user->name  = $request->name;
        $user->email = $request->email;

        // update password (jika diisi)
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profile berhasil diperbarui');
    }
}
