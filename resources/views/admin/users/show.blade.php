<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ $user->name }}</h2>
            @if ($user->hasVerifiedEmail())
                <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-brand-500/10 text-brand-300">{{ __('Verificado') }}</span>
            @else
                <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-amber/10 text-amber">{{ __('Sin verificar') }}</span>
            @endif
            @if ($user->is_blocked)
                <span class="inline-flex px-1.5 py-0.5 text-xs rounded bg-red-500/10 text-red-400">{{ __('Bloqueado') }}</span>
            @endif
            <x-close-link :href="route('admin.users.index')" />
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-dim-2 uppercase tracking-wide mb-4">{{ __('Datos del cliente') }}</h3>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Nombre') }}</dt>
                                <dd class="text-paper">{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Correo') }}</dt>
                                <dd class="text-paper">{{ $user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Teléfono') }}</dt>
                                <dd class="text-paper">{{ trim(($user->phone_country_code ?? '').' '.($user->phone ?? '')) ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Registrado') }}</dt>
                                <dd class="text-paper">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>

                        @if ($user->company || $user->address_line_1)
                            <div class="mt-4 pt-4 border-t border-steel">
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Dirección de facturación') }}</dt>
                                <dd class="text-paper text-sm mt-1">
                                    @if ($user->company) {{ $user->company }}<br> @endif
                                    {{ $user->address_line_1 ?: '—' }}
                                    @if ($user->address_line_2) <br>{{ $user->address_line_2 }} @endif
                                    <br>
                                    {{ collect([$user->city, $user->state, $user->postal_code, $user->country])->filter()->implode(', ') ?: '—' }}
                                </dd>
                            </div>
                        @endif
                    </div>

                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-dim-2 uppercase tracking-wide mb-4">{{ __('Sus líneas') }} ({{ $lines->count() }})</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-steel">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Usuario XUI') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Plan') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Vence') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Conex.') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Acción') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-steel">
                                    @forelse ($lines as $line)
                                        <tr>
                                            <td class="px-3 py-3 text-sm text-dim font-mono">{{ $line->xui_username }}</td>
                                            <td class="px-3 py-3 text-sm text-dim">{{ $line->order?->package?->name ?? '—' }}</td>
                                            <td class="px-3 py-3 text-sm"><x-line-status-badge :line="$line" /></td>
                                            <td class="px-3 py-3 text-sm text-dim">{{ $line->expires_at?->format('d/m/y') ?? '—' }}</td>
                                            <td class="px-3 py-3 text-sm text-dim">{{ $line->max_connections }}</td>
                                            <td class="px-3 py-3 text-sm">
                                                <a href="{{ route('admin.lines.show', $line) }}" class="text-brand-400 hover:underline">{{ __('Ver') }}</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-6 text-center text-dim-2">{{ __('Este cliente no tiene líneas.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-dim-2 uppercase tracking-wide mb-4">{{ __('Sus pedidos') }} ({{ $orders->count() }})</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-steel">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">#</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Paquete') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Monto') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Fecha') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-steel">
                                    @forelse ($orders as $order)
                                        <tr>
                                            <td class="px-3 py-3 text-sm text-dim">{{ $order->id }}</td>
                                            <td class="px-3 py-3 text-sm text-dim">{{ $order->package->name }}</td>
                                            <td class="px-3 py-3 text-sm text-dim">${{ number_format($order->amount, 2) }}</td>
                                            <td class="px-3 py-3 text-sm"><x-order-status-badge :status="$order->status" /></td>
                                            <td class="px-3 py-3 text-sm text-dim-2">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-6 text-center text-dim-2">{{ __('Este cliente no tiene pedidos.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-panel border border-steel rounded-lg p-6 space-y-3">
                    <h3 class="text-sm font-semibold text-dim-2 uppercase tracking-wide mb-1">{{ __('Acciones') }}</h3>

                    @unless ($user->hasVerifiedEmail())
                        <form method="POST" action="{{ route('admin.users.verify', $user) }}"
                              onsubmit="return confirm('{{ __('¿Verificar manualmente el correo de este usuario? Si tiene una prueba gratis pendiente, se activará.') }}')">
                            @csrf
                            <button class="w-full px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                                {{ __('Verificar correo') }}
                            </button>
                        </form>
                    @endunless

                    <form method="POST" action="{{ route('admin.users.toggle-block', $user) }}"
                          onsubmit="return confirm('{{ $user->is_blocked ? '¿Desbloquear' : '¿Bloquear' }} a {{ $user->email }}?')">
                        @csrf
                        <button class="w-full px-4 py-2 rounded-md {{ $user->is_blocked ? 'bg-brand-500 text-ink hover:brightness-110' : 'bg-amber/10 text-amber hover:bg-amber/20' }} text-sm font-semibold">
                            {{ $user->is_blocked ? __('Desbloquear') : __('Bloquear') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          onsubmit="return confirm('¿Eliminar permanentemente a {{ $user->email }}? Se borrarán también sus {{ $orders->count() }} pedido(s) y {{ $lines->count() }} línea(s). Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button class="w-full px-4 py-2 rounded-md bg-danger/10 text-danger text-sm font-semibold hover:bg-danger/20">
                            {{ __('Eliminar') }}
                        </button>
                    </form>

                    <a href="{{ route('admin.users.index') }}" class="block text-center text-sm text-dim hover:text-paper pt-1">
                        {{ __('Volver al listado') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
