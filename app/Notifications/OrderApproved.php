<?php

namespace App\Notifications;

use App\Models\Line;
use App\Models\Order;
use App\Models\XuiSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderApproved extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public Line $line,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tu línea IPTV está activa - Pedido #{$this->order->id}")
            ->line('Tu pago fue aprobado y tu línea M3U ya está activa.')
            ->when(XuiSetting::current()->server_url, fn ($mail, $server) => $mail->line("Servidor: {$server}"))
            ->line("Usuario: {$this->line->xui_username}")
            ->line("Contraseña: {$this->line->xui_password}")
            ->when($this->line->m3u_url, fn ($mail) => $mail->line("URL M3U: {$this->line->m3u_url}"))
            ->line("Vence el: {$this->line->expires_at->format('d/m/Y')}")
            ->action('Ver mi panel', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'line_id' => $this->line->id,
            'message' => "Tu pedido #{$this->order->id} fue aprobado y tu línea está activa.",
        ];
    }
}
