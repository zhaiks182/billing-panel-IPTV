<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Genera el PDF de la factura de un pedido, 100% en el servidor (dompdf, sin binarios
 * externos ni Node/Chromium — corre bien en un LAMP normal). Se usa para adjuntarlo al
 * correo de "factura pendiente de pago" (ver App\Notifications\OrderInvoice).
 */
class InvoicePdfService
{
    public function generate(Order $order): string
    {
        $order->loadMissing(['user', 'package', 'paymentMethod']);

        $statusLabel = match (true) {
            $order->package->is_trial => 'Prueba gratuita',
            $order->status === 'activated' => 'Pagada',
            $order->status === 'rejected' => 'Cancelada',
            $order->status === 'error' => 'Error',
            default => 'Pendiente de pago',
        };

        $logoPath = public_path('images/logo.png');
        $logoDataUri = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : '';

        return Pdf::loadView('pdf.invoice', [
            'order' => $order,
            'statusLabel' => $statusLabel,
            'logoDataUri' => $logoDataUri,
            'companyUrl' => preg_replace('#^https?://#', '', config('app.url')),
        ])->output();
    }

    public function filename(Order $order): string
    {
        return "factura-{$order->order_number}.pdf";
    }
}
