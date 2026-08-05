<?php

namespace App\Notifications;

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
        return (new MailMessage)
            ->subject("Tu pedido #{$this->order->id} fue rechazado")
            ->line('No pudimos validar tu comprobante de pago.')
            ->when($this->order->admin_note, fn ($mail) => $mail->line("Motivo: {$this->order->admin_note}"))
            ->line('Si crees que es un error, contáctanos o sube un nuevo comprobante.')
            ->action('Ver mis pedidos', route('orders.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'message' => "Tu pedido #{$this->order->id} fue rechazado.",
        ];
    }
}
