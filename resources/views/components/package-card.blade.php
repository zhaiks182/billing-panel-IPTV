@props(['package', 'compact' => false])

@php
    $bodyPad = $compact ? 'p-4' : 'p-6';
    $footPad = $compact ? 'p-4 pt-0' : 'p-6 pt-0';
    $titleSize = $compact ? 'text-base' : 'text-lg';
    $priceSize = $compact ? 'text-2xl' : 'text-3xl';
    $listSpacing = $compact ? 'mt-3 space-y-1.5 text-xs' : 'mt-4 space-y-2 text-sm';
    $iconSize = $compact ? 'h-4 w-4' : 'h-5 w-5';
    $buttonPad = $compact ? 'py-2 text-sm' : 'py-2.5';
@endphp

<div class="bg-panel rounded-lg border border-steel flex flex-col hover:border-brand-700 transition">
    <div class="{{ $bodyPad }} flex-1 flex flex-col">
        <div class="flex items-center gap-2">
            <h3 class="{{ $titleSize }} font-bold text-paper">{{ $package->name }}</h3>
            @if ($package->is_trial)
                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-amber/10 text-amber">{{ __('Demo') }}</span>
            @endif
        </div>
        @if ($package->description)
            <p class="mt-1 text-sm text-dim-2">{{ $package->description }}</p>
        @endif

        @if ($package->featureList())
            <ul class="{{ $listSpacing }} text-dim flex-1">
                @foreach ($package->featureList() as $feature)
                    <li class="flex items-start gap-2">
                        <svg class="{{ $iconSize }} shrink-0 text-brand-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.42 0l-3.25-3.25a1 1 0 111.42-1.42l2.54 2.54 6.54-6.54a1 1 0 011.42 0z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="{{ $footPad }} mt-auto">
        <div class="border-t border-steel pt-4">
            <p class="{{ $priceSize }} font-extrabold text-paper">
                @if ($package->is_trial)
                    {{ __('Gratis') }}
                @else
                    ${{ number_format($package->price, 2) }}
                    <span class="text-sm font-normal text-dim-2">USD</span>
                @endif
            </p>
            <p class="text-xs text-dim-2 mb-4">
                {{ __('Cada') }} {{ $package->durationLabel() }}
            </p>

            <form method="POST" action="{{ route('cart.store', $package) }}">
                @csrf
                <button type="submit"
                        class="flex items-center justify-center gap-2 w-full bg-brand-500 text-ink {{ $buttonPad }} rounded-md hover:brightness-110 transition font-medium">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.25 2.75a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a2.25 2.25 0 002.175 1.68h6.494a2.25 2.25 0 002.19-1.75l1.202-5.25a.75.75 0 00-.73-.92H6.24l-.62-2.328A1.87 1.87 0 003.636 2.75H2.25z" />
                        <path d="M8.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM17 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                    </svg>
                    {{ __('Pedir Ahora') }}
                </button>
            </form>
        </div>
    </div>
</div>
