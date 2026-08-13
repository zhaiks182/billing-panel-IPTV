<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'icon', 'slug', 'description', 'audience', 'sort_order', 'is_active'])]
class HelpCategory extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function articles()
    {
        return $this->hasMany(HelpArticle::class);
    }

    public function isPublic(): bool
    {
        return $this->audience === 'public';
    }
}
