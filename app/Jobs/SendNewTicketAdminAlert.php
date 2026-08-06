<?php

namespace App\Jobs;

use App\Models\MailSetting;
use App\Models\Ticket;
use App\Notifications\AdminNewTicketAlert;
use App\Services\Telegram\TelegramNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

/**
 * Avisos internos (Telegram + correo a soporte) cuando se crea un ticket — encolado desde
 * 2026-08-06 porque las dos llamadas de red (Telegram + SMTP) bloqueaban la respuesta al
 * cliente varios segundos. Requiere que el worker de la cola esté corriendo en el VPS
 * (`systemctl status billing-panel-queue`), ver CLAUDE.md "Módulo de Tickets de Soporte".
 */
class SendNewTicketAdminAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(public Ticket $ticket, public string $firstMessage)
    {
    }

    public function handle(TelegramNotifier $telegram): void
    {
        $ticket = $this->ticket;

        $telegram->send(
            "🎫 <b>Nuevo ticket #{$ticket->ticket_number}</b>\n\n".
            "Cliente: {$ticket->customerName()} ({$ticket->customerEmail()})\n".
            "Categoría: {$ticket->categoryLabel()}\n".
            "Prioridad: {$ticket->priorityLabel()}\n".
            "Asunto: {$ticket->subject}\n\n".
            "Mensaje:\n{$this->firstMessage}\n\n".
            'Ver ticket: '.route('admin.tickets.show', $ticket)
        );

        $adminEmail = MailSetting::current()->username;

        if (! $adminEmail) {
            return;
        }

        Notification::route('mail', $adminEmail)->notify(new AdminNewTicketAlert($ticket, $this->firstMessage));
    }
}
