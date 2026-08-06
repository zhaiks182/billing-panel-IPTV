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
 * Aviso de Telegram cuando se cierra un ticket — mismo motivo que
 * SendAdminReplyTelegramNotice (antes no se avisaba nada al cerrar).
 */
class SendTicketClosedTelegramNotice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(public Ticket $ticket)
    {
    }

    public function handle(TelegramNotifier $telegram): void
    {
        $ticket = $this->ticket;

        $telegram->send(
            "🔒 <b>Ticket #{$ticket->ticket_number} cerrado</b>\n\n".
            "Cliente: {$ticket->customerName()}\n".
            "Asunto: {$ticket->subject}\n\n".
            "Solución:\n".($ticket->resolution ?: 'Sin solución especificada.')."\n\n".
            'Ver ticket: '.route('admin.tickets.show', $ticket)
        );
    }
}
