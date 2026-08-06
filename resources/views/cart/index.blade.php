<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">
                {{ __('Revisar Pedido') }}
            </h2>
            <x-close-link :href="route('home')" />
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-dim-2 mb-6">
                <a href="{{ route('home') }}" class="hover:text-paper">{{ __('Inicio') }}</a>
                <span>&rsaquo;</span>
                <span class="text-paper">{{ __('Carro de Pedidos') }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2 order-2 lg:order-1">
                    <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                        <table class="min-w-full divide-y divide-steel">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Producto/Opciones') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-dim-2 uppercase">{{ __('Precio/Ciclo') }}</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-steel">
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-paper">{{ $package->name }}</p>
                                        @if ($package->category)
                                            <p class="text-xs text-dim-2 mt-0.5">{{ $package->category->name }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <span class="font-semibold text-paper">
                                            {{ $package->is_trial ? __('Gratis') : '$'.number_format($package->price, 2).' USD' }}
                                        </span>
                                        <span class="block text-xs text-dim-2">{{ $package->durationLabel() }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form method="POST" action="{{ route('cart.destroy') }}">
                                            @csrf
                                            <button type="submit" aria-label="{{ __('Quitar') }}" class="text-dim-2 hover:text-danger">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <form method="POST" action="{{ route('cart.destroy') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                            {{ __('Vaciar Carro') }}
                        </button>
                    </form>
                </div>

                <div class="bg-panel border border-steel rounded-lg overflow-hidden order-1 lg:order-2 lg:sticky lg:top-6">
                    <div class="bg-panel-alt px-6 py-3">
                        <h3 class="text-sm font-semibold text-paper uppercase tracking-wide">{{ __('Sumario de Pedido') }}</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-dim">{{ __('Subtotal') }}</span>
                            <span class="text-paper">{{ $package->is_trial ? '$0.00 USD' : '$'.number_format($package->price, 2).' USD' }}</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-dim">{{ __('Total') }}</span>
                            <span class="text-dim-2 text-xs">{{ $package->durationLabel() }}</span>
                        </div>

                        <div class="mt-4 pt-4 border-t border-steel">
                            <p class="text-3xl font-display font-extrabold text-paper">
                                {{ $package->is_trial ? __('$0.00') : '$'.number_format($package->price, 2) }}
                                <span class="text-sm font-normal text-dim-2">USD</span>
                            </p>
                            <p class="text-xs text-dim-2">{{ __('Importe a la Fecha') }}</p>
                        </div>

                        <a href="{{ route('orders.create', $package) }}"
                           class="mt-6 flex items-center justify-center gap-2 w-full bg-brand-500 text-ink py-3 rounded-md hover:brightness-110 transition font-semibold">
                            {{ __('Comprar') }}
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="{{ route('home') }}" class="block text-center text-xs text-dim-2 hover:text-paper mt-3">
                            {{ __('Seguir Comprando') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
