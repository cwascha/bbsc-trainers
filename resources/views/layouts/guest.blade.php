<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ $currentClub?->logo_path ?: '/images/trainersync-logo.svg' }}">
        <link rel="apple-touch-icon" href="{{ $currentClub?->logo_path ?: '/images/trainersync-logo.svg' }}">
        <link rel="manifest" href="/manifest.json">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="flex flex-col items-center space-y-3">
                <a href="/">
                    @if($currentClub?->logo_path)
                        <img src="{{ $currentClub->logo_path }}" alt="{{ $currentClub->name }}" style="height:80px;width:auto;">
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" height="52" viewBox="0 0 400 80" style="display:block;">
                            <text x="4" y="60" font-size="56" font-family="'Arial Black','Helvetica Neue',Arial,sans-serif" font-weight="900" letter-spacing="-1">
                                <tspan fill="#0c3a15">Trainer</tspan><tspan fill="#c4e21b">Sync</tspan>
                            </text>
                            <rect x="4" y="68" width="72" height="5" fill="#c4e21b" rx="2"/>
                        </svg>
                    @endif
                </a>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">{{ $currentClub?->name ?? 'TrainerSync' }}</h1>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>

            <!-- Footer links -->
            <div class="mt-6 text-center text-xs text-gray-400 space-x-3">
                <a href="{{ route('privacy') }}" class="underline hover:text-gray-600">Privacy Policy</a>
                <span>&middot;</span>
                <a href="{{ route('terms') }}" class="underline hover:text-gray-600">Terms of Service</a>
            </div>
        </div>
    </body>
</html>
