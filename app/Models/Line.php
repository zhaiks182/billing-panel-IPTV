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
}
