<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ isset($title) ? $title.' - Panel Admin' : 'Panel Admin' }} · {{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32.png') }}">
        <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicons/favicon-48.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/favicon-180.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600|space-grotesk:500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-ink text-paper lg:flex">
            @include('layouts.admin-navigation')

            <div class="flex-1 min-w-0">
                <!-- Mobile top bar -->
                <div class="flex items-center justify-between bg-panel border-b border-steel px-4 py-3 lg:hidden">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <x-application-logo class="h-8 w-auto" />
                        <span class="text-xs font-semibold uppercase tracking-wide text-dim-2">Panel Admin</span>
                    </a>
                    <button @click="sidebarOpen = true" class="p-2 text-dim hover:text-paper" aria-label="{{ __('Abrir menú') }}">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                @isset($header)
                    <header class="border-b border-steel">
                        <div class="max-w-7xl mx-auto pt-8 pb-4 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
