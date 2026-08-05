<x-app-layout :title="$category->name" :metaDescription="$category->description ?? ($category->name.' - Paquetes IPTV')">
    <x-slot name="header">
        <nav class="text-sm text-dim-2 mb-1">
            <a href="{{ route('home') }}" class="hover:underline">{{ __('Paquetes') }}</a>
            <span class="mx-1">/</span>
            <span class="text-dim">{{ $category->name }}</span>
        </nav>
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ $category->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($category->description)
                <p class="text-dim mb-6">{{ $category->description }}</p>
            @endif

            @if ($packages->isEmpty())
                <div class="bg-panel border border-steel rounded-lg p-6 text-dim-2">
                    {{ __('No hay paquetes disponibles en esta categoría por ahora.') }}
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
                    @foreach ($packages as $package)
                        <x-package-card :package="$package" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
