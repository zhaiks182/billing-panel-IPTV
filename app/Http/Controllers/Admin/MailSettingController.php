<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailSettingController extends Controller
{
    public function edit()
    {
        $settings = MailSetting::current();

        return view('admin.mail-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'mailer' => ['required', 'in:log,smtp'],
            'host' => ['nullable', 'required_if:mailer,smtp', 'string', 'max:255'],
            'port' => ['nullable', 'required_if:mailer,smtp', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'from_address' => ['nullable', 'required_if:mailer,smtp', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = MailSetting::current();

        $settings->mailer = $validated['mailer'];
        $settings->host = $validated['host'] ?? null;
        $settings->port = $validated['port'] ?? null;
        $settings->username = $validated['username'] ?? null;
        $settings->encryption = $validated['encryption'] ?? null;
        $settings->from_address = $validated['from_address'] ?? null;
        $settings->from_name = $validated['from_name'] ?? null;

        if (! empty($validated['password'])) {
            $settings->password = $validated['password'];
        }

        $settings->save();

        if ($request->filled('test_email')) {
            $request->validate(['test_email' => ['email']]);

            try {
                $this->applySettingsToRuntime($settings);

                Mail::raw('Este es un correo de prueba enviado desde el panel de administración de 4LivePro Latino.', function ($message) use ($request, $settings) {
                    $message->to($request->string('test_email'))
                        ->subject('Correo de prueba - 4LivePro Latino');

                    if ($settings->from_address) {
                        $message->from($settings->from_address, $settings->from_name ?: config('app.name'));
                    }
                });

                return back()->with('status', 'Configuración guardada. Correo de prueba enviado correctamente a '.$request->string('test_email').'.');
            } catch (\Throwable $e) {
                return back()->with('status', 'Configuración guardada, pero el envío de prueba falló: '.$e->getMessage());
            }
        }

        return back()->with('status', 'Configuración guardada.');
    }

    private function applySettingsToRuntime(MailSetting $settings): void
    {
        if ($settings->mailer !== 'smtp' || ! $settings->host) {
            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $settings->host);
        Config::set('mail.mailers.smtp.port', $settings->port);
        Config::set('mail.mailers.smtp.username', $settings->username);
        Config::set('mail.mailers.smtp.password', $settings->password);
        Config::set('mail.mailers.smtp.encryption', $settings->encryption ?: null);
    }
}
