<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'type', 'value', 'max_redemptions', 'expires_at', 'is_active'])]
class Coupon extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * "Canjeado" = cualquier pedido que no sea `rejected`, en vivo (`COUNT`), sin columna
     * contadora desnormalizada — mismo criterio que `Package::soldCount()` (ver CLAUDE.md
     * "Control de stock por paquete").
     */
    public function redeemedCount(): int
    {
        return $this->orders()->where('status', '!=', 'rejected')->count();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasReachedLimit(): bool
    {
        return $this->max_redemptions !== null && $this->redeemedCount() >= $this->max_redemptions;
    }

    public function isRedeemable(): bool
    {
        return $this->is_active && ! $this->isExpired() && ! $this->hasReachedLimit();
    }

    /**
     * Nunca supera el precio del paquete, para no dejar un total negativo.
     */
    public function discountFor(float $price): float
    {
        $discount = $this->type === 'percent'
            ? $price * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($discount, $price), 2);
    }
}
