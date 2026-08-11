@csrf
@if (isset($package)) @method('PUT') @endif

<div>
    <x-input-label for="name" value="{{ __('Nombre') }}" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required
                  value="{{ old('name', $package->name ?? '') }}" />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="package_category_id" value="{{ __('Categoría') }}" />
    <select id="package_category_id" name="package_category_id"
            class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm">
        <option value="">{{ __('Sin categoría') }}</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat->id }}" {{ (int) old('package_category_id', $package->package_category_id ?? '') === $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('package_category_id')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="xui_package_id" value="{{ __('Paquete en XUI ONE') }}" />
    @if (count($xuiPackages))
        <select id="xui_package_id" name="xui_package_id"
                class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm">
            <option value="">{{ __('Selecciona un paquete de XUI') }}</option>
            @foreach ($xuiPackages as $xp)
                <option value="{{ $xp['id'] }}" {{ (string) old('xui_package_id', $package->xui_package_id ?? '') === (string) $xp['id'] ? 'selected' : '' }}>
                    {{ $xp['name'] }} (ID {{ $xp['id'] }})
                </option>
            @endforeach
        </select>
    @else
        <x-text-input id="xui_package_id" name="xui_package_id" type="text" class="mt-1 block w-full"
                      placeholder="{{ __('ID del paquete en XUI (no se pudo conectar para listarlos)') }}"
                      value="{{ old('xui_package_id', $package->xui_package_id ?? '') }}" />
    @endif
    <x-input-error :messages="$errors->get('xui_package_id')" class="mt-2" />
    <p class="mt-1 text-xs text-dim-2">{{ __('Al aprobar un pedido de este paquete, se creará/renovará la línea usando este paquete de XUI (define duración, bouquets y conexiones reales).') }}</p>
</div>

<div class="mt-4">
    <x-input-label for="description" value="{{ __('Descripción') }}" />
    <textarea id="description" name="description" rows="3"
              class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm">{{ old('description', $package->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="features" value="{{ __('Características (una por línea)') }}" />
    <textarea id="features" name="features" rows="4"
              class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm font-mono text-sm"
              placeholder="{{ "+8,500 Canales\nSoporte 24/7\nCalidad HD/FHD" }}">{{ old('features', $package->features ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('features')" class="mt-2" />
    <p class="mt-1 text-xs text-dim-2">{{ __('Se muestran como viñetas con check en la tarjeta del paquete.') }}</p>
</div>

<div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div>
        <x-input-label for="price" value="{{ __('Precio (USD)') }}" />
        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" required
                      value="{{ old('price', $package->price ?? '') }}" />
        <x-input-error :messages="$errors->get('price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="duration_days" value="{{ __('Duración') }}" />
        <x-text-input id="duration_days" name="duration_days" type="number" min="1" class="mt-1 block w-full" required
                      value="{{ old('duration_days', $package->duration_days ?? '') }}" />
        <x-input-error :messages="$errors->get('duration_days')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="duration_unit" value="{{ __('Unidad') }}" />
        <select id="duration_unit" name="duration_unit"
                class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm">
            @foreach (['hours' => 'Horas', 'days' => 'Días'] as $value => $label)
                <option value="{{ $value }}" {{ old('duration_unit', $package->duration_unit ?? 'days') === $value ? 'selected' : '' }}>
                    {{ __($label) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('duration_unit')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="max_connections" value="{{ __('Conexiones') }}" />
        <x-text-input id="max_connections" name="max_connections" type="number" min="1" class="mt-1 block w-full" required
                      value="{{ old('max_connections', $package->max_connections ?? 1) }}" />
        <x-input-error :messages="$errors->get('max_connections')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    <x-input-label for="stock_limit" value="{{ __('Cupo disponible (opcional)') }}" />
    <x-text-input id="stock_limit" name="stock_limit" type="number" min="0" class="mt-1 block w-full sm:w-48"
                  placeholder="{{ __('Sin límite') }}"
                  value="{{ old('stock_limit', $package->stock_limit ?? '') }}" />
    <x-input-error :messages="$errors->get('stock_limit')" class="mt-2" />
    <p class="mt-1 text-xs text-dim-2">{{ __('Deja vacío para no limitar las ventas. Si lo llenas, el paquete se marca "Agotado" y deja de poder comprarse al alcanzar este número de pedidos (no cuentan los pedidos cancelados).') }}</p>
</div>

<div class="mt-4 space-y-2">
    <label class="inline-flex items-center">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}
               class="rounded border-steel bg-panel text-brand-500 focus:ring-brand-500">
        <span class="ms-2 text-sm text-dim">{{ __('Activo (visible en la landing)') }}</span>
    </label>
    <label class="flex items-center">
        <input type="checkbox" name="is_trial" value="1"
               {{ old('is_trial', $package->is_trial ?? false) ? 'checked' : '' }}
               class="rounded border-steel bg-panel text-brand-500 focus:ring-brand-500">
        <span class="ms-2 text-sm text-dim">{{ __('Es una prueba gratuita (sin pago, se activa al instante)') }}</span>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ __('Guardar') }}</x-primary-button>
    <a href="{{ route('admin.paquetes.index') }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
</div>
