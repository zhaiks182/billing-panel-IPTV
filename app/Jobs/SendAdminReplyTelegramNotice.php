<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\Telegram\TelegramNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso de Telegram cuando un admin responde un ticket — antes solo se notificaba al
 * cliente por correo, sin avisar a Telegram; el usuario notó que no llegaba nada ahí
 * (el grupo de Telegram tiene varios miembros, útil para que el resto del equipo vea que
 * ya se atendió). Encolado por el mismo motivo de rendimiento que el resto del módulo.
 */
class SendAdminReplyTelegramNotice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(public Ticket $ticket, public string $message)
    {
    }

    public function handle(TelegramNotifier $telegram): void
    {
        $ticket = $this->ticket;

        $telegram->send(
            "✅ <b>Admin respondió el ticket #{$ticket->ticket_number}</b>\n\n".
            "Cliente: {$ticket->customerName()}\n".
            "Asunto: {$ticket->subject}\n\n".
            "Mensaje:\n{$this->message}\n\n".
            'Ver ticket: '.route('admin.tickets.show', $ticket)
        );
    }
}
