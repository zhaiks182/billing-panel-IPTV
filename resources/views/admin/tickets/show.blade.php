@php
    $categories = [
        'installation' => 'Instalación', 'credentials' => 'Credenciales', 'payment' => 'Pago',
        'renewal' => 'Renovación', 'connection_limit' => 'Límite de conexiones',
        'intermittent_service' => 'Servicio intermitente', 'channels_content' => 'Canales o contenido', 'other' => 'Otro',
    ];
    $priorities = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta'];
    $statuses = ['open' => 'Abierto', 'answered' => 'Respondido', 'in_progress' => 'En progreso', 'closed' => 'Cerrado'];
@endphp
<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-xl text-paper leading-tight">
                {{ __('Ticket') }} #{{ $ticket->ticket_number }}
            </h2>
            <x-ticket-status-badge :status="$ticket->status" />
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
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
                                            {{ $attachment->original_name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <h3 class="text-base font-semibold text-paper mb-4">{{ __('Responder') }}</h3>
                        <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <textarea name="message" rows="4" required
                                      class="block w-full rounded-md border-steel bg-ink text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('message') }}</textarea>
                            <input name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.gif,.png,.txt,.pdf"
                                   class="block w-full text-sm text-dim">
                            <x-turnstile-widget :site-key="$turnstileSiteKey" />
                            <x-primary-button>{{ __('Enviar respuesta') }}</x-primary-button>
                        </form>
                    </div>
                </div>

                <div class="space-y-6 order-1 lg:order-2 lg:sticky lg:top-6">
                    <div class="bg-panel border border-steel rounded-lg p-6 space-y-3 text-sm">
                        <h3 class="text-base font-semibold text-paper mb-2">{{ __('Detalles') }}</h3>
                        <div class="flex justify-between">
                            <span class="text-dim">{{ __('Cliente') }}</span>
                            <span class="text-paper text-right">{{ $ticket->customerName() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-dim">{{ __('Correo') }}</span>
                            <span class="text-dim-2 text-right">{{ $ticket->customerEmail() }}</span>
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
                                <span class="text-dim-2">#{{ $ticket->order->order_number }}</span>
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

                    <div class="bg-panel border border-steel rounded-lg p-6">
                        <h3 class="text-base font-semibold text-paper mb-4">{{ __('Gestionar ticket') }}</h3>
                        <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}" class="space-y-5">
                            @csrf
                            @method('PUT')

                            <div>
                                <x-input-label for="category" value="{{ __('Categoría') }}" />
                                <select id="category" name="category" class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                                    @foreach ($categories as $value => $label)
                                        <option value="{{ $value }}" {{ $ticket->category === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="priority" value="{{ __('Prioridad') }}" />
                                <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                                    @foreach ($priorities as $value => $label)
                                        <option value="{{ $value }}" {{ $ticket->priority === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="status" value="{{ __('Estado') }}" />
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ $ticket->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="assigned_admin_id" value="{{ __('Administrador asignado') }}" />
                                <select id="assigned_admin_id" name="assigned_admin_id" class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                                    <option value="">{{ __('Sin asignar') }}</option>
                                    @foreach ($admins as $admin)
                                        <option value="{{ $admin->id }}" {{ $ticket->assigned_admin_id === $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="resolution" value="{{ __('Solución aplicada') }}" />
                                <textarea id="resolution" name="resolution" rows="3"
                                          class="mt-1 block w-full rounded-md border-steel bg-ink text-paper shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">{{ old('resolution', $ticket->resolution) }}</textarea>
                            </div>

                            <x-secondary-button type="submit" class="w-full justify-center">{{ __('Guardar cambios') }}</x-secondary-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
