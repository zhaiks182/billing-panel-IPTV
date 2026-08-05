<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description', 'sort_order', 'is_active'])]
class PackageCategory extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function packages()
    {
        return $this->hasMany(Package::class)->orderBy('price');
    }
}
