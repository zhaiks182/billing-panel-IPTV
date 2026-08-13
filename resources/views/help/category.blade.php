<x-app-layout :title="$category->name" :metaDescription="$category->description ?? ($category->name.' - Guías de ayuda')">
    <x-slot name="header">
        <nav class="text-sm text-dim-2 mb-1">
            <a href="{{ route('help.index') }}" class="hover:underline">{{ __('Ayuda') }}</a>
            <span class="mx-1">/</span>
            <span class="text-dim">{{ $category->name }}</span>
        </nav>
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ $category->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
                {{-- Sidebar de categorías --}}
                <div class="bg-panel border border-steel rounded-lg p-4 space-y-1">
                    <p class="px-2 pb-2 text-xs font-semibold text-dim-2 uppercase tracking-wide">{{ __('Categorías') }}</p>

                    @foreach ($categories as $cat)
                        <a href="{{ route('help.category', $cat) }}"
                           class="flex items-center justify-between px-2 py-1.5 rounded-md text-sm {{ $cat->id === $category->id ? 'bg-brand-500/10 text-brand-400 font-medium' : 'text-dim hover:text-paper' }}">
                            <span>{{ $cat->name }}</span>
                            <span class="text-xs text-dim-2">{{ $cat->articles_count }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Artículos de la categoría seleccionada --}}
                <div class="lg:col-span-3">
                    @if ($category->description)
                        <p class="text-dim mb-6">{{ $category->description }}</p>
                    @endif

                    @if ($articles->isEmpty())
                        <div class="bg-panel border border-steel rounded-lg p-6 text-dim-2">
                            {{ __('Todavía no hay guías en esta categoría.') }}
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($articles as $article)
                                <a href="{{ route('help.article', [$category, $article]) }}"
                                   class="block bg-panel border border-steel rounded-lg p-5 hover:border-brand-500 transition">
                                    <h3 class="text-base font-semibold text-paper mb-1">{{ $article->title }}</h3>
                                    @if ($article->excerpt)
                                        <p class="text-sm text-dim">{{ $article->excerpt }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
