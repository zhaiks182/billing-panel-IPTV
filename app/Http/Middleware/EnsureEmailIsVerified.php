<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Igual que el middleware 'verified' de Laravel, pero guarda la URL a la que
 * el usuario intentaba llegar (ej. comprar un paquete) para poder regresarlo
 * ahí después de verificar su correo, en vez de mandarlo siempre al dashboard.
 */
class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null): Response
    {
        $user = $request->user();

        if (! $user || ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail())) {
            $request->session()->put('url.intended', $request->fullUrl());

            return $request->expectsJson()
                ? abort(403, 'Your email address is not verified.')
                : redirect()->route($redirectToRoute ?: 'verification.notice');
        }

        return $next($request);
    }
}
