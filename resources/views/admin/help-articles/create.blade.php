<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Nuevo artículo') }}</h2>
            <x-close-link :href="route('admin.help.articles.index')" />
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-panel border border-steel rounded-lg p-6">
                <form method="POST" action="{{ route('admin.help.articles.store') }}">
                    @include('admin.help-articles._form')
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
