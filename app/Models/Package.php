<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['package_category_id', 'xui_package_id', 'name', 'slug', 'description', 'features', 'price', 'duration_days', 'duration_unit', 'max_connections', 'stock_limit', 'force_sold_out', 'is_active', 'is_trial'])]
class Package extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_trial' => 'boolean',
            'force_sold_out' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function category()
    {
        return $this->belongsTo(PackageCategory::class, 'package_category_id');
    }

    public function featureList(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->features))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    public function durationLabel(): string
    {
        $unit = $this->duration_unit === 'hours'
            ? ($this->duration_days === 1 ? 'hora' : 'horas')
            : ($this->duration_days === 1 ? 'día' : 'días');

        return "{$this->duration_days} {$unit}";
    }

    public function durationInDays(): float
    {
        return $this->duration_unit === 'hours'
            ? $this->duration_days / 24
            : $this->duration_days;
    }

    /**
     * "Vendido" = cualquier pedido que no sea `rejected` (pending/approved/activated/error
     * representan una unidad comprometida, ver CLAUDE.md → "Control de stock"). Usa
     * `sold_count` si ya viene precargado vía withCount() (patrón usado en
     * PackageController para evitar N+1); si no, cae a una consulta directa.
     */
    public function soldCount(): int
    {
        if (array_key_exists('sold_count', $this->attributes)) {
            return (int) $this->attributes['sold_count'];
        }

        return $this->orders()->where('status', '!=', 'rejected')->count();
    }

    /**
     * "Agotado" es verdadero si el admin lo marcó a mano (`force_sold_out`, independiente
     * del cupo/conteo) o si se alcanzó el `stock_limit` numérico.
     */
    public function isSoldOut(): bool
    {
        if ($this->force_sold_out) {
            return true;
        }

        return $this->stock_limit !== null && $this->soldCount() >= $this->stock_limit;
    }

    public function availableCount(): ?int
    {
        if ($this->force_sold_out) {
            return 0;
        }

        if ($this->stock_limit === null) {
            return null;
        }

        return max(0, $this->stock_limit - $this->soldCount());
    }
}
