<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Pedidos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-4 flex gap-2 text-sm">
                @foreach (['' => 'Todos', 'pending' => 'Pendientes', 'approved' => 'Aprobados', 'activated' => 'Activados', 'rejected' => 'Cancelados', 'error' => 'Con error'] as $value => $label)
                    <a href="{{ route('admin.orders.index', array_filter(['status' => $value ?: null, 'date_from' => request('date_from'), 'date_to' => request('date_to')])) }}"
                       class="px-3 py-1.5 rounded-md border {{ request('status', '') === $value ? 'bg-brand-600 text-white border-brand-600' : 'bg-panel text-dim border-steel' }}">
                        {{ __($label) }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
                @if (is_array(request('status')))
                    @foreach (request('status') as $statusValue)
                        <input type="hidden" name="status[]" value="{{ $statusValue }}">
                    @endforeach
                @elseif (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div>
                    <x-input-label for="date_from" value="{{ __('Desde') }}" />
                    <input id="date_from" name="date_from" type="date" value="{{ request('date_from') }}"
                           class="mt-1 bg-panel border-steel text-paper rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                </div>
                <div>
                    <x-input-label for="date_to" value="{{ __('Hasta') }}" />
                    <input id="date_to" name="date_to" type="date" value="{{ request('date_to') }}"
                           class="mt-1 bg-panel border-steel text-paper rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                </div>
                <button type="submit" class="px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                    {{ __('Filtrar') }}
                </button>
                @if (request('date_from') || request('date_to'))
                    <a href="{{ route('admin.orders.index', array_filter(['status' => request('status')])) }}"
                       class="px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                        {{ __('Quitar fechas') }}
                    </a>
                @endif
                <a href="{{ route('admin.orders.export', request()->only(['status', 'date_from', 'date_to'])) }}"
                   class="px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                    {{ __('Exportar a CSV') }}
                </a>
            </form>

            <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-steel">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Cliente') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Paquete') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Monto') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Método') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Comprobante') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-4 py-4 text-sm text-dim">{{ $order->order_number }}</td>
                                <td class="px-4 py-4 text-sm text-dim">
                                    <a href="{{ route('admin.users.show', $order->user) }}" class="text-paper hover:text-brand-400 hover:underline">{{ $order->user->name }}</a><br>
                                    <span class="text-xs text-dim-2">{{ $order->user->email }}</span>
                                    @if ($order->is_renewal)
                                        <span class="ml-1 inline-flex px-1.5 py-0.5 text-xs rounded bg-brand-500/10 text-brand-300">{{ __('Cliente con línea activa') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-dim">
                                    {{ $order->package->name }}
                                    @if ($order->package->is_trial)
                                        <span class="ml-1 inline-flex px-1.5 py-0.5 text-xs rounded bg-amber/10 text-amber">{{ __('Demo') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-dim">${{ number_format($order->amount, 2) }}</td>
                                <td class="px-4 py-4 text-sm text-dim">{{ $order->paymentMethod->name ?? __('Prueba Gratis') }}</td>
                                <td class="px-4 py-4 text-sm">
                                    @if ($order->proof_path)
                                        <a href="{{ asset('storage/'.$order->proof_path) }}" target="_blank" class="text-paper underline">{{ __('Ver') }}</a>
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <x-order-status-badge :status="$order->status" />
                                    @if ($order->status === 'error' && $order->admin_note)
                                        <p class="text-xs text-red-600 mt-1 max-w-xs">{{ $order->admin_note }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm space-y-1">
                                    @if ($order->status === 'pending')
                                        <form method="POST" action="{{ route('admin.orders.approve', $order) }}">
                                            @csrf
                                            <button class="text-green-700 hover:underline">{{ __('Aprobar') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.orders.reject', $order) }}"
                                              onsubmit="return confirm('{{ __('¿Rechazar este pedido?') }}')">
                                            @csrf
                                            <button class="text-red-700 hover:underline">{{ __('Rechazar') }}</button>
                                        </form>
                                    @elseif (in_array($order->status, ['approved', 'error']))
                                        <form method="POST" action="{{ route('admin.orders.retry', $order) }}">
                                            @csrf
                                            <button class="text-amber-700 hover:underline">
                                                {{ $order->status === 'error' ? __('Reintentar activación') : __('Activar línea') }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.orders.reject', $order) }}"
                                              onsubmit="return confirm('{{ __('¿Rechazar este pedido?') }}')">
                                            @csrf
                                            <button class="text-red-700 hover:underline">{{ __('Rechazar') }}</button>
                                        </form>
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-dim-2">{{ __('No hay pedidos.') }}</td>
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
</x-admin-layout>
