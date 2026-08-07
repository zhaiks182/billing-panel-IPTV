<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Líneas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-4 flex flex-wrap gap-2 text-sm">
                @foreach (['' => 'Todas', 'active' => 'Activas', 'expiring_soon' => 'Por vencer', 'expired' => 'Vencidas', 'suspended' => 'Suspendidas', 'demo' => 'Demos'] as $value => $label)
                    <a href="{{ route('admin.lines.index', array_filter(['status' => $value ?: null, 'q' => $search ?: null])) }}"
                       class="px-3 py-1.5 rounded-md border {{ $statusFilter === $value ? 'bg-brand-600 text-white border-brand-600' : 'bg-panel text-dim border-steel' }}">
                        {{ __($label) }}
                    </a>
                @endforeach
            </div>

            <p class="mb-4 text-sm text-dim-2">
                {{ __(':total línea(s) en total.', ['total' => $lines->total()]) }}
            </p>

            <form method="GET" action="{{ route('admin.lines.index') }}" class="mb-4 flex gap-2">
                @if ($statusFilter !== '')
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                <x-text-input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Buscar por cliente, correo o usuario XUI...') }}" class="w-full max-w-sm" />
                <button type="submit" class="px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                    {{ __('Buscar') }}
                </button>
                @if ($search !== '')
                    <a href="{{ route('admin.lines.index', array_filter(['status' => $statusFilter ?: null])) }}" class="px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                        {{ __('Limpiar') }}
                    </a>
                @endif
            </form>

            <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-steel">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Cliente') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Usuario XUI') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Plan') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Vence') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Conex.') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Acción') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($lines as $line)
                            <tr>
                                <td class="px-4 py-4 text-sm">
                                    <p class="text-paper">{{ $line->user->name }}</p>
                                    <p class="text-xs text-dim-2">{{ $line->user->email }}</p>
                                </td>
                                <td class="px-4 py-4 text-sm text-dim font-mono">{{ $line->xui_username }}</td>
                                <td class="px-4 py-4 text-sm text-dim">{{ $line->order?->package?->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-sm">
                                    <x-line-status-badge :line="$line" />
                                </td>
                                <td class="px-4 py-4 text-sm text-dim">{{ $line->expires_at?->format('d/m/y') ?? '—' }}</td>
                                <td class="px-4 py-4 text-sm text-dim">{{ $line->max_connections }}</td>
                                <td class="px-4 py-4 text-sm">
                                    <a href="{{ route('admin.lines.show', $line) }}" class="text-brand-400 hover:underline">{{ __('Ver') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-dim-2">{{ __('No hay líneas.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $lines->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
