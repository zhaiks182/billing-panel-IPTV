@csrf
@if (isset($category)) @method('PUT') @endif

<div>
    <x-input-label for="name" value="{{ __('Nombre') }}" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required
                  value="{{ old('name', $category->name ?? '') }}" />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="description" value="{{ __('Descripción') }}" />
    <textarea id="description" name="description" rows="3"
              class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm">{{ old('description', $category->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="sort_order" value="{{ __('Orden (menor aparece primero)') }}" />
    <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-40"
                  value="{{ old('sort_order', $category->sort_order ?? 0) }}" />
    <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
</div>

<div class="mt-4">
    <label class="inline-flex items-center">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
               class="rounded border-steel bg-panel text-brand-500 focus:ring-brand-500">
        <span class="ms-2 text-sm text-dim">{{ __('Activa (visible en la landing)') }}</span>
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>{{ __('Guardar') }}</x-primary-button>
    <a href="{{ route('admin.categorias.index') }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
</div>
