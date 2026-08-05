<?php

namespace App\Console\Commands;

use App\Models\Line;
use App\Notifications\LineExpiringSoon;
use Illuminate\Console\Command;

class SendLineExpirationReminders extends Command
{
    protected $signature = 'lines:send-expiration-reminders {--days=3 : Días de anticipación antes del vencimiento}';

    protected $description = 'Envía un recordatorio por correo a los clientes cuya línea de pago vence pronto';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $lines = Line::query()
            ->with(['user', 'order.package'])
            ->where('status', 'active')
            ->whereNull('reminder_sent_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)])
            ->whereHas('order.package', fn ($q) => $q->where('is_trial', false))
            ->get();

        $sent = 0;

        foreach ($lines as $line) {
            if (! $line->user) {
                continue;
            }

            $line->user->notify(new LineExpiringSoon($line));
            $line->update(['reminder_sent_at' => now()]);
            $sent++;
        }

        $this->info("Recordatorios enviados: {$sent}");

        return self::SUCCESS;
    }
}
