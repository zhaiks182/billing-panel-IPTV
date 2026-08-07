<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra la sesión de un cliente bloqueado por un admin, aunque ya tuviera una sesión
 * activa antes del bloqueo (el rechazo en el login por sí solo no alcanza para eso).
 * Los admins nunca pueden estar bloqueados (User::isBlocked() ya los excluye).
 */
class EnsureUserIsNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isBlocked()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Tu cuenta ha sido bloqueada. Contacta a soporte.');
        }

        return $next($request);
    }
}
