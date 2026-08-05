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

        try {
            $response = Http::timeout(10)->asForm()->post(
                "https://api.telegram.org/bot{$settings->bot_token}/sendMessage",
                [
                    'chat_id' => $settings->chat_id,
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
