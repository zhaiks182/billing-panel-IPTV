<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketClosed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return EmailTemplate::mail('ticket_closed', [
            'user_name' => $this->ticket->customerName(),
            'ticket_id' => (string) $this->ticket->id,
            'subject' => $this->ticket->subject,
            'resolution' => $this->ticket->resolution ?? '—',
            'ticket_url' => $this->ticket->publicUrl(),
        ]);
    }
}
