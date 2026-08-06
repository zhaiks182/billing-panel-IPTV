<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'guest_name', 'guest_email', 'access_token', 'line_id', 'order_id',
    'category', 'priority', 'status', 'subject', 'assigned_admin_id',
    'first_response_at', 'resolution', 'closed_at',
])]
class Ticket extends Model
{
    protected function casts(): array
    {
        return [
            'first_response_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at')->orderBy('id');
    }

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    public function publicUrl(): string
    {
        return $this->isGuest()
            ? route('tickets.show', ['ticket' => $this, 'token' => $this->access_token])
            : route('tickets.show', $this);
    }

    public function customerName(): string
    {
        return $this->user->name ?? $this->guest_name ?? '—';
    }

    public function customerEmail(): string
    {
        return $this->user->email ?? $this->guest_email ?? '—';
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'installation' => 'Instalación',
            'credentials' => 'Credenciales',
            'payment' => 'Pago',
            'renewal' => 'Renovación',
            'connection_limit' => 'Límite de conexiones',
            'intermittent_service' => 'Servicio intermitente',
            'channels_content' => 'Canales o contenido',
            default => 'Otro',
        };
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            'high' => 'Alta',
            'low' => 'Baja',
            default => 'Media',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'answered' => 'Respondido',
            'in_progress' => 'En progreso',
            'closed' => 'Cerrado',
            default => 'Abierto',
        };
    }
}
