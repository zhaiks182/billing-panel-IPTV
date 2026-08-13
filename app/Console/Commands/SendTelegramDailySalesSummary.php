<?php

namespace App\Console\Commands;

use App\Models\MailSetting;
use App\Models\TelegramSetting;
use App\Notifications\AdminDailySalesSummary;
use App\Services\Telegram\SalesReportBuilder;
use App\Services\Telegram\TelegramNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendTelegramDailySalesSummary extends Command
{
    protected $signature = 'telegram:daily-summary';

    protected $description = 'Envía el resumen de ventas del día por Telegram y por correo a soporte (comando programado, ver Admin > Telegram)';

    public function handle(TelegramNotifier $telegram, SalesReportBuilder $salesReport): int
    {
        $this->sendTelegram($telegram, $salesReport);
        $this->sendEmail($salesReport);

        return self::SUCCESS;
    }

    private function sendTelegram(TelegramNotifier $telegram, SalesReportBuilder $salesReport): void
    {
        $settings = TelegramSetting::current();

        if (! $settings->isActive() || ! $settings->daily_summary_enabled) {
            $this->info('Resumen diario por Telegram desactivado o sin configurar — no se envía.');

            return;
        }

        $sent = $telegram->send($salesReport->today());

        $this->info($sent ? 'Resumen diario enviado por Telegram.' : 'No se pudo enviar el resumen diario por Telegram.');
    }

    /**
     * Independiente del checkbox de Telegram (`daily_summary_enabled`) — el correo se manda
     * mientras haya un correo de soporte configurado en Admin > Configuración de correo,
     * mismo criterio ya usado para los avisos internos de tickets (MailSetting::current()->username).
     */
    private function sendEmail(SalesReportBuilder $salesReport): void
    {
        $adminEmail = MailSetting::current()->username;

        if (! $adminEmail) {
            $this->info('Resumen diario por correo sin enviar: no hay correo de soporte configurado.');

            return;
        }

        $stats = $salesReport->todayStats();

        Notification::route('mail', $adminEmail)->notify(new AdminDailySalesSummary(
            $stats['date']->format('d/m/Y'),
            $stats['paid_count'],
            '$'.number_format($stats['revenue'], 2).' USD',
            $stats['trial_count'],
            $stats['total_count'],
        ));

        $this->info("Resumen diario por correo enviado a {$adminEmail}.");
    }
}
