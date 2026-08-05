<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Order;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Se envía dos veces en el ciclo de vida de un pedido de pago: al crearse (estado
 * "Pendiente de pago", como confirmación de recepción del comprobante) y de nuevo al
 * aprobarse (estado "Pagada", como comprobante final de la transacción) — ver
 * Admin\OrderController@activate. En pedidos trial se envía solo una vez, al crearse
 * (siempre "Prueba gratuita", no hay "pago" que confirmar después).
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
        $isApproved = $this->order->status === 'approved';

        $statusLabel = match (true) {
            $isTrial => 'Prueba gratuita',
            $isApproved => 'Pagada',
            default => 'Pendiente de pago',
        };

        $introText = match (true) {
            $isTrial => 'recibimos tu solicitud de prueba gratuita. Este es el comprobante de tu pedido — en cuanto verifiques tu correo, activaremos tu línea automáticamente.',
            $isApproved => 'confirmamos tu pago. Aquí tienes tu factura como comprobante de la transacción — gracias por tu compra.',
            default => 'recibimos tu pedido y el comprobante de pago que subiste. Está en revisión — en cuanto lo confirmemos, activaremos tu línea y te avisaremos por correo.',
        };

        return EmailTemplate::mail('order_invoice', [
            'user_name' => $notifiable->name,
            'order_id' => (string) $this->order->id,
            'package_name' => $this->order->package->name,
            'amount' => '$'.number_format((float) $this->order->amount, 2).' USD',
            'payment_method_name' => $isTrial ? 'Prueba gratuita' : ($this->order->paymentMethod?->name ?: '—'),
            'status_label' => $statusLabel,
            'intro_text' => $introText,
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
