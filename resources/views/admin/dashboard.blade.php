<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Panel de administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
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
                    <p class="text-sm text-dim-2">{{ __('Ingresos este mes') }}</p>
                    <p class="text-3xl font-bold text-paper">${{ number_format($monthlyRevenue, 2) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('admin.users.index') }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Clientes nuevos este mes') }}</p>
                    <p class="text-3xl font-bold text-paper">{{ $newClientsThisMonth }}</p>
                </a>
                <div class="bg-panel border border-steel rounded-lg p-6">
                    <p class="text-sm text-dim-2">{{ __('Líneas activas') }}</p>
                    <p class="text-3xl font-bold text-brand-400">{{ $activeLinesCount }}</p>
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'approved']) }}" class="bg-panel border border-steel rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-dim-2">{{ __('Pedidos aprobados este mes') }}</p>
                    <p class="text-3xl font-bold text-paper">{{ $approvedOrdersThisMonth }}</p>
                </a>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-paper mb-3">{{ __('Líneas por vencer (próximos 3 días)') }}</h3>
                <div class="bg-panel border border-steel rounded-lg overflow-hidden">
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
</x-app-layout>
