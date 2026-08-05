<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">{{ __('Plantillas de correo') }}</h2>
            <x-close-link :href="route('admin.dashboard')" />
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <p class="text-sm text-dim mb-6">
                {{ __('Estos son los correos que el sistema envía automáticamente a los clientes. Puedes editar el asunto, el diseño HTML y la versión en texto plano de cada uno.') }}
            </p>

            <div class="bg-panel border border-steel rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-steel">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Correo') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Asunto') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @foreach ($templates as $template)
                            <tr>
                                <td class="px-6 py-4 text-sm text-paper">{{ $template->name }}</td>
                                <td class="px-6 py-4 text-sm text-dim-2">{{ $template->subject }}</td>
                                <td class="px-6 py-4 text-sm text-right">
                                    <a href="{{ route('admin.email-templates.edit', $template) }}" class="text-paper underline">{{ __('Editar') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
