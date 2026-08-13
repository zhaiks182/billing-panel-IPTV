<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <nav class="text-sm text-dim-2">
                <a href="{{ route('admin.help.articles.index') }}" class="hover:underline">{{ __('Artículos de ayuda') }}</a>
                <span class="mx-1">/</span>
                <span class="text-dim">{{ $article->category->name }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3 mt-1">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ $article->title }}</h2>
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $article->category->audience === 'internal' ? 'bg-amber/10 text-amber' : 'bg-brand-500/10 text-brand-400' }}">
                {{ $article->category->audience === 'internal' ? __('Interna') : __('Pública') }}
            </span>
            <x-close-link :href="route('admin.help.articles.index')" />
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="flex justify-end">
                <a href="{{ route('admin.help.articles.edit', $article) }}" class="text-sm text-brand-400 underline">{{ __('Editar este artículo') }}</a>
            </div>

            <div class="bg-panel border border-steel rounded-lg p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    @if ($article->icon)
                        <span class="text-3xl leading-none">{{ $article->icon }}</span>
                    @endif
                    <h1 class="text-2xl sm:text-3xl font-display font-bold text-paper">{{ $article->title }}</h1>
                </div>
                <x-help-article-content :article="$article" />
            </div>
        </div>
    </div>
</x-admin-layout>
