<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso interno por correo (a la bandeja de soporte, no al cliente) cuando se crea un
 * ticket — mismo diseño de marca que `TicketCreated` (la que sí es para el cliente), a
 * pedido del usuario 2026-08-06 (antes era texto plano vía Mail::raw()). Se envía desde
 * App\Jobs\SendNewTicketAdminAlert, ya en cola, así que esta clase no necesita
 * implementar ShouldQueue por su cuenta.
 */
class AdminNewTicketAlert extends Notification
{
    public function __construct(public Ticket $ticket, public string $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;

        return EmailTemplate::mail('ticket_admin_new', [
            'ticket_id' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'customer_name' => $ticket->customerName(),
            'customer_email' => $ticket->customerEmail(),
            'category_label' => $ticket->categoryLabel(),
            'priority_label' => $ticket->priorityLabel(),
            'message' => $this->message,
            'ticket_url' => route('admin.tickets.show', $ticket),
        ])->replyTo($ticket->customerEmail(), $ticket->customerName());
    }
}
