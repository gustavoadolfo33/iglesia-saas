<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFilamentRoleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no hay usuario logueado, que Filament maneje el redirect al login
        if (!$user) {
            return $next($request);
        }

        // Solo estos roles pueden entrar al backoffice
        if (!$user->hasAnyRole(['presidente', 'tesorero', 'presbitero', 'pastor', 'contador'])) {
            abort(403, 'No tienes permisos para acceder al panel de administración.');
        }
        return $next($request);
    }
}
