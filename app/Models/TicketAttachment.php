<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['ticket_message_id', 'path', 'original_name'])]
class TicketAttachment extends Model
{
    public function message()
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
