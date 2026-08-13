<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TurnstileSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TurnstileSettingController extends Controller
{
    public function edit()
    {
        $settings = TurnstileSetting::current();

        return view('admin.turnstile-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'site_key' => ['nullable', 'required_if:enabled,1', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string', 'max:255'],
            'theme' => ['required', Rule::in(['dark', 'light'])],
        ]);

        $settings = TurnstileSetting::current();

        $settings->enabled = $request->boolean('enabled');
        $settings->site_key = $validated['site_key'] ?? null;
        $settings->theme = $validated['theme'];

        if (! empty($validated['secret_key'])) {
            $settings->secret_key = $validated['secret_key'];
        }

        if ($settings->enabled && ! $settings->secret_key) {
            return back()->withErrors(['secret_key' => 'Debes indicar la Secret Key para activar Turnstile.'])->withInput();
        }

        $settings->save();

        return back()->with('status', 'Configuración guardada.');
    }
}
