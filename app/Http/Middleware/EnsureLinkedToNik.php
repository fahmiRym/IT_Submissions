<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Force user lama (legacy, no employee_id) untuk input NIK dulu sebelum akses
 * fitur apa pun. Superadmin bypass.
 */
class EnsureLinkedToNik
{
    public function handle(Request $request, Closure $next)
    {
        $u = auth()->user();
        if (!$u) return $next($request);

        if ($u->role === 'superadmin') return $next($request);

        if (empty($u->employee_id)) {
            // Whitelist: link-nik, change-password, logout
            $allowed = [
                'auth.link-nik', 'auth.link-nik.submit',
                'auth.change-password', 'auth.change-password.submit',
                'logout',
            ];
            $current = optional($request->route())->getName();
            if (in_array($current, $allowed, true)) return $next($request);

            return redirect()->route('auth.link-nik')
                ->with('info', 'Silakan masukkan NIK karyawan Anda terlebih dulu.');
        }

        return $next($request);
    }
}
