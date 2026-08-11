<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Panel de administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            @if ($errorCount > 0 || $linesExpiringTodayCount > 0)
                <div class="bg-amber/10 border border-amber rounded-lg p-4">
                    <p class="flex items-center gap-2 font-semibold text-amber">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Atención requerida') }}
                    </p>
                    <ul class="mt-2 space-y-1 text-sm text-paper list-disc list-inside">
                        @if ($errorCount > 0)
                            <li>
                                <a href="{{ route('admin.orders.index', ['status' => 'error']) }}" class="hover:underline">
                                    {{ __(':count pedido(s) con error de activación en XUI', ['count' => $errorCount]) }}
                                </a>
                            </li>
                        @endif
                        @if ($linesExpiringTodayCount > 0)
                            <li>
                                <a href="{{ route('admin.lines.index') }}" class="hover:underline">
                                    {{ __(':count línea(s) vencen hoy', ['count' => $linesExpiringTodayCount]) }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            @else
                <div class="flex items-center gap-2 bg-brand-500/10 border border-brand-800 text-brand-300 rounded-lg p-4 text-sm">
                    <span>✓</span>
                    {{ __('No hay incidencias pendientes.') }}
                </div>
            @endif

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

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('admin.orders.index', ['status' => ['approved', 'activated']]) }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
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
                <h3 class="text-lg font-semibold text-paper mb-3">{{ __('Ingresos por día en el período') }}</h3>
                <div class="bg-panel border border-steel rounded-lg p-6">
                    @if ($revenueByDay->isEmpty())
                        <p class="text-dim-2 text-sm text-center py-6">{{ __('Sin ingresos registrados en este período.') }}</p>
                    @else
                        @php $maxRevenue = $revenueByDay->max('total') ?: 1; @endphp
                        <div class="flex items-end gap-1 h-40">
                            @foreach ($revenueByDay as $day)
                                <div class="flex-1 flex flex-col justify-end items-center group relative h-full">
                                    <div class="absolute -top-7 hidden group-hover:block text-xs bg-ink border border-steel rounded px-2 py-1 whitespace-nowrap text-paper z-10">
                                        {{ \Carbon\Carbon::parse($day->day)->format('d/m') }} — ${{ number_format($day->total, 2) }}
                                    </div>
                                    <div class="w-full bg-brand-500/70 hover:bg-brand-500 rounded-t transition"
                                         style="height: {{ max(2, ($day->total / $maxRevenue) * 100) }}%"></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-dim-2">
                            <span>{{ \Carbon\Carbon::parse($revenueByDay->first()->day)->format('d/m') }}</span>
                            <span>{{ \Carbon\Carbon::parse($revenueByDay->last()->day)->format('d/m') }}</span>
                        </div>
                    @endif
                </div>
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
                                    <td class="px-6 py-4 text-sm text-dim">
                                        {{ $line->user->name }} ({{ $line->user->email }})
                                        @if ($line->order?->package?->is_trial)
                                            <span class="ml-1 inline-flex px-1.5 py-0.5 text-xs rounded bg-amber/10 text-amber">{{ __('Demo') }}</span>
                                        @endif
                                    </td>
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
