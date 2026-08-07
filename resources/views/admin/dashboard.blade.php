<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Panel de administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
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
                    {{ __('Los ingresos, clientes nuevos y pedidos aprobados de abajo corresponden al período :from – :to.', ['from' => $dateFrom->format('d/m/Y'), 'to' => $dateTo->format('d/m/Y')]) }}
                </p>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Pedidos pendientes') }}</p>
                    <p class="text-3xl font-bold text-paper">{{ $pendingCount }}</p>
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'error']) }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Pedidos con error') }}</p>
                    <p class="text-3xl font-bold text-red-600">{{ $errorCount }}</p>
                </a>
                <div class="bg-panel border border-steel rounded-lg p-6">
                    <p class="text-sm text-dim-2">{{ __('Ingresos en el período') }}</p>
                    <p class="text-3xl font-bold text-paper">${{ number_format($periodRevenue, 2) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('admin.users.index') }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Clientes nuevos en el período') }}</p>
                    <p class="text-3xl font-bold text-paper">{{ $newClientsInPeriod }}</p>
                </a>
                <div class="bg-panel border border-steel rounded-lg p-6">
                    <p class="text-sm text-dim-2">{{ __('Líneas activas') }}</p>
                    <p class="text-3xl font-bold text-brand-400">{{ $activeLinesCount }}</p>
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'approved']) }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Pedidos aprobados en el período') }}</p>
                    <p class="text-3xl font-bold text-paper">{{ $approvedOrdersInPeriod }}</p>
                </a>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-paper mb-3">{{ __('Líneas por vencer (próximos 3 días)') }}</h3>
                <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-steel">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Cliente') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Usuario XUI') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Vence') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-steel">
                            @forelse ($expiringSoon as $line)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-dim">{{ $line->user->name }} ({{ $line->user->email }})</td>
                                    <td class="px-6 py-4 text-sm font-mono text-dim">{{ $line->xui_username }}</td>
                                    <td class="px-6 py-4 text-sm text-dim-2">{{ $line->expires_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-dim-2">{{ __('Ninguna línea vence pronto.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
