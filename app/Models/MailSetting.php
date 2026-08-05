<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'mailer', 'host', 'port', 'username', 'password', 'encryption', 'from_address', 'from_name',
])]
class MailSetting extends Model
{
    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }
}
