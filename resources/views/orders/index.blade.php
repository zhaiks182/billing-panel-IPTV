<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Mis Pedidos') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ search: '' }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
                {{-- Sidebar: filtro por estado con contador, estilo "Mis Facturas" de WHMCS --}}
                <div class="bg-panel border border-steel rounded-lg p-4 space-y-1">
                    <p class="px-2 pb-2 text-xs font-semibold text-dim-2 uppercase tracking-wide">{{ __('Estado') }}</p>

                    @php
                        $statusLabels = [
                            'pending' => 'Pendiente',
                            'approved' => 'Aprobado',
                            'activated' => 'Activado',
                            'rejected' => 'Cancelado',
                            'error' => 'Error',
                        ];
                        $totalOrders = $statusCounts->sum();
                    @endphp

                    <a href="{{ route('orders.index') }}"
                       class="flex items-center justify-between px-2 py-1.5 rounded-md text-sm {{ ! request('status') ? 'bg-brand-500/10 text-brand-400 font-medium' : 'text-dim hover:text-paper' }}">
                        <span>{{ __('Todos') }}</span>
                        <span class="text-xs text-dim-2">{{ $totalOrders }}</span>
                    </a>

                    @foreach ($statusLabels as $value => $label)
                        @if ($statusCounts->has($value))
                            <a href="{{ route('orders.index', ['status' => $value]) }}"
                               class="flex items-center justify-between px-2 py-1.5 rounded-md text-sm {{ request('status') === $value ? 'bg-brand-500/10 text-brand-400 font-medium' : 'text-dim hover:text-paper' }}">
                                <span>{{ __($label) }}</span>
                                <span class="text-xs text-dim-2">{{ $statusCounts->get($value) }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>

                {{-- Tabla de pedidos --}}
                <div class="lg:col-span-3 space-y-4">
                    <div class="flex justify-end">
                        <input type="text" x-model="search" placeholder="{{ __('Buscar por paquete...') }}"
                               class="w-full sm:w-64 rounded-md border-steel bg-panel text-paper text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>

                    <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                        <table class="min-w-full divide-y divide-steel">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Fecha') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Paquete') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Total') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-steel">
                                @forelse ($orders as $order)
                                    <tr x-show="search === '' || {{ Illuminate\Support\Js::from(str($order->package->name)->lower()->value()) }}.includes(search.toLowerCase())">
                                        <td class="px-6 py-4 text-sm text-dim">{{ $order->order_number }}</td>
                                        <td class="px-6 py-4 text-sm text-dim-2">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 text-sm text-dim">{{ $order->package->name }}</td>
                                        <td class="px-6 py-4 text-sm text-dim">${{ number_format($order->amount, 2) }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <x-order-status-badge :status="$order->status" />
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="{{ route('orders.invoice', $order) }}" class="text-brand-400 hover:underline">
                                                {{ __('Descargar factura') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-dim-2">
                                            {{ __('Aún no tienes pedidos.') }}
                                            <a href="{{ route('home') }}" class="text-paper underline">{{ __('Ver paquetes') }}</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
