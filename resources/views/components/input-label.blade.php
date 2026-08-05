@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-dim']) }}>
    {{ $value ?? $slot }}
</label>
