<?php

namespace App\Http\Controllers;

use App\Models\HelpArticle;
use App\Models\HelpCategory;

class HelpController extends Controller
{
    public function index()
    {
        $categories = HelpCategory::where('audience', 'public')
            ->where('is_active', true)
            ->withCount(['articles' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        return view('help.index', compact('categories'));
    }

    public function category(HelpCategory $category)
    {
        abort_unless($category->isPublic() && $category->is_active, 404);

        $articles = $category->articles()->where('is_active', true)->orderBy('sort_order')->get();

        $categories = HelpCategory::where('audience', 'public')
            ->where('is_active', true)
            ->withCount(['articles' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        return view('help.category', compact('category', 'articles', 'categories'));
    }

    public function article(HelpCategory $category, HelpArticle $article)
    {
        abort_unless($category->isPublic() && $category->is_active, 404);
        abort_unless($article->help_category_id === $category->id && $article->is_active, 404);

        return view('help.article', compact('category', 'article'));
    }
}
