<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        foreach ($roles as $role) {
            if ($role === 'system_admin' && $user->isSystemAdmin()) {
                return $next($request);
            }
            if ($role === 'center_manager' && $user->isCenterManager()) {
                return $next($request);
            }
            if ($role === 'center_employee' && $user->isCenterEmployee()) {
                return $next($request);
            }
        }

        abort(403);
    }
}
