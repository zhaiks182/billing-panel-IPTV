@props(['status'])

@php
    $classes = match ($status) {
        'pending' => 'bg-amber/10 text-amber',
        'approved' => 'bg-brand-500/10 text-brand-400',
        'rejected' => 'bg-danger/10 text-danger',
        'error' => 'bg-danger/10 text-danger',
        default => 'bg-steel text-dim',
    };

    $labels = [
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
        'error' => 'Error',
    ];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex px-2 py-1 text-xs font-semibold rounded-full $classes"]) }}>
    {{ $labels[$status] ?? $status }}
</span>
