<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Editar método de pago') }}</h2>
            <x-close-link :href="route('admin.metodos-pago.index')" />
        </div>
    </x-slot>

    <div class="py-12" x-data @keydown.escape.window="window.location = '{{ route('admin.metodos-pago.index') }}'">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-panel border border-steel rounded-lg p-6">
                <form method="POST" action="{{ route('admin.metodos-pago.update', $paymentMethod) }}">
                    @include('admin.payment-methods._form')
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
