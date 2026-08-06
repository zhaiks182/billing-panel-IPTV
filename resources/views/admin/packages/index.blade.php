<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Paquetes') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.categorias.index') }}" class="text-sm text-dim underline self-center">{{ __('Categorías') }}</a>
                <a href="{{ route('admin.paquetes.create') }}" class="bg-brand-600 text-white px-4 py-2 rounded-md text-sm hover:bg-brand-700">
                    {{ __('Nuevo paquete') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-steel">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Nombre') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Categoría') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Precio') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Duración') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Conexiones') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($packages as $package)
                            <tr>
                                <td class="px-6 py-4 text-sm text-paper">
                                    {{ $package->name }}
                                    @if ($package->is_trial)
                                        <span class="ml-1 inline-flex px-1.5 py-0.5 text-xs rounded bg-amber/10 text-amber">{{ __('Demo') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-dim-2">{{ $package->category?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-dim">${{ number_format($package->price, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-dim">{{ $package->durationLabel() }}</td>
                                <td class="px-6 py-4 text-sm text-dim">{{ $package->max_connections }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $package->is_active ? 'bg-brand-500/10 text-brand-400' : 'bg-steel text-dim' }}">
                                        {{ $package->is_active ? __('Activo') : __('Inactivo') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right space-x-2">
                                    <a href="{{ route('admin.paquetes.edit', $package) }}" class="text-paper underline">{{ __('Editar') }}</a>
                                    <form method="POST" action="{{ route('admin.paquetes.destroy', $package) }}" class="inline"
                                          onsubmit="return confirm('{{ __('¿Eliminar este paquete?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-700 underline">{{ __('Eliminar') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-dim-2">{{ __('No hay paquetes.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
