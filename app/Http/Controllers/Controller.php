<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

abstract class Controller
{
    /**
     * Igual que redirect()->intended($default), pero ignora un `url.intended` que apunte al
     * panel admin (/adm_4livepro). Bug real encontrado 2026-08-07: Laravel guarda esa clave de
     * sesión automáticamente para CUALQUIER visita sin sesión a una ruta protegida, incluidas
     * las de /adm_4livepro — aunque el login admin nunca la usa (Admin\AuthController::store()
     * siempre redirige fijo a admin.dashboard). Si alguien prueba el panel admin y el registro
     * de cliente en el mismo navegador (misma sesión), esa URL admin queda pegada y termina
     * mandando al cliente recién logueado/verificado a una ruta admin → 403 (EnsureUserIsAdmin).
     */
    protected function intendedRedirect(string $default): RedirectResponse
    {
        $intended = session()->pull('url.intended');

        if ($intended && str_starts_with(parse_url($intended, PHP_URL_PATH) ?? '', '/adm_4livepro')) {
            $intended = null;
        }

        return Redirect::to($intended ?: $default);
    }
}
