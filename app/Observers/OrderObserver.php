<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\Telegram\TelegramNotifier;

class OrderObserver
{
    public function __construct(private readonly TelegramNotifier $telegram)
    {
    }

    public function created(Order $order): void
    {
        $order->loadMissing(['user', 'package']);

        $isTrial = $order->package->is_trial;
        $type = $isTrial ? '🎁 Demo gratis' : '💳 Pedido de pago';
        $amount = $isTrial ? 'Gratis' : '$'.number_format($order->amount, 2);
        $statusLabel = match ($order->status) {
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'activated' => 'Activado',
            'rejected' => 'Cancelado',
            'error' => 'Error',
            default => $order->status,
        };

        $message = "<b>{$type} #{$order->id}</b>\n"
            ."Cliente: {$order->user->name} ({$order->user->email})\n"
            ."Paquete: {$order->package->name}\n"
            ."Monto: {$amount}\n"
            ."Estado: {$statusLabel}\n\n"
            .'Aprobar pedido: '.route('admin.orders.index', ['status' => 'pending']);

        $this->telegram->send($message);
    }
}
