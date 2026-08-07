<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aparta al admin del área de clientes: si el usuario autenticado es admin, lo manda de
 * vuelta al panel en vez de dejarlo pasar. No exige sesión (no-op para invitados/clientes),
 * así que se puede usar en rutas de carrito/checkout que también sirven a invitados.
 */
class RedirectAuthenticatedAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
