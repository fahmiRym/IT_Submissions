<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * PROSES LOGIN — username + password only (NIK/SSO tidak dipakai).
     */
    public function loginProcess(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $credentials = [
            'username' => trim($request->input('username')),
            'password' => $request->input('password'),
        ];

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->withInput($request->only('username'));
        }

        // 🛡️ CEK STATUS AKTIF
        if (!auth()->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors([
                'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Superadmin.',
            ]);
        }

        $request->session()->regenerate();

        // 📌 TRACK LAST LOGIN
        $u = auth()->user();
        $u->last_login_at = now();
        $u->last_login_ip = $request->ip();
        $u->login_count = (int) ($u->login_count ?? 0) + 1;
        $u->saveQuietly();

        // 🔁 REDIRECT
        if ($u->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
