<x-app-layout :title="$article->title" :metaDescription="$article->excerpt ?? $article->title">
    <x-slot name="header">
        <nav class="text-sm text-dim-2 mb-1">
            <a href="{{ route('help.index') }}" class="hover:underline">{{ __('Ayuda') }}</a>
            <span class="mx-1">/</span>
            <a href="{{ route('help.category', $category) }}" class="hover:underline">{{ $category->name }}</a>
        </nav>
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ $article->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
                <x-help-sidebar :categories="$categories" :active-category="$category" :active-article="$article" />

                <div class="lg:col-span-3">
                    <div class="bg-panel border border-steel rounded-lg p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            @if ($article->icon)
                                <span class="text-3xl leading-none">{{ $article->icon }}</span>
                            @endif
                            <h1 class="text-2xl sm:text-3xl font-display font-bold text-paper">{{ $article->title }}</h1>
                        </div>
                        <x-help-article-content :article="$article" />
                    </div>

                    @if ($previousArticle || $nextArticle)
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                @if ($previousArticle)
                                    <a href="{{ route('help.article', [$category, $previousArticle]) }}"
                                       class="block bg-panel border border-steel rounded-lg p-4 hover:border-brand-500 transition">
                                        <p class="text-xs text-dim-2 mb-1">{{ __('Anterior') }}</p>
                                        <p class="text-sm font-semibold text-brand-400">« {{ $previousArticle->title }}</p>
                                    </a>
                                @endif
                            </div>
                            <div>
                                @if ($nextArticle)
                                    <a href="{{ route('help.article', [$category, $nextArticle]) }}"
                                       class="block bg-panel border border-steel rounded-lg p-4 text-right hover:border-brand-500 transition">
                                        <p class="text-xs text-dim-2 mb-1">{{ __('Siguiente') }}</p>
                                        <p class="text-sm font-semibold text-brand-400">{{ $nextArticle->title }} »</p>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <a href="{{ route('help.category', $category) }}" class="mt-6 inline-block text-sm text-dim hover:text-paper">
                        {{ __('← Volver a :category', ['category' => $category->name]) }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
