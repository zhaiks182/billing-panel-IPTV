<x-app-layout metaDescription="Guías y tutoriales para instalar y configurar tu servicio IPTV.">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Ayuda') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
                <x-help-sidebar :categories="$categories" />

                <div class="lg:col-span-3 space-y-6">
                    <p class="text-dim">{{ __('Guías paso a paso para instalar y usar tu servicio IPTV.') }}</p>

                    @if ($categories->isEmpty())
                        <div class="bg-panel border border-steel rounded-lg p-6 text-dim-2">
                            {{ __('Todavía no hay guías publicadas.') }}
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($categories as $category)
                                <a href="{{ route('help.category', $category) }}"
                                   class="block bg-panel border border-steel rounded-lg p-6 hover:border-brand-500 transition">
                                    <h3 class="text-lg font-semibold text-paper mb-1 flex items-center gap-2">
                                        @if ($category->icon)
                                            <span>{{ $category->icon }}</span>
                                        @endif
                                        {{ $category->name }}
                                    </h3>
                                    @if ($category->description)
                                        <p class="text-sm text-dim mb-3">{{ $category->description }}</p>
                                    @endif
                                    <span class="text-xs text-dim-2">{{ __(':count guía(s)', ['count' => $category->articles->count()]) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
