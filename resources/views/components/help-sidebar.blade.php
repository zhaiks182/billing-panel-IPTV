@props(['categories', 'activeCategory' => null, 'activeArticle' => null])

<div class="bg-panel border border-steel rounded-lg p-4 space-y-5">
    @foreach ($categories as $cat)
        <div>
            <a href="{{ route('help.category', $cat) }}"
               class="flex items-center gap-2 px-2 py-1 text-sm font-semibold {{ $activeCategory && $activeCategory->id === $cat->id ? 'text-brand-400' : 'text-paper hover:text-brand-400' }}">
                @if ($cat->icon)
                    <span>{{ $cat->icon }}</span>
                @endif
                <span>{{ $cat->name }}</span>
            </a>
            @if ($cat->articles->isNotEmpty())
                <div class="mt-1 ml-3 pl-3 border-l border-steel space-y-0.5">
                    @foreach ($cat->articles as $art)
                        <a href="{{ route('help.article', [$cat, $art]) }}"
                           class="block px-2 py-1 rounded-md text-xs {{ $activeArticle && $activeArticle->id === $art->id ? 'bg-brand-500/10 text-brand-400 font-medium' : 'text-dim hover:text-paper' }}">
                            {{ $art->title }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
