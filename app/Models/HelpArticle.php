<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['help_category_id', 'title', 'slug', 'excerpt', 'content', 'sort_order', 'is_active'])]
class HelpArticle extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(HelpCategory::class, 'help_category_id');
    }
}
