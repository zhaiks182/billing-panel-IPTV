<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Order;
use App\Services\InvoicePdfService;
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
        $this->order->loadMissing(['user', 'package', 'paymentMethod']);

        $pdf = app(InvoicePdfService::class);
        $isTrial = $this->order->package->is_trial;

        return EmailTemplate::mail('order_invoice', [
            'user_name' => $notifiable->name,
            'order_id' => (string) $this->order->id,
            'package_name' => $this->order->package->name,
            'amount' => '$'.number_format((float) $this->order->amount, 2).' USD',
            'payment_method_name' => $isTrial ? 'Prueba gratuita' : ($this->order->paymentMethod?->name ?: '—'),
            'status_label' => $isTrial ? 'Prueba gratuita' : 'Pendiente de pago',
            'intro_text' => $isTrial
                ? 'recibimos tu solicitud de prueba gratuita. Este es el comprobante de tu pedido — en cuanto verifiques tu correo, activaremos tu línea automáticamente.'
                : 'recibimos tu pedido y el comprobante de pago que subiste. Está en revisión — en cuanto lo confirmemos, activaremos tu línea y te avisaremos por correo.',
            'issued_date' => $this->order->created_at->format('d/m/Y'),
            'billing_address' => $this->billingAddressLines($notifiable, '<br>'),
            'billing_address_text' => $this->billingAddressLines($notifiable, "\n"),
            'orders_url' => route('orders.index'),
        ])->attachData($pdf->generate($this->order), $pdf->filename($this->order), [
            'mime' => 'application/pdf',
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
