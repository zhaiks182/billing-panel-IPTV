<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra la sesión del admin tras 15 minutos sin actividad dentro de /adm_4livepro —
 * a diferencia del resto de la app, que usa el SESSION_LIFETIME normal. Solo corre
 * dentro del grupo de rutas admin.*, después de EnsureUserIsAdmin.
 */
class AdminIdleTimeout
{
    private const MAX_IDLE_SECONDS = 15 * 60;

    public function handle(Request $request, Closure $next): Response
    {
        $lastActivity = $request->session()->get('admin_last_activity');

        if ($lastActivity !== null && (time() - $lastActivity) >= self::MAX_IDLE_SECONDS) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('status', 'Tu sesión de administrador se cerró por inactividad.');
        }

        $request->session()->put('admin_last_activity', time());

        return $next($request);
    }
}
