<?php

namespace App\Http\Controllers;

use App\Models\Package;

class CartController extends Controller
{
    public function store(Package $package)
    {
        abort_unless($package->is_active, 404);

        session(['cart_package_id' => $package->id]);

        return redirect()->route('cart.index');
    }

    public function index()
    {
        $package = Package::find(session('cart_package_id'));

        if (! $package || ! $package->is_active) {
            return redirect()->route('home')->with('status', 'Tu carrito está vacío. Elige un paquete para continuar.');
        }

        return view('cart.index', compact('package'));
    }

    public function destroy()
    {
        session()->forget('cart_package_id');

        return redirect()->route('home');
    }
}
