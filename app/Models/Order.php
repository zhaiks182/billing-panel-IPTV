<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'package_id', 'payment_method_id', 'amount', 'proof_path',
    'customer_note', 'status', 'admin_note', 'is_renewal', 'approved_by', 'approved_at',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_renewal' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function line()
    {
        return $this->hasOne(Line::class);
    }
}
