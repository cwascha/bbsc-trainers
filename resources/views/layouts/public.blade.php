<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', ($currentClub->name ?? config('app.name')) . ' Teams')</title>
    @php $publicLogo = $currentClub?->logo_path ?: asset('images/BBSClogo.png'); @endphp
    <link rel="icon" type="image/png" href="{{ $publicLogo }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <header class="bg-gray-900 text-white shadow">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
            <img src="{{ $publicLogo }}"
                 alt="{{ $currentClub->name ?? 'Logo' }}" width="32" height="32" class="h-8 w-8 object-contain" style="max-width:32px;max-height:32px;">
            <a href="{{ route('teams.public.index') }}" class="text-lg font-bold tracking-wide hover:text-gray-200">
                {{ $currentClub->name ?? config('app.name') }} — Teams
            </a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-gray-200 py-6 text-center text-sm text-gray-400">
        &copy; {{ date('Y') }} {{ $currentClub->name ?? config('app.name') }}
    </footer>

</body>
</html>
