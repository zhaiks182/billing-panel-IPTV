<?php

namespace App\Rules;

use App\Models\TurnstileSetting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class ValidTurnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $settings = TurnstileSetting::current();

        if (! $settings->isActive()) {
            return;
        }

        if (! $value) {
            $fail('Por favor completa la verificación de seguridad.');

            return;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $settings->secret_key,
            'response' => $value,
        ]);

        if (! $response->ok() || ! ($response->json('success') ?? false)) {
            $fail('La verificación de seguridad falló. Inténtalo de nuevo.');
        }
    }
}
