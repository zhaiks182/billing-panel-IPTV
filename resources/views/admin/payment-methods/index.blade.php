<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Métodos de pago') }}</h2>
            <a href="{{ route('admin.metodos-pago.create') }}" class="bg-brand-600 text-white px-4 py-2 rounded-md text-sm hover:bg-brand-700">
                {{ __('Nuevo método') }}
            </a>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Nombre') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Instrucciones') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($paymentMethods as $method)
                            <tr>
                                <td class="px-6 py-4 text-sm text-paper">{{ $method->name }}</td>
                                <td class="px-6 py-4 text-sm text-dim-2 max-w-md truncate">{{ $method->instructions }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $method->is_active ? 'bg-brand-500/10 text-brand-400' : 'bg-steel text-dim' }}">
                                        {{ $method->is_active ? __('Activo') : __('Inactivo') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-right space-x-2">
                                    <a href="{{ route('admin.metodos-pago.edit', $method) }}" class="text-paper underline">{{ __('Editar') }}</a>
                                    <form method="POST" action="{{ route('admin.metodos-pago.destroy', $method) }}" class="inline"
                                          onsubmit="return confirm('{{ __('¿Eliminar este método?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-700 underline">{{ __('Eliminar') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-dim-2">{{ __('No hay métodos de pago.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
