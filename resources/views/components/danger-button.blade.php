<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-danger border border-transparent rounded-md font-medium text-sm text-white hover:brightness-110 active:brightness-95 focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2 focus:ring-offset-ink transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
