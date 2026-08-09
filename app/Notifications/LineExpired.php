<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Line;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A diferencia de LineExpiringSoon (antes del vencimiento), esta se envía cuando la línea
 * YA venció — ver App\Console\Commands\SendExpiredLineNotices.
 */
class LineExpired extends Notification
{
    use Queueable;

    public function __construct(public Line $line)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->line->loadMissing('order.package');

        return EmailTemplate::mail('line_expired', [
            'user_name' => $notifiable->name,
            'package_name' => $this->line->order?->package?->name ?: 'tu paquete',
            'line_expired_at' => $this->line->expires_at->format('d/m/Y H:i'),
            'renew_url' => route('home'),
        ]);
    }
}
