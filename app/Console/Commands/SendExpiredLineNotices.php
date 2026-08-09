<?php

namespace App\Console\Commands;

use App\Models\Line;
use App\Notifications\LineExpired;
use Illuminate\Console\Command;

/**
 * A diferencia de lines:send-expiration-reminders (avisa ANTES de vencer), este comando
 * avisa cuando la línea YA venció — se agregó porque una línea real (Roberto Ríos, #38)
 * venció sin que nadie se enterara, ya que el recordatorio existente solo corre antes del
 * vencimiento. También marca la línea como 'expired' (antes se quedaba en 'active' para
 * siempre a nivel de BD, aunque displayStatus() ya la mostrara como vencida por fecha).
 */
class SendExpiredLineNotices extends Command
{
    protected $signature = 'lines:send-expired-notices';

    protected $description = 'Avisa a los clientes cuya línea de pago ya venció y la marca como expired';

    public function handle(): int
    {
        $lines = Line::query()
            ->with(['user', 'order.package'])
            ->where('status', 'active')
            ->where('expires_at', '<=', now())
            ->whereHas('order.package', fn ($q) => $q->where('is_trial', false))
            ->get();

        $sent = 0;

        foreach ($lines as $line) {
            if ($line->user) {
                $line->user->notify(new LineExpired($line));
                $sent++;
            }

            $line->update(['status' => 'expired']);
        }

        $this->info("Avisos de vencimiento enviados: {$sent}");

        return self::SUCCESS;
    }
}
