<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso interno por correo (a la bandeja de soporte), mismo contenido que el resumen
 * automático de ventas que ya se manda por Telegram todos los días a las 10pm — a pedido
 * del usuario, que quería la misma información también por correo. Se envía desde
 * App\Console\Commands\SendTelegramDailySalesSummary, con destinatario on-demand
 * (Notification::route('mail', ...)) porque soporte@4livepro.com no es una cuenta User real.
 */
class AdminDailySalesSummary extends Notification
{
    public function __construct(
        public string $date,
        public int $paidOrdersCount,
        public string $revenue,
        public int $trialOrdersCount,
        public int $totalOrdersCount,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return EmailTemplate::mail('daily_sales_summary', [
            'date' => $this->date,
            'paid_orders_count' => (string) $this->paidOrdersCount,
            'revenue' => $this->revenue,
            'trial_orders_count' => (string) $this->trialOrdersCount,
            'total_orders_count' => (string) $this->totalOrdersCount,
        ]);
    }
}
