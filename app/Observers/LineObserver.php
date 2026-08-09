<?php

namespace App\Observers;

use App\Models\Line;
use App\Services\Telegram\TelegramNotifier;

class LineObserver
{
    public function __construct(private readonly TelegramNotifier $telegram)
    {
    }

    public function created(Line $line): void
    {
        $line->loadMissing(['user', 'order.package']);

        $package = $line->order?->package;
        $type = $package?->is_trial ? '🎁 Demo' : '💳 Pago';

        $message = "✅ <b>Línea activada ({$type})</b>\n"
            .'Cliente: '.TelegramNotifier::escape($line->user->name).' ('.TelegramNotifier::escape($line->user->email).")\n"
            .($package ? 'Paquete: '.TelegramNotifier::escape($package->name)."\n" : '')
            .'Usuario XUI: '.TelegramNotifier::escape($line->xui_username)."\n"
            ."Vence: {$line->expires_at->format('d/m/Y H:i')}";

        $this->telegram->send($message);
    }
}
