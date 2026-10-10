<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin — TrainerSync</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen font-sans antialiased">

<nav class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="text-xs font-bold bg-red-600 text-white px-2 py-0.5 rounded uppercase tracking-wide">Super Admin</span>
        <span class="font-bold">TrainerSync</span>
    </div>
    <div class="flex items-center gap-4 text-sm">
        <span class="text-gray-400">{{ Auth::user()->name }}</span>
        @if(session('impersonating_club_id'))
            <span class="bg-yellow-500 text-black text-xs font-bold px-2 py-0.5 rounded">Impersonating</span>
            <form method="POST" action="{{ route('superadmin.stop-impersonating') }}" class="inline">
                @csrf
                <button class="text-yellow-400 hover:text-white">Stop impersonating</button>
            </form>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-gray-400 hover:text-white">Log out</button>
        </form>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

    <h1 class="text-2xl font-bold text-gray-800">All Clubs</h1>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['Total Clubs', $stats['total_clubs']],
            ['Active', $stats['active_clubs']],
            ['Trial', $stats['trial_clubs']],
            ['Total Trainers', $stats['total_trainers']],
        ] as [$label, $val])
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $val }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Clubs table --}}
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Club</th>
                    <th class="px-4 py-3 text-left">Slug</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Trial Ends</th>
                    <th class="px-4 py-3 text-left">Trainers</th>
                    <th class="px-4 py-3 text-left">Sessions</th>
                    <th class="px-4 py-3 text-left">Joined</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($clubs as $club)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $club->name }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $club->slug }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            @if($club->subscription_status === 'active') bg-green-100 text-green-700
                            @elseif($club->subscription_status === 'trial') bg-blue-100 text-blue-700
                            @elseif($club->subscription_status === 'past_due') bg-yellow-100 text-yellow-700
                            @else bg-gray-100 text-gray-500
                            @endif">
                            {{ ucfirst($club->subscription_status ?? 'unknown') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $club->trial_ends_at?->format('M j, Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $club->users_count }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $club->training_days_count }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $club->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            {{-- Impersonate --}}
                            <form method="POST" action="{{ route('superadmin.clubs.impersonate', $club) }}">
                                @csrf
                                <button class="text-xs text-blue-600 hover:underline">View</button>
                            </form>
                            {{-- Status toggle --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="text-xs text-gray-500 hover:text-gray-700 underline">
                                    Status ▾
                                </button>
                                <div x-show="open" @click.away="open = false"
                                     class="absolute right-0 mt-1 bg-white border border-gray-200 rounded shadow-lg z-10 py-1 min-w-28">
                                    @foreach(['active','trial','inactive','cancelled'] as $s)
                                    <form method="POST"
                                          action="{{ route('superadmin.clubs.status', [$club, $s]) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="w-full text-left px-4 py-1.5 text-xs hover:bg-gray-50
                                                    {{ $club->subscription_status === $s ? 'font-bold text-gray-900' : 'text-gray-600' }}">
                                            {{ ucfirst($s) }}
                                        </button>
                                    </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No clubs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
