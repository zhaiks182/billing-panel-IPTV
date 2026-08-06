<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Mis Enlaces M3U') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if (session('status'))
                <div class="bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if (request('verified') == 1)
                <div class="bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ __('¡Correo verificado correctamente! Ya puedes comprar paquetes.') }}
                </div>
            @endif

            <div>
                <h3 class="text-lg font-semibold text-paper mb-3">{{ __('Mis líneas') }}</h3>

                @if ($lines->isEmpty())
                    <div class="bg-panel border border-steel rounded-lg p-6 text-dim-2">
                        {{ __('Todavía no tienes ninguna línea activa.') }}
                        <a href="{{ route('home') }}" class="text-paper underline">{{ __('Ver paquetes') }}</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($lines as $line)
                            @php $package = $line->order?->package; @endphp
                            <div class="bg-panel border border-steel rounded-lg p-6">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="font-semibold text-paper">{{ $package->name ?? __('Línea IPTV') }}</h4>
                                    @if ($package?->is_trial)
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-amber/10 text-amber">{{ __('Demo') }}</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-steel text-dim">{{ __('De pago') }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $line->status === 'active' && $line->expires_at->isFuture() ? 'bg-brand-500/10 text-brand-400' : 'bg-danger/10 text-danger' }}">
                                        {{ $line->status === 'active' && $line->expires_at->isFuture() ? __('Activa') : __('Vencida') }}
                                    </span>
                                    <span class="text-sm text-dim-2">
                                        {{ __('Vence') }}: {{ $line->expires_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                <dl class="text-sm space-y-1">
                                    @if ($serverUrl)
                                        <div class="flex justify-between items-center gap-2">
                                            <dt class="text-dim-2">{{ __('Servidor') }}</dt>
                                            <dd class="font-mono text-paper break-all text-right flex items-center gap-1.5">
                                                <span>{{ $serverUrl }}</span>
                                                <x-copy-button :text="$serverUrl" />
                                            </dd>
                                        </div>
                                    @endif
                                    <div class="flex justify-between items-center gap-2">
                                        <dt class="text-dim-2">{{ __('Usuario') }}</dt>
                                        <dd class="font-mono text-paper flex items-center gap-1.5">
                                            <span>{{ $line->xui_username }}</span>
                                            <x-copy-button :text="$line->xui_username" />
                                        </dd>
                                    </div>
                                    <div class="flex justify-between items-center gap-2">
                                        <dt class="text-dim-2">{{ __('Contraseña') }}</dt>
                                        <dd class="font-mono text-paper flex items-center gap-1.5">
                                            <span>{{ $line->xui_password }}</span>
                                            <x-copy-button :text="$line->xui_password" />
                                        </dd>
                                    </div>
                                    <div class="flex justify-between items-center gap-2">
                                        <dt class="text-dim-2">{{ __('Conexiones') }}</dt>
                                        <dd class="text-paper flex items-center gap-1.5">
                                            <span>{{ $line->max_connections }}</span>
                                            <span class="inline-block h-3.5 w-3.5 shrink-0" aria-hidden="true"></span>
                                        </dd>
                                    </div>
                                    @if ($line->m3u_url)
                                        <div class="pt-2">
                                            <dt class="text-dim-2 mb-1">{{ __('URL M3U') }}</dt>
                                            <dd class="text-xs break-all flex items-center gap-1.5">
                                                <a href="{{ $line->m3u_url }}" target="_blank" rel="noopener" class="font-mono text-brand-700 hover:underline">
                                                    {{ $line->m3u_url }}
                                                </a>
                                                <x-copy-button :text="$line->m3u_url" />
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                                <a href="{{ route('home') }}" class="mt-4 inline-block text-sm text-paper underline">
                                    {{ __('Comprar otra línea') }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-lg font-semibold text-paper mb-3">{{ __('Pedidos recientes') }}</h3>

                <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-steel">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Paquete') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Fecha') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-steel">
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-dim">
                                        {{ $order->package->name }}
                                        @if ($order->package->is_trial)
                                            <span class="ml-1 inline-flex px-1.5 py-0.5 text-xs rounded bg-amber/10 text-amber">{{ __('Demo') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm"><x-order-status-badge :status="$order->status" /></td>
                                    <td class="px-6 py-4 text-sm text-dim-2">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-dim-2">{{ __('Sin pedidos todavía.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
