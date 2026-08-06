@php
    $token = request('token');
    $replyUrl = route('tickets.reply', $ticket).($token ? '?token='.$token : '');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">
                {{ __('Ticket') }} #{{ $ticket->ticket_number }}
            </h2>
            <x-ticket-status-badge :status="$ticket->status" />
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-danger/10 border border-danger text-danger px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2 space-y-6 order-2 lg:order-1">
                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-paper">{{ $ticket->subject }}</h3>
                    </div>

                    @foreach ($ticket->messages as $message)
                        <div class="bg-panel border border-steel rounded-lg p-6 {{ $message->isFromAdmin() ? 'border-l-4 border-l-brand-500' : '' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-paper">
                                    {{ $message->authorName() }}
                                    @if ($message->isFromAdmin())
                                        <span class="ml-1 text-xs font-normal text-brand-400">({{ __('Soporte') }})</span>
                                    @endif
                                </span>
                                <span class="text-xs text-dim-2">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-dim whitespace-pre-line">{{ $message->message }}</p>

                            @if ($message->attachments->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($message->attachments as $attachment)
                                        <a href="{{ $attachment->url() }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-panel-alt border border-steel text-xs text-dim hover:text-paper">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $attachment->original_name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if ($ticket->status === 'closed' && $ticket->resolution)
                        <div class="bg-brand-500/10 border border-brand-800 rounded-lg p-6">
                            <h3 class="text-sm font-semibold text-brand-300 uppercase tracking-wide mb-2">{{ __('Solución aplicada') }}</h3>
                            <p class="text-sm text-brand-100 whitespace-pre-line">{{ $ticket->resolution }}</p>
                        </div>
                    @endif

                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <h3 class="text-base font-semibold text-paper mb-4">
                            {{ $ticket->status === 'closed' ? __('Responder para reabrir el ticket') : __('Responder') }}
                        </h3>
                        <form method="POST" action="{{ $replyUrl }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <div>
                                <textarea name="message" rows="4" required
                                          class="block w-full rounded-md border-steel bg-ink text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('message') }}</textarea>
                            </div>
                            <div>
                                <input name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.gif,.png,.txt,.pdf"
                                       class="block w-full text-sm text-dim">
                            </div>
                            <x-turnstile-widget :site-key="$turnstileSiteKey" />
                            <x-primary-button>{{ __('Enviar respuesta') }}</x-primary-button>
                        </form>
                    </div>
                </div>

                <div class="bg-panel border border-steel rounded-lg p-6 order-1 lg:order-2 lg:sticky lg:top-6 space-y-3 text-sm">
                    <h3 class="text-base font-semibold text-paper mb-2">{{ __('Detalles') }}</h3>

                    <div class="flex justify-between">
                        <span class="text-dim">{{ __('Cliente') }}</span>
                        <span class="text-paper text-right">{{ $ticket->customerName() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dim">{{ __('Categoría') }}</span>
                        <span class="text-dim-2">{{ $ticket->categoryLabel() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dim">{{ __('Prioridad') }}</span>
                        <span class="text-dim-2">{{ $ticket->priorityLabel() }}</span>
                    </div>
                    @if ($ticket->line)
                        <div class="flex justify-between">
                            <span class="text-dim">{{ __('Línea') }}</span>
                            <span class="text-dim-2 font-mono text-xs">{{ $ticket->line->xui_username }}</span>
                        </div>
                    @endif
                    @if ($ticket->order)
                        <div class="flex justify-between">
                            <span class="text-dim">{{ __('Pedido') }}</span>
                            <span class="text-dim-2">#{{ $ticket->order->id }}</span>
                        </div>
                    @endif
                    @if ($ticket->assignedAdmin)
                        <div class="flex justify-between">
                            <span class="text-dim">{{ __('Asignado a') }}</span>
                            <span class="text-dim-2">{{ $ticket->assignedAdmin->name }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-dim">{{ __('Apertura') }}</span>
                        <span class="text-dim-2">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if ($ticket->first_response_at)
                        <div class="flex justify-between">
                            <span class="text-dim">{{ __('Tiempo de respuesta') }}</span>
                            <span class="text-dim-2">{{ $ticket->created_at->diffForHumans($ticket->first_response_at, true) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
