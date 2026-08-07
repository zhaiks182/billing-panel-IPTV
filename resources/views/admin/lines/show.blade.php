<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">
                {{ __('Línea de') }} {{ $line->user->name }}
            </h2>
            <x-line-status-badge :line="$line" />
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-danger/10 border border-danger text-danger px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-dim-2 uppercase tracking-wide mb-4">{{ __('Credenciales') }}</h3>
                        <dl class="space-y-4 text-sm">
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Usuario XUI') }}</dt>
                                <dd class="flex items-center gap-2 text-paper font-mono">
                                    <span>{{ $line->xui_username }}</span>
                                    <x-copy-button :text="$line->xui_username" />
                                </dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Contraseña') }}</dt>
                                <dd class="flex items-center gap-2 text-paper font-mono">
                                    <span>{{ $line->xui_password }}</span>
                                    <x-copy-button :text="$line->xui_password" />
                                </dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('URL M3U') }}</dt>
                                <dd class="flex items-center gap-2">
                                    @if ($line->m3u_url)
                                        <a href="{{ $line->m3u_url }}" target="_blank" rel="noopener" class="font-mono text-brand-400 hover:underline break-all">{{ $line->m3u_url }}</a>
                                        <x-copy-button :text="$line->m3u_url" />
                                    @else
                                        <span class="text-dim-2">—</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-dim-2 uppercase tracking-wide mb-4">{{ __('Detalles') }}</h3>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Cliente') }}</dt>
                                <dd class="text-paper">{{ $line->user->name }}</dd>
                                <dd class="text-dim text-xs">{{ $line->user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Plan') }}</dt>
                                <dd class="text-paper">{{ $line->order?->package?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Conexiones') }}</dt>
                                <dd class="text-paper">{{ $line->max_connections }}</dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Vence') }}</dt>
                                <dd class="text-paper">{{ $line->expires_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('Pedido relacionado') }}</dt>
                                <dd class="text-paper">
                                    @if ($line->order)
                                        <a href="{{ route('admin.orders.index') }}" class="text-brand-400 hover:underline">#{{ $line->order->id }}</a>
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-dim-2 text-xs uppercase tracking-wide">{{ __('ID en XUI ONE') }}</dt>
                                <dd class="text-paper font-mono">{{ $line->xui_line_id ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-panel border border-steel rounded-lg p-6 space-y-3">
                        <h3 class="text-sm font-semibold text-dim-2 uppercase tracking-wide mb-1">{{ __('Acciones') }}</h3>

                        <form method="POST" action="{{ route('admin.lines.renew', $line) }}"
                              onsubmit="return confirm('{{ __('¿Renovar esta línea por la duración de su plan?') }}')">
                            @csrf
                            <button class="w-full px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                                {{ __('Renovar') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.lines.add-days', $line) }}" class="flex gap-2">
                            @csrf
                            <input type="number" name="days" min="1" max="365" required placeholder="{{ __('Días') }}"
                                   class="w-24 rounded-md border-steel bg-ink text-paper text-sm shadow-sm">
                            <button class="flex-1 px-3 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                                {{ __('Sumar días') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.lines.toggle-suspend', $line) }}"
                              onsubmit="return confirm('{{ $line->status === 'suspended' ? __('¿Reactivar esta línea?') : __('¿Suspender esta línea? El cliente perderá acceso al servicio.') }}')">
                            @csrf
                            <button class="w-full px-4 py-2 rounded-md {{ $line->status === 'suspended' ? 'bg-brand-500 text-ink hover:brightness-110' : 'bg-amber/10 text-amber hover:bg-amber/20' }} text-sm font-semibold">
                                {{ $line->status === 'suspended' ? __('Reactivar') : __('Suspender') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.lines.change-password', $line) }}"
                              onsubmit="return confirm('{{ __('¿Generar una nueva contraseña para esta línea? La contraseña actual dejará de funcionar.') }}')">
                            @csrf
                            <button class="w-full px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                                {{ __('Cambiar contraseña') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.lines.resend', $line) }}"
                              onsubmit="return confirm('{{ __('¿Reenviar las credenciales por correo al cliente?') }}')">
                            @csrf
                            <button class="w-full px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                                {{ __('Reenviar credenciales') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.lines.sync', $line) }}">
                            @csrf
                            <button class="w-full px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                                {{ __('Sincronizar con XUI') }}
                            </button>
                        </form>

                        <a href="{{ route('admin.lines.index') }}" class="block text-center text-sm text-dim hover:text-paper pt-1">
                            {{ __('Volver al listado') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
