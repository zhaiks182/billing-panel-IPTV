<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Panel de administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <x-input-label for="date_from" value="{{ __('Desde') }}" />
                        <input id="date_from" name="date_from" type="date" value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}"
                               class="mt-1 bg-panel border-steel text-paper rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                    </div>
                    <div>
                        <x-input-label for="date_to" value="{{ __('Hasta') }}" />
                        <input id="date_to" name="date_to" type="date" value="{{ request('date_to', $dateTo->format('Y-m-d')) }}"
                               class="mt-1 bg-panel border-steel text-paper rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                        {{ __('Filtrar') }}
                    </button>
                    @if (request('date_from') || request('date_to'))
                        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                            {{ __('Quitar fechas (mes actual)') }}
                        </a>
                    @endif
                    <p class="text-xs text-dim-2 basis-full">
                        {{ __('Los ingresos y clientes nuevos de abajo corresponden al período :from – :to.', ['from' => $dateFrom->format('d/m/Y'), 'to' => $dateTo->format('d/m/Y')]) }}
                    </p>
                </form>

                <div class="bg-panel border border-steel rounded-lg px-5 py-3 text-right shrink-0" title="{{ __('XUI ONE no expone conexiones en vivo a la clave de reseller — es la suma de conexiones permitidas en líneas activas, no uso en tiempo real.') }}">
                    <p class="text-xs text-dim-2">{{ __('Capacidad de conexiones') }}</p>
                    <p class="text-2xl font-bold text-paper">{{ $totalConnectionsCapacity }}</p>
                    <p class="text-[11px] text-dim-2 mt-0.5">{{ __('conexiones permitidas · líneas activas') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('admin.orders.index', ['status' => 'approved']) }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Ingresos en el período') }}</p>
                    <p class="text-3xl font-bold text-paper">${{ number_format($periodRevenue, 2) }}</p>
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Pedidos pendientes') }}</p>
                    <p class="text-3xl font-bold text-paper">{{ $pendingCount }}</p>
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'error']) }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Pedidos con error') }}</p>
                    <p class="text-3xl font-bold text-red-600">{{ $errorCount }}</p>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('admin.users.index') }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Clientes nuevos en el período') }}</p>
                    <p class="text-3xl font-bold text-paper">{{ $newClientsInPeriod }}</p>
                </a>
                <a href="{{ route('admin.lines.index', ['status' => 'active']) }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Líneas activas') }}</p>
                    <p class="text-3xl font-bold text-brand-400">{{ $activeLinesCount }}</p>
                </a>
                <a href="{{ route('admin.lines.index', ['status' => 'expiring_soon']) }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Líneas por vencer') }}</p>
                    <p class="text-3xl font-bold text-amber">{{ $expiringSoonCount }}</p>
                </a>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-paper mb-3">{{ __('Líneas por vencer (próximos :days días)', ['days' => \App\Models\Line::EXPIRING_SOON_DAYS]) }}</h3>
                <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-steel">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Cliente') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Usuario XUI') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Vence') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Restante') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Acción') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-steel">
                            @forelse ($expiringSoon as $line)
                                @php $daysLeft = (int) floor(now()->diffInDays($line->expires_at)); @endphp
                                <tr>
                                    <td class="px-6 py-4 text-sm text-dim">{{ $line->user->name }} ({{ $line->user->email }})</td>
                                    <td class="px-6 py-4 text-sm font-mono text-dim">{{ $line->xui_username }}</td>
                                    <td class="px-6 py-4 text-sm text-dim-2">{{ $line->expires_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm text-amber">{{ $daysLeft < 1 ? __('Hoy') : __(':days días', ['days' => $daysLeft]) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('admin.lines.show', $line) }}" class="text-brand-400 hover:underline">{{ __('Renovar') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-dim-2">{{ __('Ninguna línea vence pronto.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-paper">{{ __('Pedidos recientes') }}</h3>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm text-brand-400 hover:underline">{{ __('Ver todos') }}</a>
                </div>
                <div class="bg-panel border border-steel rounded-lg divide-y divide-steel">
                    @forelse ($recentOrders as $order)
                        <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-3 text-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-dim-2">#{{ $order->id }}</span>
                                <span class="text-paper">{{ $order->user->name }}</span>
                                <span class="text-dim-2">·</span>
                                <span class="text-dim">{{ $order->package->name }}</span>
                                <span class="text-dim-2">·</span>
                                <span class="text-paper font-medium">${{ number_format($order->amount, 2) }}</span>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <x-order-status-badge :status="$order->status" />
                                <span class="text-dim-2 text-xs" title="{{ $order->created_at->diffForHumans() }}">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-dim-2">{{ __('No hay pedidos todavía.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
