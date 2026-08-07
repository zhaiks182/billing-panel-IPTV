<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\TurnstileSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Login del panel admin, separado por completo del /login de clientes (Auth\AuthenticatedSessionController)
 * — un admin nunca debe autenticarse por el formulario de clientes ni terminar en el
 * dashboard de cliente. Reutiliza App\Http\Requests\Auth\LoginRequest tal cual (rate-limit +
 * Turnstile + Auth::attempt, no sabe nada de roles); el chequeo de rol vive aquí.
 */
class AuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->user()) {
            abort(403);
        }

        $turnstile = TurnstileSetting::current();

        return view('admin.auth.login', [
            'turnstileSiteKey' => $turnstile->isActive() ? $turnstile->site_key : null,
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (! Auth::user()->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'username' => 'Estas credenciales no tienen acceso al panel de administración.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'Sesión cerrada correctamente.');
    }
}
