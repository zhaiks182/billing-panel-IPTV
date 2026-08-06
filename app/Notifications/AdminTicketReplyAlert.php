<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso interno por correo cuando el cliente/invitado responde un ticket existente — ver
 * AdminNewTicketAlert (mismo patrón, para la respuesta en vez del ticket nuevo).
 */
class AdminTicketReplyAlert extends Notification
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

        return EmailTemplate::mail('ticket_admin_reply', [
            'ticket_id' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'customer_name' => $ticket->customerName(),
            'message' => $this->message,
            'ticket_url' => route('admin.tickets.show', $ticket),
        ])->replyTo($ticket->customerEmail(), $ticket->customerName());
    }
}
