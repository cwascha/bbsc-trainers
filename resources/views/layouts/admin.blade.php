<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — {{ $currentClub->name ?? config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ $currentClub?->logo_path ?: asset('images/trainersync-logo.svg') }}">
    <link rel="apple-touch-icon" href="{{ $currentClub?->logo_path ?: asset('images/trainersync-logo.svg') }}">
    <style>
      :root {
        --club-primary: {{ $currentClub->primary_color ?? '#1e3a5f' }};
        --club-accent:  {{ $currentClub->accent_color  ?? '#3b82f6' }};
      }
    </style>
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-sans antialiased bg-gray-100">

<nav class="text-white" style="background-color: var(--club-primary);" x-data>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center h-16 gap-4">

            {{-- Logo + club name — flex-shrink-0 so it never gets squeezed --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('admin.dashboard') }}">
                    <x-club-logo height="32px" />
                </a>
                <span class="font-bold text-sm leading-tight hidden lg:block">
                    {{ $currentClub->name ?? 'TrainerSync' }}<br>
                    <span class="font-normal opacity-70 text-xs">Admin</span>
                </span>
            </div>

            {{-- Nav items --}}
            <div class="flex items-center gap-0.5 ml-2 flex-1 min-w-0">

                @php
                    $navLink = 'whitespace-nowrap px-3 py-2 rounded text-sm hover:bg-black/20 transition-colors';
                    $navActive = 'whitespace-nowrap px-3 py-2 rounded text-sm bg-black/20';
                    $dropBtn = 'flex items-center gap-1 whitespace-nowrap px-3 py-2 rounded text-sm hover:bg-black/20 transition-colors cursor-pointer';
                    $dropBtnActive = 'flex items-center gap-1 whitespace-nowrap px-3 py-2 rounded text-sm bg-black/20 cursor-pointer';
                @endphp

                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? $navActive : $navLink }}">Overview</a>
                <a href="{{ route('admin.sessions.index') }}" class="{{ request()->routeIs('admin.sessions.*') ? $navActive : $navLink }}">Sessions</a>
                <a href="{{ route('admin.trainers.index') }}" class="{{ request()->routeIs('admin.trainers.*') ? $navActive : $navLink }}">Trainers</a>
                <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? $navActive : $navLink }}">Payroll</a>

                {{-- Communicate dropdown --}}
                @php $communicateActive = request()->routeIs('admin.email.*', 'admin.sms.*', 'admin.notifications.*'); @endphp
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="{{ $communicateActive ? $dropBtnActive : $dropBtn }}">
                        Communicate
                        <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute top-full left-0 mt-1 bg-white text-gray-800 rounded-lg shadow-lg py-1 min-w-40 z-50">
                        <a href="{{ route('admin.email.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('admin.email.*') ? 'font-semibold' : '' }}">Email</a>
                        <a href="{{ route('admin.sms.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('admin.sms.*') ? 'font-semibold' : '' }}">SMS</a>
                        <a href="{{ route('admin.notifications.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('admin.notifications.*') ? 'font-semibold' : '' }}">SMS Logs</a>
                    </div>
                </div>

                {{-- Content dropdown --}}
                @php $contentActive = request()->routeIs('admin.training-plans.*', 'admin.teams.*', 'admin.documents.*'); @endphp
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="{{ $contentActive ? $dropBtnActive : $dropBtn }}">
                        Content
                        <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute top-full left-0 mt-1 bg-white text-gray-800 rounded-lg shadow-lg py-1 min-w-40 z-50">
                        <a href="{{ route('admin.training-plans.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('admin.training-plans.*') ? 'font-semibold' : '' }}">Training Plans</a>
                        <a href="{{ route('admin.teams.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('admin.teams.*') ? 'font-semibold' : '' }}">Teams</a>
                        <a href="{{ route('admin.documents.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('admin.documents.*') ? 'font-semibold' : '' }}">Docs</a>
                    </div>
                </div>

                {{-- Settings dropdown --}}
                @php $settingsActive = request()->routeIs('admin.seasons.*', 'admin.admins.*', 'admin.club.*', 'admin.onboarding', 'help'); @endphp
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="{{ $settingsActive ? $dropBtnActive : $dropBtn }}">
                        Settings
                        <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute top-full left-0 mt-1 bg-white text-gray-800 rounded-lg shadow-lg py-1 min-w-44 z-50">
                        <a href="{{ route('admin.seasons.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('admin.seasons.*') ? 'font-semibold' : '' }}">Seasons</a>
                        <a href="{{ route('admin.admins.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('admin.admins.*') ? 'font-semibold' : '' }}">Club Settings</a>
                        <a href="{{ route('admin.club.edit') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('admin.club.*') ? 'font-semibold' : '' }}">Branding</a>
                        <div class="my-1 border-t border-gray-100"></div>
                        <a href="{{ route('admin.onboarding') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('admin.onboarding') ? 'font-semibold' : '' }}">Setup Guide</a>
                        <a href="{{ route('help') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 {{ request()->routeIs('help') ? 'font-semibold' : '' }}">Help</a>
                    </div>
                </div>

            </div>

            {{-- Trainer View (right side) --}}
            <a href="{{ route('dashboard') }}" class="flex-shrink-0 whitespace-nowrap px-3 py-2 rounded text-sm text-yellow-300 hover:bg-black/20 transition-colors">← Trainer View</a>
        </div>
    </div>
</nav>

@if($currentClub->is_demo)
<div class="bg-amber-400 text-amber-900 text-sm font-medium text-center px-4 py-2">
    🔍 This is a <strong>shared demo environment</strong> — data resets every hour. <a href="https://trainersync.co" class="underline font-semibold">Get started →</a>
</div>
@endif

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded">
            {{ session('error') }}
        </div>
    @endif

    @yield('content')
</main>

</body>
</html>
