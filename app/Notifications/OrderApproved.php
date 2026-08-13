<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Line;
use App\Models\Order;
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
        $this->order->loadMissing('package');

        return EmailTemplate::mail('order_approved', [
            'user_name' => $notifiable->name,
            'order_id' => (string) $this->order->order_number,
            'package_name' => $this->order->package->name,
            'xui_username' => $this->line->xui_username,
            'xui_password' => $this->line->xui_password,
            'm3u_url' => $this->line->m3u_url ?: '—',
            'line_expires_at' => $this->line->expires_at->format('d/m/Y'),
            'dashboard_url' => route('dashboard'),
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'line_id' => $this->line->id,
            'message' => "Tu pedido #{$this->order->order_number} fue aprobado y tu línea está activa.",
        ];
    }
}
