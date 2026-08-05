@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-brand-400']) }}>
        {{ $status }}
    </div>
@endif
