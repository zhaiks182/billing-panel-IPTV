<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramSetting;
use App\Services\Telegram\TelegramNotifier;
use Illuminate\Http\Request;

class TelegramSettingController extends Controller
{
    public function edit()
    {
        $settings = TelegramSetting::current();

        return view('admin.telegram-settings.edit', compact('settings'));
    }

    public function update(Request $request, TelegramNotifier $telegram)
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'bot_token' => ['nullable', 'string', 'max:255'],
            'chat_id' => ['nullable', 'required_if:enabled,1', 'string', 'max:255'],
        ]);

        $settings = TelegramSetting::current();

        $settings->enabled = $request->boolean('enabled');
        $settings->chat_id = $validated['chat_id'] ?? null;

        if (! empty($validated['bot_token'])) {
            $settings->bot_token = $validated['bot_token'];
        }

        if ($settings->enabled && ! $settings->bot_token) {
            return back()->withErrors(['bot_token' => 'Debes indicar el Bot Token para activar Telegram.'])->withInput();
        }

        $settings->save();

        if ($request->boolean('send_test')) {
            $sent = $telegram->send('✅ Prueba de notificaciones de 4LivePro Latino. Si ves esto, la configuración es correcta.');

            return back()->with('status', $sent
                ? 'Configuración guardada. Mensaje de prueba enviado correctamente.'
                : 'Configuración guardada, pero el mensaje de prueba no pudo enviarse. Revisa el Bot Token y el Chat ID.');
        }

        return back()->with('status', 'Configuración guardada.');
    }
}
