<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket, public string $firstMessage)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return EmailTemplate::mail('ticket_created', [
            'user_name' => $this->ticket->customerName(),
            'ticket_id' => (string) $this->ticket->id,
            'subject' => $this->ticket->subject,
            'category_label' => $this->ticket->categoryLabel(),
            'priority_label' => $this->ticket->priorityLabel(),
            'message' => $this->firstMessage,
            'ticket_url' => $this->ticket->publicUrl(),
        ]);
    }
}
