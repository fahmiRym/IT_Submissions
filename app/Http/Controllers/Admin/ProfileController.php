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
            'password' => 'nullable|min:6|confirmed'
        ]);

        $user = auth()->user();
        $user->name  = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success','Profil berhasil diperbarui');
    }
}
