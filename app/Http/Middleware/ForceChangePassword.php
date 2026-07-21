<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Force user dgn must_change_password=true untuk ganti password dulu.
 * Superadmin bypass.
 */
class ForceChangePassword
{
    public function handle(Request $request, Closure $next)
    {
        $u = auth()->user();
        if (!$u) return $next($request);

        if ($u->role === 'superadmin') return $next($request);

        if ($u->must_change_password) {
            $allowed = [
                'auth.change-password', 'auth.change-password.submit',
                'logout',
            ];
            $current = optional($request->route())->getName();
            if (in_array($current, $allowed, true)) return $next($request);

            return redirect()->route('auth.change-password')
                ->with('info', 'Silakan ganti password default Anda sebelum melanjutkan.');
        }

        return $next($request);
    }
}
