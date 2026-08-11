<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ isset($title) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>
        @isset($metaDescription)
            <meta name="description" content="{{ $metaDescription }}">
        @endisset

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
        <div class="min-h-screen bg-ink text-paper">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-steel">
                    <div class="max-w-7xl mx-auto pt-8 pb-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <x-whatsapp-button />
    </body>
</html>
