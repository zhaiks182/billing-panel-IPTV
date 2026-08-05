<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRejected extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return EmailTemplate::mail('order_rejected', [
            'user_name' => $notifiable->name,
            'order_id' => (string) $this->order->id,
            'admin_note' => $this->order->admin_note ?: 'No se especificó un motivo.',
            'orders_url' => route('orders.index'),
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'message' => "Tu pedido #{$this->order->id} fue rechazado.",
        ];
    }
}
