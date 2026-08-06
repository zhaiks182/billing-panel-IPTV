<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticket_id', 'user_id', 'message'])]
class TicketMessage extends Model
{
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function isFromAdmin(): bool
    {
        return (bool) $this->user?->isAdmin();
    }

    public function authorName(): string
    {
        return $this->user->name ?? $this->ticket->guest_name ?? '—';
    }
}
