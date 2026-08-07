<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'admin.timeout' => \App\Http\Middleware\AdminIdleTimeout::class,
            'no-admin' => \App\Http\Middleware\RedirectAuthenticatedAdmin::class,
        ]);

        // Un invitado (o una sesión ya expirada) que pide una ruta protegida de
        // /adm_4livepro/* debe caer en el login del panel, no en el /login de clientes.
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('adm_4livepro*') ? route('admin.login') : route('login')
        );

        // Telegram llama a este webhook sin cookie de sesión ni token CSRF — la seguridad
        // la da el header X-Telegram-Bot-Api-Secret-Token, verificado en el controller.
        $middleware->validateCsrfTokens(except: [
            'telegram/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
