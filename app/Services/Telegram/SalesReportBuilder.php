<?php

namespace App\Services\Telegram;

use App\Models\Order;

/**
 * Arma el texto del resumen de ventas del día — usado tanto por el comando /ventashoy del
 * webhook (TelegramWebhookController) como por el resumen automático diario (comando
 * telegram:daily-summary), para no duplicar la misma consulta en dos lugares.
 */
class SalesReportBuilder
{
    public function today(): string
    {
        $today = now()->startOfDay();

        $approvedToday = Order::whereIn('status', ['approved', 'activated'])
            ->whereDate('approved_at', $today)
            ->with('package')
            ->get();

        $paidOrders = $approvedToday->filter(fn (Order $order) => ! $order->package->is_trial);
        $trialOrders = $approvedToday->filter(fn (Order $order) => $order->package->is_trial);
        $revenue = $paidOrders->sum('amount');

        return "📊 <b>Ventas de hoy</b> ({$today->format('d/m/Y')})\n\n".
            "Pedidos pagados aprobados: {$paidOrders->count()}\n".
            'Ingresos: $'.number_format((float) $revenue, 2)." USD\n".
            "Demos activadas: {$trialOrders->count()}\n\n".
            "Total pedidos aprobados: {$approvedToday->count()}";
    }
}
