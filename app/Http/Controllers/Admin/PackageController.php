<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Services\Xui\XuiOneClient;
use App\Support\Sluggable;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::with('category')->orderBy('price')->get();

        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        $categories = PackageCategory::orderBy('sort_order')->get();
        $xuiPackages = $this->fetchXuiPackages();

        return view('admin.packages.create', compact('categories', 'xuiPackages'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['slug'] = Sluggable::unique('packages', $validated['name']);

        // Un paquete recién creado no tiene pedidos todavía, así que el punto de partida
        // del cupo es 0 (ver Package::soldSinceLimit()) — sin esto, "vendidos desde hoy"
        // quedaría sin definir hasta el próximo cambio de cupo.
        if ($validated['stock_limit'] !== null) {
            $validated['stock_baseline_sold'] = 0;
        }

        Package::create($validated);

        return redirect()->route('admin.paquetes.index')->with('status', 'Paquete creado.');
    }

    public function edit(Package $package)
    {
        $categories = PackageCategory::orderBy('sort_order')->get();
        $xuiPackages = $this->fetchXuiPackages();

        return view('admin.packages.edit', compact('package', 'categories', 'xuiPackages'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $this->validated($request);

        if ($validated['name'] !== $package->name) {
            $validated['slug'] = Sluggable::unique('packages', $validated['name'], $package->id);
        }

        // El cupo cuenta "vendidos desde que se puso/cambió el número" (no el historial
        // completo, ver Package::soldSinceLimit()) — a pedido del usuario, que probó con un
        // paquete que ya tenía ventas reales y esperaba que el cupo nuevo arrancara en 0.
        // Solo se recongela el punto de partida cuando el número realmente cambia, para no
        // resetear el cupo cada vez que se edita cualquier otro campo del paquete.
        if ($validated['stock_limit'] !== $package->stock_limit) {
            $validated['stock_baseline_sold'] = $validated['stock_limit'] !== null
                ? $package->orders()->where('status', '!=', 'rejected')->count()
                : null;
        }

        $package->update($validated);

        return redirect()->route('admin.paquetes.index')->with('status', 'Paquete actualizado.');
    }

    public function destroy(Package $package)
    {
        if ($package->orders()->exists()) {
            $package->update(['is_active' => false]);

            return back()->with('status', "«{$package->name}» tiene pedidos asociados y no se puede eliminar; se desactivó en su lugar (ya no aparece en la tienda).");
        }

        $package->delete();

        return back()->with('status', 'Paquete eliminado.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'package_category_id' => ['nullable', 'exists:package_categories,id'],
            'xui_package_id' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'features' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'duration_unit' => ['required', 'in:hours,days'],
            'max_connections' => ['required', 'integer', 'min:1', 'max:255'],
            'stock_limit' => ['nullable', 'integer', 'min:0'],
            'force_sold_out' => ['boolean'],
            'is_active' => ['boolean'],
            'is_trial' => ['boolean'],
        ]);

        $validated['force_sold_out'] = $request->boolean('force_sold_out');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_trial'] = $request->boolean('is_trial');

        return $validated;
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    private function fetchXuiPackages(): array
    {
        try {
            return collect((new XuiOneClient)->getPackages())
                ->map(fn ($p) => ['id' => $p['id'], 'name' => $p['package_name'] ?? $p['id']])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
