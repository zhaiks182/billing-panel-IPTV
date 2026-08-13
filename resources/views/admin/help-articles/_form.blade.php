@csrf
@if (isset($article)) @method('PUT') @endif

<div x-data="{
        content: @js($article->content ?? ''),
        uploadingImage: false,
        uploadImage(event) {
            const file = event.target.files[0];
            if (! file) return;

            this.uploadingImage = true;
            const formData = new FormData();
            formData.append('image', file);

            fetch('{{ route('admin.help.articles.upload-image') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    const textarea = this.$refs.content;
                    const placeholder = '<img src=\'' + data.url + '\' alt=\'\'>';
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    this.content = this.content.slice(0, start) + placeholder + this.content.slice(end);
                    this.$nextTick(() => textarea.focus());
                })
                .catch(() => alert('{{ __('No se pudo subir la imagen.') }}'))
                .finally(() => { this.uploadingImage = false; event.target.value = ''; });
        },
    }">
    <div>
        <x-input-label for="help_category_id" value="{{ __('Categoría') }}" />
        <select id="help_category_id" name="help_category_id" required
                class="mt-1 block w-full rounded-md border-steel bg-panel text-paper shadow-sm">
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ (int) old('help_category_id', $article->help_category_id ?? '') === $cat->id ? 'selected' : '' }}>
                    {{ $cat->icon }} {{ $cat->name }}{{ $cat->audience === 'internal' ? ' (interna)' : '' }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('help_category_id')" class="mt-2" />
    </div>

    <div class="mt-4 flex gap-4">
        <div class="w-24">
            <x-input-label for="icon" value="{{ __('Ícono') }}" />
            <x-text-input id="icon" name="icon" type="text" class="mt-1 block w-full text-center text-lg" maxlength="10"
                          placeholder="📺" value="{{ old('icon', $article->icon ?? '') }}" />
            <x-input-error :messages="$errors->get('icon')" class="mt-2" />
        </div>
        <div class="flex-1">
            <x-input-label for="title" value="{{ __('Título') }}" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required
                          value="{{ old('title', $article->title ?? '') }}" />
            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>
    </div>

    <div class="mt-4">
        <x-input-label for="excerpt" value="{{ __('Resumen corto (opcional, se muestra en el listado)') }}" />
        <x-text-input id="excerpt" name="excerpt" type="text" class="mt-1 block w-full"
                      value="{{ old('excerpt', $article->excerpt ?? '') }}" />
        <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
    </div>

    <div class="mt-4">
        <div class="flex items-center justify-between">
            <x-input-label for="content" value="{{ __('Contenido (HTML)') }}" />
            <label class="text-xs text-brand-400 hover:underline cursor-pointer">
                <span x-show="! uploadingImage">{{ __('📷 Insertar imagen') }}</span>
                <span x-show="uploadingImage" x-cloak>{{ __('Subiendo…') }}</span>
                <input type="file" accept="image/*" class="hidden" @change="uploadImage($event)" :disabled="uploadingImage">
            </label>
        </div>
        <textarea id="content" name="content" rows="18" x-model="content" x-ref="content"
                  class="mt-1 block w-full rounded-md border-steel bg-panel text-paper font-mono text-xs shadow-sm">{{ old('content', $article->content ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('content')" class="mt-2" />
        <p class="mt-2 text-xs text-dim-2">
            {{ __('Usa tags semánticos simples: <h2>, <h3>, <p>, <ul>/<ol>/<li>, <strong>, <a>, <img>, <code>. Se estilizan automáticamente al mostrarse. La imagen se inserta donde tengas el cursor.') }}
        </p>

        <p class="mt-3 text-xs text-dim-2">{{ __('Vista previa:') }}</p>
        <div class="mt-1 bg-ink rounded-md border border-steel p-4 max-h-96 overflow-y-auto">
            <div class="help-content" x-html="content"></div>
        </div>
    </div>

    <div class="mt-4">
        <x-input-label for="sort_order" value="{{ __('Orden (menor aparece primero)') }}" />
        <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-40"
                      value="{{ old('sort_order', $article->sort_order ?? 0) }}" />
        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
    </div>

    <div class="mt-4">
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $article->is_active ?? true) ? 'checked' : '' }}
                   class="rounded border-steel bg-panel text-brand-500 focus:ring-brand-500">
            <span class="ms-2 text-sm text-dim">{{ __('Activo') }}</span>
        </label>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <x-primary-button>{{ __('Guardar') }}</x-primary-button>
        <a href="{{ route('admin.help.articles.index') }}" class="text-sm text-dim hover:text-paper">{{ __('Cancelar') }}</a>
    </div>
</div>
