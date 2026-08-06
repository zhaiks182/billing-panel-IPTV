<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Mis Pedidos') }}
        </h2>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Paquete') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Monto') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Fecha') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-6 py-4 text-sm text-dim">{{ $order->id }}</td>
                                <td class="px-6 py-4 text-sm text-dim">{{ $order->package->name }}</td>
                                <td class="px-6 py-4 text-sm text-dim">${{ number_format($order->amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <x-order-status-badge :status="$order->status" />
                                </td>
                                <td class="px-6 py-4 text-sm text-dim-2">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-dim-2">
                                    {{ __('Aún no tienes pedidos.') }}
                                    <a href="{{ route('home') }}" class="text-paper underline">{{ __('Ver paquetes') }}</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
