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
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-panel border border-steel rounded-lg p-6 sm:p-8">
                <x-help-article-content :article="$article" />
            </div>

            <a href="{{ route('help.category', $category) }}" class="mt-6 inline-block text-sm text-dim hover:text-paper">
                {{ __('← Volver a :category', ['category' => $category->name]) }}
            </a>
        </div>
    </div>
</x-app-layout>
