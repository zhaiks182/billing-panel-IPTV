@php
    $pendingTicketsCount = \App\Models\Ticket::where('status', 'open')->count();

    $navLinkClasses = fn (bool $active) => 'flex items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium transition '
        .($active ? 'bg-brand-500/10 text-brand-500' : 'text-dim hover:text-paper hover:bg-panel-alt');
@endphp

<!-- Fondo oscuro al abrir el sidebar en mobile -->
<div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" x-cloak
     class="fixed inset-0 z-40 bg-black/60 lg:hidden"></div>

<aside
    class="fixed inset-y-0 left-0 z-50 flex w-64 transform flex-col border-r border-steel bg-panel transition-transform duration-200 ease-in-out lg:static lg:z-auto lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex items-center justify-between border-b border-steel px-4 py-4">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
            <x-application-logo class="h-8 w-auto" />
            <span class="text-xs font-semibold uppercase tracking-wide text-dim-2">Panel Admin</span>
        </a>
        <button @click="sidebarOpen = false" class="text-dim hover:text-paper lg:hidden" aria-label="{{ __('Cerrar menú') }}">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4 scrollbar-dark">
        <a href="{{ route('admin.dashboard') }}" class="{{ $navLinkClasses(request()->routeIs('admin.dashboard')) }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            {{ __('Dashboard') }}
        </a>

        <div>
            <p class="mb-2 flex items-center gap-2 px-3 text-xs font-semibold uppercase tracking-wide text-dim-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.106.147.279.322.397.14.093.324.176.58.253v-1.69c-.312.076-.52.17-.65.258-.211.14-.257.264-.257.383 0 .1.026.212.005.399z" />
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v.75h-.014c-.596.033-1.2.19-1.687.51-.542.357-.949.933-.949 1.673 0 .74.407 1.316.949 1.673.487.32 1.091.477 1.687.51h.014v1.696a3.14 3.14 0 01-.658-.281c-.29-.174-.438-.36-.474-.492a.75.75 0 00-1.448.39c.192.71.72 1.163 1.238 1.47.343.202.72.34 1.343.408v.68a.75.75 0 001.5 0v-.68a3.13 3.13 0 001.34-.408c.523-.307 1.05-.76 1.243-1.47.216-.795-.184-1.462-.752-1.88-.484-.36-1.126-.55-1.75-.582V9.13c.312-.076.52-.17.65-.258.212-.14.257-.264.257-.383 0-.1-.026-.212-.005-.399a1.5 1.5 0 00-.317-.397C10.895 7.5 10.71 7.417 10.454 7.34V9.03z" clip-rule="evenodd" />
                </svg>
                {{ __('Ventas') }}
            </p>
            <div class="space-y-1">
                <a href="{{ route('admin.orders.index') }}" class="{{ $navLinkClasses(request()->routeIs('admin.orders.*')) }}">{{ __('Pedidos') }}</a>
            </div>
        </div>

        <div>
            <p class="mb-2 flex items-center gap-2 px-3 text-xs font-semibold uppercase tracking-wide text-dim-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.638 1.093a.75.75 0 01.724 0l2 1.104a.75.75 0 11-.724 1.313L10 2.788l-1.638.722a.75.75 0 11-.724-1.313l2-1.104zM5.403 4.287a.75.75 0 01-.295 1.019l-.805.444.805.444a.75.75 0 01-.724 1.314L3.5 7.02v.73a.75.75 0 01-1.5 0v-2a.75.75 0 01.388-.657l1.996-1.1a.75.75 0 011.019.294zm9.194 0a.75.75 0 011.02-.295l1.995 1.101A.75.75 0 0118 5.75v2a.75.75 0 01-1.5 0v-.73l-.884.488a.75.75 0 11-.724-1.314l.806-.444-.806-.444a.75.75 0 01-.295-1.02zM7.343 8.284a.75.75 0 011.02-.294L10 8.856l1.638-.866a.75.75 0 11.724 1.314l-1.612.848v2.264a.75.75 0 01-1.5 0V10.15l-1.612-.848a.75.75 0 01-.295-1.019zM2.75 11.5a.75.75 0 01.75.75v1.936l.086.048 1.5.83a.75.75 0 01-.724 1.313l-1.5-.83a.75.75 0 01-.386-.657v-2.64a.75.75 0 01.274-.75zm14.5 0a.75.75 0 01.274.75v2.64a.75.75 0 01-.386.657l-1.5.83a.75.75 0 11-.724-1.313l1.5-.83.086-.048V12.25a.75.75 0 01.75-.75zm-7.25 6.5v-2.64a.75.75 0 011.5 0v2.64a.75.75 0 01-1.5 0z" clip-rule="evenodd" />
                </svg>
                {{ __('Paquetes') }}
            </p>
            <div class="space-y-1">
                <a href="{{ route('admin.paquetes.index') }}" class="{{ $navLinkClasses(request()->routeIs('admin.paquetes.*')) }}">{{ __('Paquetes') }}</a>
                <a href="{{ route('admin.categorias.index') }}" class="{{ $navLinkClasses(request()->routeIs('admin.categorias.*')) }}">{{ __('Categorías') }}</a>
            </div>
        </div>

        <div>
            <p class="mb-2 flex items-center gap-2 px-3 text-xs font-semibold uppercase tracking-wide text-dim-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M2.5 4A1.5 1.5 0 001 5.5V6h18v-.5A1.5 1.5 0 0017.5 4h-15zM19 8.5H1v6A1.5 1.5 0 002.5 16h15a1.5 1.5 0 001.5-1.5v-6zM3 13.25a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zm4.75-.75a.75.75 0 000 1.5h3.5a.75.75 0 000-1.5h-3.5z" clip-rule="evenodd" />
                </svg>
                {{ __('Métodos de pago') }}
            </p>
            <div class="space-y-1">
                <a href="{{ route('admin.metodos-pago.index') }}" class="{{ $navLinkClasses(request()->routeIs('admin.metodos-pago.*')) }}">{{ __('Métodos de pago') }}</a>
            </div>
        </div>

        <div>
            <p class="mb-2 flex items-center gap-2 px-3 text-xs font-semibold uppercase tracking-wide text-dim-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                </svg>
                {{ __('Soporte') }}
            </p>
            <div class="space-y-1">
                <a href="{{ route('admin.tickets.index') }}" class="{{ $navLinkClasses(request()->routeIs('admin.tickets.*')) }} justify-between">
                    <span>{{ __('Tickets') }}</span>
                    @if ($pendingTicketsCount > 0)
                        <span class="flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                            {{ $pendingTicketsCount > 9 ? '9+' : $pendingTicketsCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        <div>
            <p class="mb-2 flex items-center gap-2 px-3 text-xs font-semibold uppercase tracking-wide text-dim-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                </svg>
                {{ __('Usuarios') }}
            </p>
            <div class="space-y-1">
                <a href="{{ route('admin.users.index') }}" class="{{ $navLinkClasses(request()->routeIs('admin.users.*')) }}">{{ __('Usuarios') }}</a>
            </div>
        </div>

        <div>
            <p class="mb-2 flex items-center gap-2 px-3 text-xs font-semibold uppercase tracking-wide text-dim-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                </svg>
                {{ __('Configuración') }}
            </p>
            <div class="space-y-1">
                <a href="{{ route('admin.xui.edit') }}" class="{{ $navLinkClasses(request()->routeIs('admin.xui.*')) }}">{{ __('XUI One') }}</a>
                <a href="{{ route('admin.mail.edit') }}" class="{{ $navLinkClasses(request()->routeIs('admin.mail.*')) }}">{{ __('SMTP') }}</a>
                <a href="{{ route('admin.turnstile.edit') }}" class="{{ $navLinkClasses(request()->routeIs('admin.turnstile.*')) }}">{{ __('Cloudflare Turnstile') }}</a>
                <a href="{{ route('admin.telegram.edit') }}" class="{{ $navLinkClasses(request()->routeIs('admin.telegram.*')) }}">{{ __('Telegram') }}</a>
                <a href="{{ route('admin.email-templates.index') }}" class="{{ $navLinkClasses(request()->routeIs('admin.email-templates.*')) }}">{{ __('Plantillas de correo') }}</a>
            </div>
        </div>
    </nav>

    <div class="border-t border-steel px-4 py-4">
        <p class="truncate text-sm font-medium text-paper">{{ Auth::user()->name }}</p>
        <p class="truncate text-xs text-dim">{{ Auth::user()->email }}</p>

        <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="flex w-full items-center gap-2 text-sm text-dim hover:text-paper">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V4a1 1 0 00-1-1H3zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                </svg>
                {{ __('Cerrar sesión') }}
            </button>
        </form>
    </div>
</aside>
