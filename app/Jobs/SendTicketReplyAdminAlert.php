<?php

namespace App\Jobs;

use App\Models\MailSetting;
use App\Models\Ticket;
use App\Notifications\AdminTicketReplyAlert;
use App\Services\Telegram\TelegramNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

/**
 * Avisos internos (Telegram + correo a soporte) cuando el cliente/invitado responde un
 * ticket existente — mismo motivo y patrón que SendNewTicketAdminAlert.
 */
class SendTicketReplyAdminAlert implements ShouldQueue
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
            "💬 <b>Nueva respuesta de cliente en ticket #{$ticket->id}</b>\n\n".
            "Cliente: {$ticket->customerName()}\n".
            "Asunto: {$ticket->subject}\n\n".
            "Mensaje:\n{$this->message}\n\n".
            'Ver ticket: '.route('admin.tickets.show', $ticket)
        );

        $adminEmail = MailSetting::current()->username;

        if (! $adminEmail) {
            return;
        }

        Notification::route('mail', $adminEmail)->notify(new AdminTicketReplyAlert($ticket, $this->message));
    }
}
