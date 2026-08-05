<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Se envía apenas el cliente sube su comprobante y crea el pedido (pago manual), como
 * confirmación de recepción — no es una notificación de que ya se aprobó (eso lo manda
 * OrderApproved más tarde, cuando un admin revisa el comprobante).
 */
class OrderInvoice extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing(['package', 'paymentMethod']);

        return EmailTemplate::mail('order_invoice', [
            'user_name' => $notifiable->name,
            'order_id' => (string) $this->order->id,
            'package_name' => $this->order->package->name,
            'amount' => '$'.number_format((float) $this->order->amount, 2).' USD',
            'payment_method_name' => $this->order->paymentMethod?->name ?: '—',
            'issued_date' => $this->order->created_at->format('d/m/Y'),
            'billing_address' => $this->billingAddressLines($notifiable, '<br>'),
            'billing_address_text' => $this->billingAddressLines($notifiable, "\n"),
            'orders_url' => route('orders.index'),
        ]);
    }

    private function billingAddressLines(object $user, string $separator): string
    {
        $lines = array_filter([
            $user->address_line_1,
            $user->address_line_2,
            implode(', ', array_filter([$user->city, $user->state, $user->postal_code, $user->country])),
        ]);

        return $lines ? implode($separator, $lines) : '—';
    }
}
