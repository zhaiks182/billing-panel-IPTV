<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['package_category_id', 'xui_package_id', 'name', 'slug', 'description', 'features', 'price', 'duration_days', 'duration_unit', 'max_connections', 'is_active', 'is_trial'])]
class Package extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_trial' => 'boolean',
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
}
