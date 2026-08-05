<x-app-layout metaDescription="Paquetes y planes IPTV: elige tu plan y activa tu línea M3U al instante.">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Paquetes IPTV') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">
            @if (session('status'))
                <div class="bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if ($categories->isEmpty() && $uncategorized->isEmpty())
                <div class="bg-panel border border-steel rounded-lg p-6 text-dim-2">
                    {{ __('Todavía no hay paquetes disponibles. Vuelve pronto.') }}
                </div>
            @endif

            @if ($trialPackage)
                <section class="rounded-xl border border-brand-700/50 bg-gradient-to-br from-brand-900 via-panel-alt to-ink p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                        <div>
                            <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-amber/10 text-amber mb-3">
                                {{ __('Sin compromiso') }}
                            </span>
                            <h3 class="text-2xl font-display font-bold text-paper">
                                {{ __('Prueba gratis por 2 horas') }}
                            </h3>
                            <p class="mt-2 text-sm text-dim max-w-xl">
                                {{ __('Activa tu línea de demostración al instante, sin pagar nada, y compruébalo por ti mismo antes de suscribirte.') }}
                            </p>
                        </div>

                        <div class="shrink-0">
                            @if ($trialAlreadyUsed)
                                <span class="block text-center text-sm text-dim-2 max-w-[14rem]">
                                    {{ __('¿Te gustó la demo? Elige tu plan y sigue disfrutando sin interrupciones.') }}
                                </span>
                            @else
                                <form method="POST" action="{{ route('cart.store', $trialPackage) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center gap-2 bg-amber text-ink px-6 py-3 rounded-md font-semibold hover:brightness-110 transition whitespace-nowrap">
                                        {{ __('Activar demo gratis') }}
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </section>

                <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-2 text-sm text-dim-2 -mt-6">
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-brand-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.42 0l-3.25-3.25a1 1 0 111.42-1.42l2.54 2.54 6.54-6.54a1 1 0 011.42 0z" clip-rule="evenodd" /></svg>
                        {{ __('+8,500 canales en vivo') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-brand-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.42 0l-3.25-3.25a1 1 0 111.42-1.42l2.54 2.54 6.54-6.54a1 1 0 011.42 0z" clip-rule="evenodd" /></svg>
                        {{ __('Activación al instante') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-brand-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.42 0l-3.25-3.25a1 1 0 111.42-1.42l2.54 2.54 6.54-6.54a1 1 0 011.42 0z" clip-rule="evenodd" /></svg>
                        {{ __('Soporte por WhatsApp') }}
                    </span>
                </div>
            @endif

            @foreach ($categories as $category)
                <section>
                    <div class="flex items-baseline justify-between mb-4">
                        <h3 class="text-xl font-bold text-paper">{{ $category->name }}</h3>
                        <a href="{{ route('packages.category', $category) }}" class="text-sm text-brand-700 hover:underline">
                            {{ __('Ver todos') }} &rarr;
                        </a>
                    </div>
                    @if ($category->description)
                        <p class="text-sm text-dim-2 mb-4 -mt-2">{{ $category->description }}</p>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
                        @foreach ($category->packages as $package)
                            <x-package-card :package="$package" />
                        @endforeach
                    </div>
                </section>
            @endforeach

            @if ($uncategorized->isNotEmpty())
                <section>
                    @if ($categories->isNotEmpty())
                        <h3 class="text-xl font-bold text-paper mb-4">{{ __('Otros paquetes') }}</h3>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
                        @foreach ($uncategorized as $package)
                            <x-package-card :package="$package" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
