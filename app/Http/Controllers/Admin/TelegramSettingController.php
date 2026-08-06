<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramSetting;
use App\Services\Telegram\TelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramSettingController extends Controller
{
    public function edit()
    {
        $settings = TelegramSetting::current();

        return view('admin.telegram-settings.edit', compact('settings'));
    }

    public function test(Request $request, TelegramNotifier $telegram)
    {
        $validated = $request->validate([
            'bot_token' => ['nullable', 'string', 'max:255'],
            'chat_id' => ['required', 'string', 'max:255'],
        ]);

        $botToken = $validated['bot_token'] ?: TelegramSetting::current()->bot_token;

        if (! $botToken) {
            return response()->json([
                'success' => false,
                'message' => 'Debes indicar el Bot Token.',
            ], 422);
        }

        $sent = $telegram->sendTo($botToken, $validated['chat_id'], '✅ Prueba de conexión desde el panel de administración de 4LivePro Latino.');

        return response()->json([
            'success' => $sent,
            'message' => $sent
                ? 'Mensaje de prueba enviado correctamente. Revisa tu Telegram.'
                : 'No se pudo enviar el mensaje. Revisa el Bot Token y el Chat ID.',
        ]);
    }

    public function update(Request $request, TelegramNotifier $telegram)
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'bot_token' => ['nullable', 'string', 'max:255'],
            'chat_id' => ['nullable', 'required_if:enabled,1', 'string', 'max:255'],
        ]);

        $settings = TelegramSetting::current();
        $previousBotToken = $settings->bot_token;

        $settings->enabled = $request->boolean('enabled');
        $settings->chat_id = $validated['chat_id'] ?? null;

        if (! empty($validated['bot_token'])) {
            $settings->bot_token = $validated['bot_token'];
        }

        if ($settings->enabled && ! $settings->bot_token) {
            return back()->withErrors(['bot_token' => 'Debes indicar el Bot Token para activar Telegram.'])->withInput();
        }

        $webhookWarning = null;

        if ($settings->isActive()) {
            if (! $settings->webhook_secret) {
                $settings->webhook_secret = Str::random(48);
            }

            $webhookUrl = route('telegram.webhook');

            if (str_starts_with($webhookUrl, 'https://')) {
                if (! $telegram->setWebhook($settings->bot_token, $webhookUrl, $settings->webhook_secret)) {
                    $webhookWarning = ' El bot quedó configurado, pero no se pudo activar el comando /ventashoy (revisa el Bot Token).';
                }
            } else {
                $webhookWarning = ' El comando /ventashoy solo funciona en un sitio con HTTPS público (no en local).';
            }
        } elseif ($previousBotToken) {
            $telegram->deleteWebhook($previousBotToken);
        }

        $settings->save();

        if ($request->boolean('send_test')) {
            $sent = $telegram->send('✅ Prueba de notificaciones de 4LivePro Latino. Si ves esto, la configuración es correcta.');

            return back()->with('status', ($sent
                ? 'Configuración guardada. Mensaje de prueba enviado correctamente.'
                : 'Configuración guardada, pero el mensaje de prueba no pudo enviarse. Revisa el Bot Token y el Chat ID.').$webhookWarning);
        }

        return back()->with('status', 'Configuración guardada.'.$webhookWarning);
    }
}
