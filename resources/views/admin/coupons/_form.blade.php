@csrf
@if (isset($coupon)) @method('PUT') @endif

<div>
    <x-input-label for="code" value="{{ __('Código') }}" />
    <x-text-input id="code" name="code" type="text" class="mt-1 block w-full uppercase" required
                  placeholder="BLACKFRIDAY20"
                  value="{{ old('code', $coupon->code ?? '') }}" />
    <x-input-error :messages="$errors->get('code')" class="mt-2" />
</div>

<div class="mt-4 grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="type" value="{{ __('Tipo') }}" />
        <select id="type" name="type" class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm">
            <option value="percent" {{ old('type', $coupon->type ?? 'percent') === 'percent' ? 'selected' : '' }}>{{ __('Porcentaje (%)') }}</option>
            <option value="fixed" {{ old('type', $coupon->type ?? 'percent') === 'fixed' ? 'selected' : '' }}>{{ __('Monto fijo ($)') }}</option>
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="value" value="{{ __('Valor') }}" />
        <x-text-input id="value" name="value" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                      value="{{ old('value', $coupon->value ?? '') }}" />
        <x-input-error :messages="$errors->get('value')" class="mt-2" />
    </div>
</div>

<div class="mt-4 grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="max_redemptions" value="{{ __('Usos máximos (vacío = ilimitado)') }}" />
        <x-text-input id="max_redemptions" name="max_redemptions" type="number" min="1" class="mt-1 block w-full"
                      value="{{ old('max_redemptions', $coupon->max_redemptions ?? '') }}" />
        <x-input-error :messages="$errors->get('max_redemptions')" class="mt-2" />
        @if (isset($coupon))
            <p class="mt-1 text-xs text-dim-2">{{ __('Canjeado :count vez(veces) hasta ahora.', ['count' => $coupon->redeemedCount()]) }}</p>
        @endif
    </div>

    <div>
        <x-input-label for="expires_at" value="{{ __('Vence (vacío = sin vencimiento)') }}" />
        <x-text-input id="expires_at" name="expires_at" type="date" class="mt-1 block w-full"
                      value="{{ old('expires_at', optional($coupon->expires_at ?? null)->format('Y-m-d')) }}" />
        <x-input-error :messages="$errors->get('expires_at')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    <label class="inline-flex items-center">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}
               class="rounded border-steel bg-panel text-brand-500 focus:ring-brand-500">
        <span class="ms-2 text-sm text-dim">{{ __('Activo') }}</span>
    </label>
    <p class="mt-2 text-xs text-dim-2">
        {{ __('Aplica a cualquier paquete de pago en la tienda (no a paquetes de prueba gratuita).') }}
    </p>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ __('Guardar') }}</x-primary-button>
    <a href="{{ route('admin.cupones.index') }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
</div>
