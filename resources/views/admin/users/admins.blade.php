<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Administradores') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-4 flex items-center justify-between gap-2">
                <form method="GET" action="{{ route('admin.users.admins') }}" class="flex gap-2">
                    <x-text-input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Buscar por nombre o usuario...') }}" class="w-full max-w-sm" />
                    <button type="submit" class="px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                        {{ __('Buscar') }}
                    </button>
                    @if ($search !== '')
                        <a href="{{ route('admin.users.admins') }}" class="px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                            {{ __('Limpiar') }}
                        </a>
                    @endif
                </form>

                <a href="{{ route('admin.users.create', ['role' => 'admin']) }}" class="shrink-0 px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                    {{ __('Nuevo administrador') }}
                </a>
            </div>

            <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-steel">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Nombre') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Usuario') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Nivel de acceso') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Registrado') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($admins as $admin)
                            <tr>
                                <td class="px-4 py-4 text-sm text-paper">{{ $admin->name }}</td>
                                <td class="px-4 py-4 text-sm text-dim font-mono">{{ $admin->username }}</td>
                                <td class="px-4 py-4 text-sm">
                                    <form method="POST" action="{{ route('admin.users.role.update', $admin) }}"
                                          onsubmit="return confirm('¿Cambiar el nivel de acceso de {{ $admin->username }}?')">
                                        @csrf
                                        <select name="admin_role" onchange="this.form.submit()"
                                                class="rounded-md border-steel bg-ink text-paper text-sm shadow-sm">
                                            <option value="support" {{ $admin->admin_role === 'support' ? 'selected' : '' }}>{{ __('Soporte') }}</option>
                                            <option value="super_admin" {{ $admin->admin_role === 'super_admin' ? 'selected' : '' }}>{{ __('Super Admin') }}</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-4 text-sm text-dim-2">{{ $admin->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-4 text-sm">
                                    @if ($admin->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $admin) }}"
                                              onsubmit="return confirm('¿Eliminar permanentemente al administrador {{ $admin->username }}? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-500 hover:underline">{{ __('Eliminar') }}</button>
                                        </form>
                                    @else
                                        <span class="text-dim-2 text-xs">{{ __('Eres tú') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-dim-2">{{ __('No se encontraron administradores.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $admins->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
