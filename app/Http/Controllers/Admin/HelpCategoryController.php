<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpCategory;
use App\Support\Sluggable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HelpCategoryController extends Controller
{
    public function index()
    {
        $categories = HelpCategory::withCount('articles')->orderBy('audience')->orderBy('sort_order')->get();

        return view('admin.help-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.help-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['slug'] = Sluggable::unique('help_categories', $validated['name']);

        HelpCategory::create($validated);

        return redirect()->route('admin.help.categories.index')->with('status', 'Categoría creada.');
    }

    public function edit(HelpCategory $category)
    {
        return view('admin.help-categories.edit', compact('category'));
    }

    public function update(Request $request, HelpCategory $category)
    {
        $validated = $this->validated($request);

        if ($validated['name'] !== $category->name) {
            $validated['slug'] = Sluggable::unique('help_categories', $validated['name'], $category->id);
        }

        $category->update($validated);

        return redirect()->route('admin.help.categories.index')->with('status', 'Categoría actualizada.');
    }

    public function destroy(HelpCategory $category)
    {
        $category->delete();

        return back()->with('status', 'Categoría eliminada.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'audience' => ['required', Rule::in(['public', 'internal'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
