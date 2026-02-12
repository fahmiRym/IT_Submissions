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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        // Handle photo removal
        if ($request->filled('remove_photo') && $request->remove_photo == '1') {
            if ($user->photo && file_exists(public_path('profile_photos/' . $user->photo))) {
                unlink(public_path('profile_photos/' . $user->photo));
            }
            $user->photo = null;
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo && file_exists(public_path('profile_photos/' . $user->photo))) {
                unlink(public_path('profile_photos/' . $user->photo));
            }

            // Create directory if not exists
            if (!file_exists(public_path('profile_photos'))) {
                mkdir(public_path('profile_photos'), 0755, true);
            }

            // Upload new photo
            $file = $request->file('photo');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('profile_photos'), $filename);
            $user->photo = $filename;
        }

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
