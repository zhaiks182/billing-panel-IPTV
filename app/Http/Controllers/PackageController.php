<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageCategory;

class PackageController extends Controller
{
    public function index()
    {
        $categories = PackageCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->with(['packages' => fn ($q) => $q->where('is_active', true)
                ->withCount(['orders as sold_count' => fn ($o) => $o->where('status', '!=', 'rejected')])])
            ->get()
            ->filter(fn ($category) => $category->packages->isNotEmpty());

        $uncategorized = Package::whereNull('package_category_id')
            ->where('is_active', true)
            ->withCount(['orders as sold_count' => fn ($q) => $q->where('status', '!=', 'rejected')])
            ->orderBy('price')
            ->get();

        $trialPackage = Package::where('is_trial', true)->where('is_active', true)
            ->withCount(['orders as sold_count' => fn ($q) => $q->where('status', '!=', 'rejected')])
            ->first();

        $trialAlreadyUsed = $trialPackage
            && auth()->check()
            && ! auth()->user()->isAdmin()
            && auth()->user()->orders()->whereHas('package', fn ($q) => $q->where('is_trial', true))->exists();

        return view('packages.index', compact('categories', 'uncategorized', 'trialPackage', 'trialAlreadyUsed'));
    }

    /**
     * "Comprar Servicios" del menú — lleva directo a la primera categoría activa (por ahora
     * solo hay una), con el mismo catálogo con sidebar de categorías. Si no hay ninguna
     * categoría activa todavía, cae de vuelta a la portada normal.
     */
    public function shop()
    {
        $category = PackageCategory::where('is_active', true)->orderBy('sort_order')->first();

        return $category
            ? redirect()->route('packages.category', $category)
            : redirect()->route('home');
    }

    public function category(PackageCategory $category)
    {
        abort_unless($category->is_active, 404);

        $packages = $category->packages()->where('is_active', true)
            ->withCount(['orders as sold_count' => fn ($q) => $q->where('status', '!=', 'rejected')])
            ->get();

        $categories = PackageCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->withCount(['packages' => fn ($q) => $q->where('is_active', true)])
            ->get();

        return view('packages.category', compact('category', 'packages', 'categories'));
    }
}
