<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Mensajes en español para los códigos de estado del password broker de Laravel —
     * ver la misma nota en PasswordResetLinkController.
     */
    private const STATUS_MESSAGES = [
        'passwords.reset' => 'Tu contraseña fue restablecida correctamente.',
        'passwords.token' => 'Este enlace de restablecimiento de contraseña no es válido o ya expiró.',
        'passwords.user' => 'No encontramos ningún usuario con ese correo electrónico.',
    ];

    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                // Defensa adicional: aunque PasswordResetLinkController ya no genera enlaces de
                // reset para cuentas admin, esto cubre un token que ya hubiera sido emitido antes
                // de ese fix. Se responde igual que un token inválido/expirado, sin distinguir.
                if ($user->isAdmin()) {
                    throw ValidationException::withMessages([
                        'email' => self::STATUS_MESSAGES['passwords.token'],
                    ]);
                }

                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        $message = self::STATUS_MESSAGES[$status] ?? __($status);

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', $message)
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => $message]);
    }
}
