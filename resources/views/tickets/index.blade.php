<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-paper leading-tight">
                {{ __('Mis Tickets') }}
            </h2>
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                {{ __('Abrir ticket') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-steel">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Asunto') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Categoría') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Fecha') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($tickets as $ticket)
                            <tr class="hover:bg-panel-alt cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                                <td class="px-6 py-4 text-sm text-dim">{{ $ticket->ticket_number }}</td>
                                <td class="px-6 py-4 text-sm text-paper">{{ $ticket->subject }}</td>
                                <td class="px-6 py-4 text-sm text-dim">{{ $ticket->categoryLabel() }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <x-ticket-status-badge :status="$ticket->status" />
                                </td>
                                <td class="px-6 py-4 text-sm text-dim-2">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-dim-2">
                                    {{ __('Aún no has abierto ningún ticket.') }}
                                    <a href="{{ route('tickets.create') }}" class="text-paper underline">{{ __('Abrir uno') }}</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
