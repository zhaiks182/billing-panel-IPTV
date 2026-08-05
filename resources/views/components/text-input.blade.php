@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-panel border-steel text-paper placeholder-dim-2 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm']) }}>
