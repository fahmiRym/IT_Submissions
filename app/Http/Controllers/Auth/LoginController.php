<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * TAMPILKAN FORM LOGIN
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * PROSES LOGIN
     */
    public function loginProcess(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'username' => 'Username atau password salah',
            ]);
        }

        $request->session()->regenerate();

        // 🔁 REDIRECT BERDASARKAN ROLE
        if (auth()->user()->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
