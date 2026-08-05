<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageCategory;
use App\Support\Sluggable;
use Illuminate\Http\Request;

class PackageCategoryController extends Controller
{
    public function index()
    {
        $categories = PackageCategory::withCount('packages')->orderBy('sort_order')->get();

        return view('admin.package-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.package-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['slug'] = Sluggable::unique('package_categories', $validated['name']);

        PackageCategory::create($validated);

        return redirect()->route('admin.categorias.index')->with('status', 'Categoría creada.');
    }

    public function edit(PackageCategory $category)
    {
        return view('admin.package-categories.edit', compact('category'));
    }

    public function update(Request $request, PackageCategory $category)
    {
        $validated = $this->validated($request);

        if ($validated['name'] !== $category->name) {
            $validated['slug'] = Sluggable::unique('package_categories', $validated['name'], $category->id);
        }

        $category->update($validated);

        return redirect()->route('admin.categorias.index')->with('status', 'Categoría actualizada.');
    }

    public function destroy(PackageCategory $category)
    {
        $category->delete();

        return back()->with('status', 'Categoría eliminada.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
