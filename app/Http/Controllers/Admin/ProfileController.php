<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $stats = [
            'total'   => \App\Models\Arsip::where('admin_id', $user->id)->count(),
            'pending' => \App\Models\Arsip::where('admin_id', $user->id)->where('status', 'Check')->count(),
            'process' => \App\Models\Arsip::where('admin_id', $user->id)->where('status', 'Process')->count(),
            'done'    => \App\Models\Arsip::where('admin_id', $user->id)->where('status', 'Done')->count(),
        ];

        return view('admin.profile.index', [
            'user' => $user,
            'stats' => $stats
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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

        $user->name  = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success','Profil berhasil diperbarui');
    }
}
