<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['panel_url', 'access_code', 'api_token', 'stream_url', 'server_url'])]
class XuiSetting extends Model
{
    protected function casts(): array
    {
        return [
            'api_token' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }
}
