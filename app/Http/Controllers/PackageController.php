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
            ->with(['packages' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->filter(fn ($category) => $category->packages->isNotEmpty());

        $uncategorized = Package::whereNull('package_category_id')
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        $trialPackage = Package::where('is_trial', true)->where('is_active', true)->first();

        $trialAlreadyUsed = $trialPackage
            && auth()->check()
            && ! auth()->user()->isAdmin()
            && auth()->user()->orders()->whereHas('package', fn ($q) => $q->where('is_trial', true))->exists();

        return view('packages.index', compact('categories', 'uncategorized', 'trialPackage', 'trialAlreadyUsed'));
    }

    public function category(PackageCategory $category)
    {
        abort_unless($category->is_active, 404);

        $packages = $category->packages()->where('is_active', true)->get();

        return view('packages.category', compact('category', 'packages'));
    }
}
