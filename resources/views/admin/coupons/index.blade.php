<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Cupones') }}</h2>
            <a href="{{ route('admin.cupones.create') }}" class="bg-brand-600 text-white px-4 py-2 rounded-md text-sm hover:bg-brand-700">
                {{ __('Nuevo cupón') }}
            </a>
        </div>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Código') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Descuento') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Canjeados') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Vence') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($coupons as $coupon)
                            <tr>
                                <td class="px-6 py-4 text-sm text-paper font-mono">{{ $coupon->code }}</td>
                                <td class="px-6 py-4 text-sm text-dim">
                                    {{ $coupon->type === 'percent' ? number_format($coupon->value, 0).'%' : '$'.number_format($coupon->value, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-dim">
                                    {{ $coupon->redeemedCount() }}{{ $coupon->max_redemptions ? ' / '.$coupon->max_redemptions : '' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-dim">{{ $coupon->expires_at?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if (! $coupon->is_active)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-steel text-dim">{{ __('Inactivo') }}</span>
                                    @elseif ($coupon->isExpired())
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-danger/10 text-danger">{{ __('Vencido') }}</span>
                                    @elseif ($coupon->hasReachedLimit())
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-amber/10 text-amber">{{ __('Agotado') }}</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-brand-500/10 text-brand-400">{{ __('Activo') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-right space-x-2">
                                    <a href="{{ route('admin.cupones.edit', $coupon) }}" class="text-paper underline">{{ __('Editar') }}</a>
                                    <form method="POST" action="{{ route('admin.cupones.destroy', $coupon) }}" class="inline"
                                          onsubmit="return confirm('{{ __('¿Eliminar este cupón?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-700 underline">{{ __('Eliminar') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-dim-2">{{ __('No hay cupones.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
