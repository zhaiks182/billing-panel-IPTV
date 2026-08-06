<?php

namespace App\Services\Telegram;

use App\Models\TelegramSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramNotifier
{
    public function send(string $message): bool
    {
        $settings = TelegramSetting::current();

        if (! $settings->isActive()) {
            return false;
        }

        return $this->sendTo($settings->bot_token, $settings->chat_id, $message);
    }

    /**
     * Envía un mensaje con un token/chat_id específicos, sin pasar por TelegramSetting.
     * Usado para probar la conexión con valores que el admin todavía no ha guardado.
     */
    public function sendTo(string $botToken, string $chatId, string $message): bool
    {
        try {
            $response = Http::timeout(10)->asForm()->post(
                "https://api.telegram.org/bot{$botToken}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]
            );

            if (! $response->ok()) {
                Log::warning('Telegram sendMessage falló', ['response' => $response->body()]);
            }

            return $response->ok();
        } catch (Throwable $e) {
            Log::warning('Telegram sendMessage lanzó una excepción', ['message' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Registra la URL del webhook en Telegram para que reenvíe ahí los mensajes que le
     * escriban al bot (comandos como /ventashoy). `secretToken` viaja en cada llamada del
     * webhook como header `X-Telegram-Bot-Api-Secret-Token`, para verificar que la petición
     * viene realmente de Telegram y no de cualquiera que adivine la URL.
     */
    public function setWebhook(string $botToken, string $url, string $secretToken): bool
    {
        try {
            $response = Http::timeout(10)->asForm()->post(
                "https://api.telegram.org/bot{$botToken}/setWebhook",
                [
                    'url' => $url,
                    'secret_token' => $secretToken,
                ]
            );

            if (! $response->ok() || ! ($response->json('ok') ?? false)) {
                Log::warning('Telegram setWebhook falló', ['response' => $response->body()]);
            }

            return $response->ok() && ($response->json('ok') ?? false);
        } catch (Throwable $e) {
            Log::warning('Telegram setWebhook lanzó una excepción', ['message' => $e->getMessage()]);

            return false;
        }
    }

    public function deleteWebhook(string $botToken): bool
    {
        try {
            $response = Http::timeout(10)->asForm()->post("https://api.telegram.org/bot{$botToken}/deleteWebhook");

            return $response->ok() && ($response->json('ok') ?? false);
        } catch (Throwable $e) {
            Log::warning('Telegram deleteWebhook lanzó una excepción', ['message' => $e->getMessage()]);

            return false;
        }
    }
}
