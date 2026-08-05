<?php

namespace App\Notifications;

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
        $days = max(0, (int) ceil(now()->diffInHours($this->line->expires_at, false) / 24));
        $daysLabel = $days <= 1 ? 'mañana' : "en {$days} días";

        return (new MailMessage)
            ->subject('Tu línea IPTV vence pronto')
            ->line("Tu línea M3U vence {$daysLabel} ({$this->line->expires_at->format('d/m/Y H:i')}).")
            ->line('Renueva ahora para que tu servicio no se interrumpa.')
            ->action('Renovar mi línea', route('home'))
            ->line('¿Dudas? Escríbenos por WhatsApp: +593 984564703');
    }
}
