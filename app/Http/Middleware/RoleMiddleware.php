<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            abort(403);
        }

        // Support multiple roles: role:admin,accounting
        foreach ($roles as $roleGroup) {
            $allowedRoles = explode(',', $roleGroup);
            if (in_array(Auth::user()->role, $allowedRoles)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
