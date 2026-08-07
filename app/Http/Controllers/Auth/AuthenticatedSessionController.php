<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\TurnstileSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $redirect = (string) $request->query('redirect', '');

        // Solo se acepta una ruta local (empieza con "/" y no "//", que un navegador trata
        // como protocol-relative a otro host) — evita que un enlace tipo /login?redirect=
        // https://evil.com termine mandando al usuario a un sitio externo tras iniciar sesión.
        if ($redirect !== '' && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            $request->session()->put('url.intended', $redirect);
        }

        $turnstile = TurnstileSetting::current();

        return view('auth.login', [
            'turnstileSiteKey' => $turnstile->isActive() ? $turnstile->site_key : null,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (Auth::user()->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Los administradores deben iniciar sesión desde el panel de administración.',
            ]);
        }

        if (Auth::user()->isBlocked()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta ha sido bloqueada. Contacta a soporte.',
            ]);
        }

        $request->session()->regenerate();

        return $this->intendedRedirect(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
