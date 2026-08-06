<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplied extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket, public string $replyMessage)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return EmailTemplate::mail('ticket_reply', [
            'user_name' => $this->ticket->customerName(),
            'ticket_id' => (string) $this->ticket->id,
            'subject' => $this->ticket->subject,
            'reply_message' => $this->replyMessage,
            'ticket_url' => $this->ticket->publicUrl(),
        ]);
    }
}
