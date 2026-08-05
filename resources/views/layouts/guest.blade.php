<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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
    <body class="font-sans text-paper antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-ink">
            <div>
                <a href="/">
                    <x-application-logo class="w-40 h-auto" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-panel border border-steel overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

        <x-whatsapp-button />
    </body>
</html>
