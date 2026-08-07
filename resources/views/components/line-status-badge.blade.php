@props(['line'])

@php
    $status = $line->displayStatus();

    $classes = match ($status) {
        'active' => 'bg-brand-500/10 text-brand-400',
        'expiring_soon' => 'bg-amber/10 text-amber',
        'expired' => 'bg-danger/10 text-danger',
        'suspended' => 'bg-steel text-dim',
        default => 'bg-steel text-dim',
    };

    $dotClasses = match ($status) {
        'active' => 'bg-brand-400',
        'expiring_soon' => 'bg-amber',
        'expired' => 'bg-danger',
        'suspended' => 'bg-dim-2',
        default => 'bg-dim-2',
    };

    $label = App\Models\Line::displayStatusLabels()[$status] ?? $status;
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2 py-1 text-xs font-semibold rounded-full $classes"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $dotClasses }}"></span>
    {{ $label }}
</span>
