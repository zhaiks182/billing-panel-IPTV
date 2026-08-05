<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['enabled', 'bot_token', 'chat_id'])]
class TelegramSetting extends Model
{
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'bot_token' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }

    public function isActive(): bool
    {
        return $this->enabled && $this->bot_token && $this->chat_id;
    }
}
