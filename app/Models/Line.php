<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'order_id', 'xui_line_id', 'xui_username', 'xui_password',
    'm3u_url', 'max_connections', 'expires_at', 'status', 'reminder_sent_at',
])]
class Line extends Model
{
    /**
     * Ventana de "por vencer" usada tanto por el badge de estado como por el dashboard
     * y el filtro de Admin > Líneas — un solo lugar para no desincronizar los tres.
     */
    public const EXPIRING_SOON_DAYS = 7;

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Estado mostrado en Admin > Líneas — no es la columna `status` cruda: "vencida"/"por
     * vencer" se calculan comparando `expires_at` con ahora (mismo criterio ya usado en
     * dashboard.blade.php y SendLineExpirationReminders), "suspendida" sí es el valor real
     * de `status` (única forma de llegar ahí es la acción manual de un admin).
     */
    public function displayStatus(): string
    {
        if ($this->status === 'suspended') {
            return 'suspended';
        }

        if (! $this->expires_at || $this->expires_at->isPast()) {
            return 'expired';
        }

        if (now()->diffInDays($this->expires_at) <= self::EXPIRING_SOON_DAYS) {
            return 'expiring_soon';
        }

        return 'active';
    }

    public static function displayStatusLabels(): array
    {
        return [
            'active' => 'Activa',
            'expiring_soon' => 'Por vencer',
            'expired' => 'Vencida',
            'suspended' => 'Suspendida',
        ];
    }
}
