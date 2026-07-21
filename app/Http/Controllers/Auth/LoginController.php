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
     * PROSES LOGIN — accept Username ATAU NIK.
     * Auto-detect: kalau input semua digit → cari by employee_id, selain itu by username.
     */
    public function loginProcess(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $login = trim($request->input('username'));
        $field = ctype_digit($login) ? 'employee_id' : 'username';

        $credentials = [
            $field    => $login,
            'password' => $request->input('password'),
        ];

        if (!Auth::attempt($credentials)) {
            // Fallback: kalau pakai username gagal, coba sebagai employee_id (atau sebaliknya)
            $altField = $field === 'username' ? 'employee_id' : 'username';
            $altCreds = [$altField => $login, 'password' => $request->input('password')];
            if (!Auth::attempt($altCreds)) {
                return back()->withErrors([
                    'username' => 'Username/NIK atau password salah.',
                ])->withInput($request->only('username'));
            }
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
        // - Superadmin: langsung ke dashboard, no middleware redirects
        // - Lainnya: middleware EnsureLinkedToNik + ForceChangePassword akan handle
        if ($u->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        // Order check di sini juga (selain middleware) untuk first-time UX
        if (empty($u->employee_id)) {
            return redirect()->route('auth.link-nik')
                ->with('info', 'Sebelum lanjut, masukkan NIK karyawan Anda untuk verifikasi.');
        }
        if ($u->must_change_password) {
            return redirect()->route('auth.change-password')
                ->with('info', 'Demi keamanan, silakan ganti password default Anda.');
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
