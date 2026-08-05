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
}
