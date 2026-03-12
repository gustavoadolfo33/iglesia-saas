<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFilamentRoleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!$user->hasAnyRole(User::panelRoleNames())) {
            abort(403, 'No tienes permisos para acceder al panel de administración.');
        }

        return $next($request);
    }
}
