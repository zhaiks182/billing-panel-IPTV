<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\XuiSetting;
use App\Services\Xui\XuiApiException;
use App\Services\Xui\XuiOneClient;
use Illuminate\Http\Request;

class XuiSettingController extends Controller
{
    public function edit()
    {
        $settings = XuiSetting::current();

        return view('admin.xui-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'panel_url' => ['required', 'url', 'max:255'],
            'access_code' => ['required', 'string', 'max:255'],
            'api_token' => ['nullable', 'string', 'max:2000'],
            'stream_url' => ['nullable', 'url', 'max:255'],
            'server_url' => ['nullable', 'url', 'max:255'],
        ]);

        $settings = XuiSetting::current();

        $settings->panel_url = $validated['panel_url'];
        $settings->access_code = $validated['access_code'];
        $settings->stream_url = $validated['stream_url'] ?? null;
        $settings->server_url = $validated['server_url'] ?? null;

        if (! empty($validated['api_token'])) {
            $settings->api_token = $validated['api_token'];
        }

        $settings->save();

        if ($request->boolean('test_connection')) {
            try {
                $packages = (new XuiOneClient($settings))->getPackages();

                return back()->with('status', 'Configuración guardada. Conexión exitosa: '.count($packages).' paquete(s) encontrados en XUI.');
            } catch (XuiApiException $e) {
                return back()->with('status', 'Configuración guardada, pero la prueba de conexión falló: '.$e->getMessage());
            }
        }

        return back()->with('status', 'Configuración guardada.');
    }
}
