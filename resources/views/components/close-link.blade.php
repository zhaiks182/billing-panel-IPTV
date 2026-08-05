@props(['href'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'text-dim hover:text-paper transition']) }} aria-label="Cerrar">
    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
    </svg>
</a>
