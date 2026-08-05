<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-transparent border border-steel rounded-md font-medium text-sm text-paper hover:border-brand-500 hover:text-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-ink disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
