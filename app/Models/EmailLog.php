<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['log_uuid', 'user_id', 'to_email', 'subject', 'html_body', 'text_body', 'status'])]
class EmailLog extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
