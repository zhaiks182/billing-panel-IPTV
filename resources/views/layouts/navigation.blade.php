<nav x-data="{ open: false }" class="bg-panel border-b border-steel">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex sm:items-center">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        {{ __('Paquetes') }}
                    </x-nav-link>

                    @guest
                        <x-nav-link :href="route('tickets.create')" :active="request()->routeIs('tickets.create')">
                            {{ __('Abrir Ticket') }}
                        </x-nav-link>
                    @endguest

                    @auth
                        @php
                            $navDropdownClasses = fn (bool $active) => $active
                                ? 'inline-flex items-center gap-1 px-1 pt-1 border-b-2 border-brand-500 text-sm font-medium leading-5 text-paper focus:outline-none transition duration-150 ease-in-out'
                                : 'inline-flex items-center gap-1 px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-dim hover:text-paper hover:border-steel focus:outline-none focus:text-paper focus:border-steel transition duration-150 ease-in-out';
                        @endphp

                        <x-dropdown align="left" width="48" class="flex items-center">
                            <x-slot name="trigger">
                                <button type="button" class="{{ $navDropdownClasses(request()->routeIs('dashboard')) }}">
                                    {{ __('Servicios') }}
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content" :contentClasses="'py-1 bg-panel-alt border border-steel'">
                                <x-dropdown-link :href="route('dashboard')">{{ __('Mis Servicios') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>

                        <x-dropdown align="left" width="48" class="flex items-center">
                            <x-slot name="trigger">
                                <button type="button" class="{{ $navDropdownClasses(request()->routeIs('orders.*')) }}">
                                    {{ __('Facturación') }}
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content" :contentClasses="'py-1 bg-panel-alt border border-steel'">
                                <x-dropdown-link :href="route('orders.index')">{{ __('Mis Facturas') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>

                        <x-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">
                            {{ __('Abrir Ticket') }}
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            @auth
                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
                    <a href="{{ route('cart.index') }}" class="relative inline-flex items-center p-2 text-dim hover:text-paper" aria-label="{{ __('Carrito') }}">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.25 2.75a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a2.25 2.25 0 002.175 1.68h6.494a2.25 2.25 0 002.19-1.75l1.202-5.25a.75.75 0 00-.73-.92H6.24l-.62-2.328A1.87 1.87 0 003.636 2.75H2.25z" />
                            <path d="M8.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM17 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                        </svg>
                        @if (session('cart_package_id'))
                            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-ink">1</span>
                        @endif
                    </a>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-dim hover:text-paper focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content" :contentClasses="'py-1 bg-panel-alt border border-steel'">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @else
                <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
                    <a href="{{ route('cart.index') }}" class="relative inline-flex items-center p-2 text-dim hover:text-paper" aria-label="{{ __('Carrito') }}">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.25 2.75a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a2.25 2.25 0 002.175 1.68h6.494a2.25 2.25 0 002.19-1.75l1.202-5.25a.75.75 0 00-.73-.92H6.24l-.62-2.328A1.87 1.87 0 003.636 2.75H2.25z" />
                            <path d="M8.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM17 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                        </svg>
                        @if (session('cart_package_id'))
                            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-ink">1</span>
                        @endif
                    </a>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-sm text-dim hover:text-paper">{{ __('Iniciar sesión') }}</a>
                        <a href="{{ route('register') }}" class="text-sm text-dim hover:text-paper">{{ __('Registrarse') }}</a>
                    </div>
                </div>
            @endauth

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-dim hover:text-paper hover:bg-panel-alt focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('Paquetes') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.index')">
                {{ __('Carrito') }}
                @if (session('cart_package_id'))
                    <span class="ml-1 inline-flex px-1.5 py-0.5 text-xs rounded-full bg-brand-500 text-ink">1</span>
                @endif
            </x-responsive-nav-link>

            @guest
                <x-responsive-nav-link :href="route('tickets.create')" :active="request()->routeIs('tickets.create')">
                    {{ __('Abrir Ticket') }}
                </x-responsive-nav-link>
            @endguest

            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Mis Servicios') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                    {{ __('Mis Facturas') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">
                    {{ __('Abrir Ticket') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-steel">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-paper">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-dim">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')">{{ __('Iniciar sesión') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">{{ __('Registrarse') }}</x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>
