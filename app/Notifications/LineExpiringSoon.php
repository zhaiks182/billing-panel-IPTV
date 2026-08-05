<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Line;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LineExpiringSoon extends Notification
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

        $days = max(0, (int) ceil(now()->diffInHours($this->line->expires_at, false) / 24));
        $daysLabel = $days <= 1 ? 'mañana' : "en {$days} días";

        return EmailTemplate::mail('line_expiring_soon', [
            'user_name' => $notifiable->name,
            'package_name' => $this->line->order?->package?->name ?: 'tu paquete',
            'line_expires_at' => $this->line->expires_at->format('d/m/Y H:i'),
            'days_label' => $daysLabel,
            'renew_url' => route('home'),
        ]);
    }
}
