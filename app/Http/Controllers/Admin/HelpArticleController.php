<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpArticle;
use App\Models\HelpCategory;
use App\Support\Sluggable;
use Illuminate\Http\Request;

class HelpArticleController extends Controller
{
    public function index()
    {
        $articles = HelpArticle::with('category')->orderBy('sort_order')->get();

        return view('admin.help-articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = HelpCategory::orderBy('audience')->orderBy('sort_order')->get();

        return view('admin.help-articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['slug'] = Sluggable::unique('help_articles', $validated['title']);

        HelpArticle::create($validated);

        return redirect()->route('admin.help.articles.index')->with('status', 'Artículo creado.');
    }

    /**
     * Vista de lectura pulida — no hay una página pública a la que "ir a previsualizar" un
     * artículo interno, así que esta es la forma normal de leer la documentación interna.
     * También sirve para previsualizar un artículo público antes de publicarlo.
     */
    public function show(HelpArticle $article)
    {
        $article->load('category');

        return view('admin.help-articles.show', compact('article'));
    }

    public function edit(HelpArticle $article)
    {
        $categories = HelpCategory::orderBy('audience')->orderBy('sort_order')->get();

        return view('admin.help-articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, HelpArticle $article)
    {
        $validated = $this->validated($request);

        if ($validated['title'] !== $article->title) {
            $validated['slug'] = Sluggable::unique('help_articles', $validated['title'], $article->id);
        }

        $article->update($validated);

        return redirect()->route('admin.help.articles.index')->with('status', 'Artículo actualizado.');
    }

    public function destroy(HelpArticle $article)
    {
        $article->delete();

        return back()->with('status', 'Artículo eliminado.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'help_category_id' => ['required', 'exists:help_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:10'],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
