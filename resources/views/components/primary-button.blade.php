<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-brand-500 border border-transparent rounded-md font-medium text-sm text-ink hover:brightness-110 focus:brightness-110 active:brightness-95 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-ink transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
