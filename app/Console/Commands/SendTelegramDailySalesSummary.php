<?php

namespace App\Console\Commands;

use App\Models\TelegramSetting;
use App\Services\Telegram\SalesReportBuilder;
use App\Services\Telegram\TelegramNotifier;
use Illuminate\Console\Command;

class SendTelegramDailySalesSummary extends Command
{
    protected $signature = 'telegram:daily-summary';

    protected $description = 'Envía por Telegram el resumen de ventas del día (comando programado, ver Admin > Telegram)';

    public function handle(TelegramNotifier $telegram, SalesReportBuilder $salesReport): int
    {
        $settings = TelegramSetting::current();

        if (! $settings->isActive() || ! $settings->daily_summary_enabled) {
            $this->info('Resumen diario desactivado o Telegram sin configurar — no se envía nada.');

            return self::SUCCESS;
        }

        $sent = $telegram->send($salesReport->today());

        $this->info($sent ? 'Resumen diario enviado.' : 'No se pudo enviar el resumen diario.');

        return self::SUCCESS;
    }
}
