@php
    $categories = [
        'installation' => 'Instalación', 'credentials' => 'Credenciales', 'payment' => 'Pago',
        'renewal' => 'Renovación', 'connection_limit' => 'Límite de conexiones',
        'intermittent_service' => 'Servicio intermitente', 'channels_content' => 'Canales o contenido', 'other' => 'Otro',
    ];
    $priorities = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta'];
    $statuses = ['open' => 'Abierto', 'answered' => 'Respondido', 'in_progress' => 'En progreso', 'closed' => 'Cerrado'];
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paper leading-tight">
            {{ __('Tickets de Soporte') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 bg-brand-500/10 border border-brand-800 text-brand-300 px-4 py-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-4 flex gap-2 text-sm flex-wrap">
                @foreach (['' => 'Todos'] + $statuses as $value => $label)
                    <a href="{{ route('admin.tickets.index', array_filter(['status' => $value ?: null, 'category' => request('category'), 'priority' => request('priority'), 'assigned_admin_id' => request('assigned_admin_id')])) }}"
                       class="px-3 py-1.5 rounded-md border {{ request('status', '') === $value ? 'bg-brand-600 text-white border-brand-600' : 'bg-panel text-dim border-steel' }}">
                        {{ __($label) }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.tickets.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
                @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div>
                    <x-input-label for="category" value="{{ __('Categoría') }}" />
                    <select id="category" name="category" class="mt-1 bg-panel border-steel text-paper rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        <option value="">{{ __('Todas') }}</option>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="priority" value="{{ __('Prioridad') }}" />
                    <select id="priority" name="priority" class="mt-1 bg-panel border-steel text-paper rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        <option value="">{{ __('Todas') }}</option>
                        @foreach ($priorities as $value => $label)
                            <option value="{{ $value }}" {{ request('priority') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="assigned_admin_id" value="{{ __('Asignado a') }}" />
                    <select id="assigned_admin_id" name="assigned_admin_id" class="mt-1 bg-panel border-steel text-paper rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                        <option value="">{{ __('Todos') }}</option>
                        @foreach ($admins as $admin)
                            <option value="{{ $admin->id }}" {{ (string) request('assigned_admin_id') === (string) $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 rounded-md bg-brand-500 text-ink text-sm font-semibold hover:brightness-110">
                    {{ __('Filtrar') }}
                </button>
                @if (request('category') || request('priority') || request('assigned_admin_id'))
                    <a href="{{ route('admin.tickets.index', array_filter(['status' => request('status')])) }}"
                       class="px-4 py-2 rounded-md bg-steel text-paper text-sm font-medium hover:bg-steel/80">
                        {{ __('Quitar filtros') }}
                    </a>
                @endif
            </form>

            <div class="bg-panel border border-steel rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-steel">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Cliente') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Asunto') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Categoría') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Prioridad') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Estado') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Asignado') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-dim-2 uppercase">{{ __('Fecha') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-steel">
                        @forelse ($tickets as $ticket)
                            <tr class="hover:bg-panel-alt cursor-pointer" onclick="window.location='{{ route('admin.tickets.show', $ticket) }}'">
                                <td class="px-4 py-4 text-sm text-dim">{{ $ticket->id }}</td>
                                <td class="px-4 py-4 text-sm text-dim">
                                    {{ $ticket->customerName() }}<br>
                                    <span class="text-xs text-dim-2">{{ $ticket->customerEmail() }}</span>
                                    @if ($ticket->isGuest())
                                        <span class="ml-1 inline-flex px-1.5 py-0.5 text-xs rounded bg-steel text-dim">{{ __('Invitado') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-paper">{{ $ticket->subject }}</td>
                                <td class="px-4 py-4 text-sm text-dim">{{ $ticket->categoryLabel() }}</td>
                                <td class="px-4 py-4 text-sm text-dim">{{ $ticket->priorityLabel() }}</td>
                                <td class="px-4 py-4 text-sm"><x-ticket-status-badge :status="$ticket->status" /></td>
                                <td class="px-4 py-4 text-sm text-dim-2">{{ $ticket->assignedAdmin->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-sm text-dim-2">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-dim-2">{{ __('No hay tickets.') }}</td>
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
