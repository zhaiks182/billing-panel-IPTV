<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('name')->get();

        return view('admin.payment-methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        return view('admin.payment-methods.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        PaymentMethod::create($validated);

        return redirect()->route('admin.metodos-pago.index')->with('status', 'Método de pago creado.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('admin.payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $this->validated($request);

        $paymentMethod->update($validated);

        return redirect()->route('admin.metodos-pago.index')->with('status', 'Método de pago actualizado.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return back()->with('status', 'Método de pago eliminado.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
