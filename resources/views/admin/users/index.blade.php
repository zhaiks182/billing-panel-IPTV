<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4 flex gap-2">
                <x-text-input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Buscar por nombre, correo o teléfono...') }}" class="w-full max-w-sm" />
                <button type="submit" class="px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                    {{ __('Buscar') }}
                </button>
                @if ($search !== '')
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                        {{ __('Limpiar') }}
                    </a>
                @endif
            </form>

            <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-steel">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Nombre') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Correo') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Teléfono') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Rol') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Verificado') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Pedidos') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Líneas') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Registrado') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-4 py-4 text-sm text-paper">{{ $user->name }}</td>
                                <td class="px-4 py-4 text-sm text-dim">{{ $user->email }}</td>
                                <td class="px-4 py-4 text-sm text-dim">
                                    {{ $user->phone_country_code }} {{ $user->phone }}
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    @if ($user->isAdmin())
                                        <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-brand-500/10 text-brand-300">{{ __('Admin') }}</span>
                                    @else
                                        <span class="text-dim-2">{{ __('Cliente') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    @if ($user->hasVerifiedEmail())
                                        <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-brand-500/10 text-brand-300">{{ __('Verificado') }}</span>
                                    @else
                                        <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-amber/10 text-amber">{{ __('Sin verificar') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-dim">{{ $user->orders_count }}</td>
                                <td class="px-4 py-4 text-sm text-dim">{{ $user->lines_count }}</td>
                                <td class="px-4 py-4 text-sm text-dim-2">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-4 text-sm space-y-1">
                                    @unless ($user->hasVerifiedEmail())
                                        <form method="POST" action="{{ route('admin.users.verify', $user) }}"
                                              onsubmit="return confirm('{{ __('¿Verificar manualmente el correo de este usuario? Si tiene una prueba gratis pendiente, se activará.') }}')">
                                            @csrf
                                            <button class="text-brand-400 hover:underline">{{ __('Verificar correo') }}</button>
                                        </form>
                                    @endif
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('¿Eliminar permanentemente a {{ $user->email }}? Se borrarán también sus {{ $user->orders_count }} pedido(s) y {{ $user->lines_count }} línea(s). Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-500 hover:underline">{{ __('Eliminar') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-dim-2">{{ __('No se encontraron usuarios.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
