<?php

namespace App\Http\Controllers;

use App\Models\HelpArticle;
use App\Models\HelpCategory;

class HelpController extends Controller
{
    public function index()
    {
        $categories = $this->sidebarCategories();

        return view('help.index', compact('categories'));
    }

    public function category(HelpCategory $category)
    {
        abort_unless($category->isPublic() && $category->is_active, 404);

        $categories = $this->sidebarCategories();
        $articles = $category->articles()->where('is_active', true)->orderBy('sort_order')->get();

        return view('help.category', compact('category', 'articles', 'categories'));
    }

    public function article(HelpCategory $category, HelpArticle $article)
    {
        abort_unless($category->isPublic() && $category->is_active, 404);
        abort_unless($article->help_category_id === $category->id && $article->is_active, 404);

        $categories = $this->sidebarCategories();

        $siblingArticles = $category->articles()->where('is_active', true)->orderBy('sort_order')->get();
        $position = $siblingArticles->search(fn ($a) => $a->id === $article->id);
        $previousArticle = $position > 0 ? $siblingArticles->get($position - 1) : null;
        $nextArticle = $position < $siblingArticles->count() - 1 ? $siblingArticles->get($position + 1) : null;

        return view('help.article', compact('category', 'article', 'categories', 'previousArticle', 'nextArticle'));
    }

    /**
     * Árbol completo (todas las categorías públicas + sus artículos) para el sidebar
     * jerárquico, compartido por las 3 vistas — mismo patrón que iptv-help.net, a pedido
     * del usuario. Con ~21 artículos en total, cargar todo de una vez es más simple y
     * barato que paginar/lazy-load el árbol.
     */
    private function sidebarCategories()
    {
        return HelpCategory::where('audience', 'public')
            ->where('is_active', true)
            ->with(['articles' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }
}
