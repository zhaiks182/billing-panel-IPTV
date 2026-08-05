<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Categorías de paquetes') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.paquetes.index') }}" class="text-sm text-dim underline self-center">{{ __('Ver paquetes') }}</a>
                <a href="{{ route('admin.categorias.create') }}" class="bg-brand-600 text-white px-4 py-2 rounded-md text-sm hover:bg-brand-700">
                    {{ __('Nueva categoría') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-panel border border-steel rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-steel">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Orden') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Nombre') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Paquetes') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-6 py-4 text-sm text-dim">{{ $category->sort_order }}</td>
                                <td class="px-6 py-4 text-sm text-paper">{{ $category->name }}</td>
                                <td class="px-6 py-4 text-sm text-dim">{{ $category->packages_count }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $category->is_active ? 'bg-brand-500/10 text-brand-400' : 'bg-steel text-dim' }}">
                                        {{ $category->is_active ? __('Activa') : __('Inactiva') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right space-x-2">
                                    <a href="{{ route('admin.categorias.edit', $category) }}" class="text-paper underline">{{ __('Editar') }}</a>
                                    <form method="POST" action="{{ route('admin.categorias.destroy', $category) }}" class="inline"
                                          onsubmit="return confirm('{{ __('¿Eliminar esta categoría? Los paquetes quedarán sin categoría.') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-700 underline">{{ __('Eliminar') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-dim-2">{{ __('No hay categorías.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
