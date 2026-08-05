<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['enabled', 'site_key', 'secret_key'])]
class TurnstileSetting extends Model
{
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'secret_key' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }

    public function isActive(): bool
    {
        return $this->enabled && $this->site_key && $this->secret_key;
    }
}
