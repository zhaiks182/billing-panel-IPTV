<?php

namespace App\Console\Commands;

use App\Models\Line;
use App\Notifications\LineExpiringSoon;
use Illuminate\Console\Command;

class SendLineExpirationReminders extends Command
{
    protected $signature = 'lines:send-expiration-reminders
        {--first=7 : Días de anticipación del primer aviso}
        {--second=3 : Días de anticipación del segundo aviso}';

    protected $description = 'Envía hasta dos recordatorios por correo (a --first y --second días de vencer) a los clientes cuya línea de pago vence pronto';

    public function handle(): int
    {
        $first = (int) $this->option('first');
        $second = (int) $this->option('second');

        $sent = $this->sendStage('reminder_7d_sent_at', $second, $first)
            + $this->sendStage('reminder_3d_sent_at', 0, $second);

        $this->info("Recordatorios enviados: {$sent}");

        return self::SUCCESS;
    }

    /**
     * Manda el aviso a las líneas activas cuyo vencimiento cae en la ventana
     * (ahora + $fromDays, ahora + $toDays] y que todavía no recibieron este aviso en
     * particular (columna $column) — cada uno de los dos avisos se manda una sola vez por
     * línea. Las ventanas de las dos etapas no se solapan (la de 7 días termina donde
     * empieza la de 3), para que una línea nunca reciba los dos correos el mismo día salvo
     * que el comando lleve varios días sin correr.
     */
    private function sendStage(string $column, int $fromDays, int $toDays): int
    {
        $lines = Line::query()
            ->with(['user', 'order.package'])
            ->where('status', 'active')
            ->whereNull($column)
            ->whereBetween('expires_at', [now()->addDays($fromDays), now()->addDays($toDays)])
            ->whereHas('order.package', fn ($q) => $q->where('is_trial', false))
            ->get();

        foreach ($lines as $line) {
            if (! $line->user) {
                continue;
            }

            $line->user->notify(new LineExpiringSoon($line));
            $line->update([$column => now()]);
        }

        return $lines->count();
    }
}
