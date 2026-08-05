<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Editar categoría') }}</h2>
            <x-close-link :href="route('admin.categorias.index')" />
        </div>
    </x-slot>

    <div class="py-12" x-data @keydown.escape.window="window.location = '{{ route('admin.categorias.index') }}'">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-panel border border-steel rounded-lg p-6">
                <form method="POST" action="{{ route('admin.categorias.update', $category) }}">
                    @include('admin.package-categories._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
