@props(['text'])

<button type="button"
        x-data="{ copied: false }"
        @click.stop.prevent="navigator.clipboard.writeText(@js($text)); copied = true; setTimeout(() => copied = false, 1500)"
        class="inline-flex items-center shrink-0 text-dim-2 hover:text-paper"
        title="{{ __('Copiar') }}">
    <svg x-show="!copied" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
        <path d="M7 3.5A1.5 1.5 0 018.5 2h3.879a1.5 1.5 0 011.06.44l3.122 3.12A1.5 1.5 0 0117 6.622V12.5a1.5 1.5 0 01-1.5 1.5h-1v-3.379a3 3 0 00-.879-2.121L10.5 5.379A3 3 0 008.379 4.5H7v-1z" />
        <path d="M4.5 6A1.5 1.5 0 003 7.5v9A1.5 1.5 0 004.5 18h7a1.5 1.5 0 001.5-1.5v-6.879a1.5 1.5 0 00-.44-1.06L9.44 5.44A1.5 1.5 0 008.378 5H4.5z" />
    </svg>
    <svg x-show="copied" x-cloak class="h-3.5 w-3.5 text-brand-400" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.25 7.25a1 1 0 01-1.42 0l-3.25-3.25a1 1 0 111.42-1.42l2.54 2.54 6.54-6.54a1 1 0 011.42 0z" clip-rule="evenodd" />
    </svg>
</button>
