<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Historial de acciones de admin sobre una línea (renovar, suspender, cambiar contraseña,
 * etc.) — a pedido del usuario, para poder auditar un reclamo de cliente ("¿quién hizo qué
 * y cuándo?"). Se registra tanto el éxito como el fallo de cada acción.
 */
#[Fillable(['line_id', 'admin_id', 'action', 'description'])]
class LineActivityLog extends Model
{
    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function record(?Line $line, string $action, string $description): self
    {
        return self::create([
            'line_id' => $line?->id,
            'admin_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}
