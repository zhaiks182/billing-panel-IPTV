<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TurnstileSetting;
use App\Models\User;
use App\Rules\ValidTurnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Mensajes en español para los códigos de estado que devuelve el password broker de
     * Laravel (`passwords.sent`, etc.) — no hay archivos de traducción `lang/es` en este
     * proyecto (APP_LOCALE=es sin lang propio), así que `__($status)` caía en inglés.
     */
    private const STATUS_MESSAGES = [
        'passwords.sent' => 'Te enviamos por correo electrónico un enlace para restablecer tu contraseña.',
        'passwords.user' => 'No encontramos ningún usuario con ese correo electrónico.',
        'passwords.throttled' => 'Por favor espera antes de volver a intentarlo.',
    ];

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        $turnstileSiteKey = TurnstileSetting::current()->isActive()
            ? TurnstileSetting::current()->site_key
            : null;

        return view('auth.forgot-password', compact('turnstileSiteKey'));
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'cf-turnstile-response' => [new ValidTurnstile],
        ]);

        // El panel admin no tiene "olvidé mi contraseña" propio a propósito (la contraseña de
        // un admin solo se cambia por SSH con `app:create-admin`, ver CLAUDE.md). Si dejáramos
        // pasar esto, cualquiera podría tomar una cuenta admin resolviendo el reset por este
        // flujo público de clientes. Respondemos igual que un envío exitoso para no filtrar
        // siquiera si el correo pertenece a un admin.
        if (User::where('email', $request->email)->where('role', 'admin')->exists()) {
            return back()->with('status', self::STATUS_MESSAGES['passwords.sent']);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        $message = self::STATUS_MESSAGES[$status] ?? __($status);

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', $message)
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => $message]);
    }
}
