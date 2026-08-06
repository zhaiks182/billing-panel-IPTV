@props(['status'])

@php
    $classes = match ($status) {
        'open' => 'bg-amber/10 text-amber',
        'answered' => 'bg-brand-500/10 text-brand-400',
        'in_progress' => 'bg-blue-500/10 text-blue-400',
        'closed' => 'bg-steel text-dim',
        default => 'bg-steel text-dim',
    };

    $labels = [
        'open' => 'Abierto',
        'answered' => 'Respondido',
        'in_progress' => 'En progreso',
        'closed' => 'Cerrado',
    ];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex px-2 py-1 text-xs font-semibold rounded-full $classes"]) }}>
    {{ $labels[$status] ?? $status }}
</span>
