<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-4 flex items-center justify-between gap-2">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
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

                <a href="{{ route('admin.users.create') }}" class="shrink-0 px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                    {{ __('Nuevo usuario') }}
                </a>
            </div>

            @php
                $orderStatusLabels = ['pending' => 'Pendiente', 'approved' => 'Aprobado', 'rejected' => 'Rechazado', 'error' => 'Error'];
                $lineStatusLabels = ['active' => 'Activa', 'expired' => 'Vencida', 'suspended' => 'Suspendida'];
            @endphp
            <div x-data="{
                    openUserId: null,
                    ordersUserId: null,
                    linesUserId: null,
                    usersData: @js($users->getCollection()->map(fn ($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'company' => $u->company,
                        'phone' => trim(($u->phone_country_code ?? '').' '.($u->phone ?? '')),
                        'address_line_1' => $u->address_line_1,
                        'address_line_2' => $u->address_line_2,
                        'city' => $u->city,
                        'state' => $u->state,
                        'postal_code' => $u->postal_code,
                        'country' => $u->country,
                        'orders' => $u->orders->map(fn ($order) => [
                            'name' => $order->package->name ?? 'Paquete eliminado',
                            'amount' => number_format($order->amount, 2),
                            'status' => $orderStatusLabels[$order->status] ?? $order->status,
                            'date' => $order->created_at->format('d/m/Y H:i'),
                        ])->values(),
                        'lines' => $u->lines->map(fn ($line) => [
                            'xui_username' => $line->xui_username,
                            'status' => $lineStatusLabels[$line->status] ?? $line->status,
                            'max_connections' => $line->max_connections,
                            'expires_at' => $line->expires_at?->format('d/m/Y H:i'),
                            'm3u_url' => $line->m3u_url,
                        ])->values(),
                    ])->values()),
                    get selected() {
                        return this.usersData.find(u => u.id === this.openUserId) ?? null;
                    },
                    get selectedOrders() {
                        return this.usersData.find(u => u.id === this.ordersUserId) ?? null;
                    },
                    get selectedLines() {
                        return this.usersData.find(u => u.id === this.linesUserId) ?? null;
                    },
                 }">
                <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-steel">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Nombre') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Correo') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Teléfono') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Rol') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Verificado') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Pedidos') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Líneas') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Registrado') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-steel">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-4 py-4 text-sm">
                                        <button type="button" @click="openUserId = {{ $user->id }}" class="text-paper hover:text-brand-400 hover:underline text-left">
                                            {{ $user->name }}
                                        </button>
                                    </td>
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
                                    <td class="px-4 py-4 text-sm">
                                        @if ($user->isAdmin())
                                            <span class="text-dim-2">—</span>
                                        @elseif ($user->is_blocked)
                                            <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-red-500/10 text-red-400">{{ __('Bloqueado') }}</span>
                                        @else
                                            <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-brand-500/10 text-brand-300">{{ __('Activo') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-dim">
                                        @if ($user->orders_count > 0)
                                            <button type="button" @click="ordersUserId = {{ $user->id }}" class="hover:text-brand-400 hover:underline">{{ $user->orders_count }}</button>
                                        @else
                                            {{ $user->orders_count }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-dim">
                                        @if ($user->lines_count > 0)
                                            <button type="button" @click="linesUserId = {{ $user->id }}" class="hover:text-brand-400 hover:underline">{{ $user->lines_count }}</button>
                                        @else
                                            {{ $user->lines_count }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-dim-2">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-4 text-sm space-y-1">
                                        @unless ($user->hasVerifiedEmail())
                                            <form method="POST" action="{{ route('admin.users.verify', $user) }}"
                                                  onsubmit="return confirm('{{ __('¿Verificar manualmente el correo de este usuario? Si tiene una prueba gratis pendiente, se activará.') }}')">
                                                @csrf
                                                <button class="text-brand-400 hover:underline">{{ __('Verificar correo') }}</button>
                                            </form>
                                        @endif
                                        @unless ($user->isAdmin())
                                            <form method="POST" action="{{ route('admin.users.toggle-block', $user) }}"
                                                  onsubmit="return confirm('{{ $user->is_blocked ? '¿Desbloquear' : '¿Bloquear' }} a {{ $user->email }}?')">
                                                @csrf
                                                <button class="{{ $user->is_blocked ? 'text-brand-400' : 'text-amber' }} hover:underline">
                                                    {{ $user->is_blocked ? __('Desbloquear') : __('Bloquear') }}
                                                </button>
                                            </form>
                                        @endunless
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
                                    <td colspan="10" class="px-4 py-8 text-center text-dim-2">{{ __('No se encontraron usuarios.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div x-show="selected" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
                     @click.self="openUserId = null" @keydown.escape.window="openUserId = null">
                    <div class="bg-panel border border-steel rounded-lg p-6 max-w-md w-full">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-paper" x-text="selected?.name"></h3>
                                <p class="text-sm text-dim" x-text="selected?.email"></p>
                            </div>
                            <button type="button" @click="openUserId = null" class="text-dim hover:text-paper">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <dl class="space-y-3 text-sm">
                            <div x-show="selected?.phone">
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Teléfono') }}</dt>
                                <dd class="text-paper" x-text="selected?.phone"></dd>
                            </div>
                            <div x-show="selected?.company">
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Empresa') }}</dt>
                                <dd class="text-paper" x-text="selected?.company"></dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Dirección') }}</dt>
                                <dd class="text-paper" x-text="selected?.address_line_1 || '{{ __('—') }}'"></dd>
                                <dd class="text-paper" x-show="selected?.address_line_2" x-text="selected?.address_line_2"></dd>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Ciudad') }}</dt>
                                    <dd class="text-paper" x-text="selected?.city || '{{ __('—') }}'"></dd>
                                </div>
                                <div>
                                    <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Estado / Provincia') }}</dt>
                                    <dd class="text-paper" x-text="selected?.state || '{{ __('—') }}'"></dd>
                                </div>
                                <div>
                                    <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Código Postal') }}</dt>
                                    <dd class="text-paper" x-text="selected?.postal_code || '{{ __('—') }}'"></dd>
                                </div>
                                <div>
                                    <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('País') }}</dt>
                                    <dd class="text-paper" x-text="selected?.country || '{{ __('—') }}'"></dd>
                                </div>
                            </div>
                        </dl>
                    </div>
                </div>

                <div x-show="selectedOrders" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
                     @click.self="ordersUserId = null" @keydown.escape.window="ordersUserId = null">
                    <div class="bg-panel border border-steel rounded-lg p-6 max-w-md w-full">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-paper">{{ __('Pedidos de') }} <span x-text="selectedOrders?.name"></span></h3>
                            </div>
                            <button type="button" @click="ordersUserId = null" class="text-dim hover:text-paper">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <ul class="space-y-3 text-sm max-h-96 overflow-y-auto scrollbar-dark">
                            <template x-for="order in (selectedOrders?.orders ?? [])" :key="order.date + order.name">
                                <li class="flex items-center justify-between gap-3 border-b border-steel pb-3 last:border-0 last:pb-0">
                                    <div>
                                        <p class="text-paper" x-text="order.name"></p>
                                        <p class="text-dim-2 text-xs" x-text="order.date"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-paper" x-text="'$' + order.amount"></p>
                                        <p class="text-dim-2 text-xs" x-text="order.status"></p>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div x-show="selectedLines" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
                     @click.self="linesUserId = null" @keydown.escape.window="linesUserId = null">
                    <div class="bg-panel border border-steel rounded-lg p-6 max-w-md w-full">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-paper">{{ __('Líneas de') }} <span x-text="selectedLines?.name"></span></h3>
                            </div>
                            <button type="button" @click="linesUserId = null" class="text-dim hover:text-paper">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <ul class="space-y-3 text-sm max-h-96 overflow-y-auto scrollbar-dark">
                            <template x-for="line in (selectedLines?.lines ?? [])" :key="line.xui_username">
                                <li class="border-b border-steel pb-3 last:border-0 last:pb-0">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-paper font-mono" x-text="line.xui_username"></p>
                                        <p class="text-dim-2 text-xs" x-text="line.status"></p>
                                    </div>
                                    <p class="text-dim text-xs mt-1">
                                        {{ __('Conexiones') }}: <span x-text="line.max_connections"></span>
                                        &middot; {{ __('Vence') }}: <span x-text="line.expires_at ?? '{{ __('—') }}'"></span>
                                    </p>
                                    <p class="text-dim text-xs mt-1 break-all" x-show="line.m3u_url" x-text="line.m3u_url"></p>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
