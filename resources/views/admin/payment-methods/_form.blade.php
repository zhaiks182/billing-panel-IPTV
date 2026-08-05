@csrf
@if (isset($paymentMethod)) @method('PUT') @endif

<div>
    <x-input-label for="name" value="{{ __('Nombre') }}" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required
                  value="{{ old('name', $paymentMethod->name ?? '') }}" />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="instructions" value="{{ __('Instrucciones (se muestran al cliente al pagar)') }}" />
    <textarea id="instructions" name="instructions" rows="5"
              class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm"
              placeholder="{{ __('Ej: Transferencia a cuenta de ahorros #1234567, Banco X, a nombre de...') }}">{{ old('instructions', $paymentMethod->instructions ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('instructions')" class="mt-2" />
</div>

<div class="mt-4">
    <label class="inline-flex items-center">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $paymentMethod->is_active ?? true) ? 'checked' : '' }}
               class="rounded border-steel bg-panel text-brand-500 focus:ring-brand-500">
        <span class="ms-2 text-sm text-dim">{{ __('Activo (disponible al pagar)') }}</span>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ __('Guardar') }}</x-primary-button>
    <a href="{{ route('admin.metodos-pago.index') }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
</div>
