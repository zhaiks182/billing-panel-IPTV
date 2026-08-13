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
    /**
     * Datos crudos del resumen de hoy, compartidos entre el texto de Telegram (`today()`)
     * y el correo interno (`App\Notifications\AdminDailySalesSummary`) — una sola consulta,
     * dos formatos de salida.
     */
    public function todayStats(): array
    {
        $today = now()->startOfDay();

        $approvedToday = Order::whereIn('status', ['approved', 'activated'])
            ->whereDate('approved_at', $today)
            ->with('package')
            ->get();

        $paidOrders = $approvedToday->filter(fn (Order $order) => ! $order->package->is_trial);
        $trialOrders = $approvedToday->filter(fn (Order $order) => $order->package->is_trial);

        return [
            'date' => $today,
            'paid_count' => $paidOrders->count(),
            'revenue' => (float) $paidOrders->sum('amount'),
            'trial_count' => $trialOrders->count(),
            'total_count' => $approvedToday->count(),
        ];
    }

    public function today(): string
    {
        $stats = $this->todayStats();

        return "📊 <b>Ventas de hoy</b> ({$stats['date']->format('d/m/Y')})\n\n".
            "Pedidos pagados aprobados: {$stats['paid_count']}\n".
            'Ingresos: $'.number_format($stats['revenue'], 2)." USD\n".
            "Demos activadas: {$stats['trial_count']}\n\n".
            "Total pedidos aprobados: {$stats['total_count']}";
    }
}
