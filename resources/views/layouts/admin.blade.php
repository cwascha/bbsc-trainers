<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — {{ config('app.name', 'BBSC Trainer Dashboard') }}</title>
    <link rel="icon" type="image/png" href="/images/BBSClogo.png">
    <link rel="apple-touch-icon" href="/images/BBSClogo.png">
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">

<nav class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.dashboard') }}">
                    <img src="{{ asset('images/BBSClogo.png') }}" alt="BBSC Logo" style="height:36px;width:auto;">
                </a>
                <span class="font-bold text-lg hidden md:block">BBSC Admin</span>
            </div>
            <div class="flex items-center space-x-1">
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">Overview</a>
                <a href="{{ route('admin.sessions.index') }}" class="px-3 py-2 rounded text-sm {{ request()->routeIs('admin.sessions.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">Sessions</a>
                <a href="{{ route('admin.training-plans.index') }}" class="px-3 py-2 rounded text-sm {{ request()->routeIs('admin.training-plans.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">Plans</a>
                <a href="{{ route('admin.trainers.index') }}" class="px-3 py-2 rounded text-sm {{ request()->routeIs('admin.trainers.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">Trainers</a>
                <a href="{{ route('admin.email.index') }}" class="px-3 py-2 rounded text-sm {{ request()->routeIs('admin.email.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">Email</a>
                <a href="{{ route('admin.notifications.index') }}" class="px-3 py-2 rounded text-sm {{ request()->routeIs('admin.notifications.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">SMS Logs</a>
                <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 rounded text-sm {{ request()->routeIs('admin.reports.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">Payroll</a>
                <a href="{{ route('admin.admins.index') }}" class="px-3 py-2 rounded text-sm {{ request()->routeIs('admin.admins.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">Settings</a>
                <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded text-sm text-orange-300 hover:bg-gray-700">← Trainer View</a>
            </div>
        </div>
    </div>
</nav>

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
