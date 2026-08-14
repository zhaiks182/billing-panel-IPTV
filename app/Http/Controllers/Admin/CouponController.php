<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderByDesc('created_at')->get();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        Coupon::create($validated);

        return redirect()->route('admin.cupones.index')->with('status', 'Cupón creado.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $this->validated($request, $coupon);

        $coupon->update($validated);

        return redirect()->route('admin.cupones.index')->with('status', 'Cupón actualizado.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return back()->with('status', 'Cupón eliminado.');
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon)],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0.01'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->input('type') === 'percent' && (float) $request->input('value') > 100) {
                $validator->errors()->add('value', 'Un descuento porcentual no puede superar 100.');
            }
        });

        $validated = $validator->validate();

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
