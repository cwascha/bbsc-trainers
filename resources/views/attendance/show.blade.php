<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekend {{ $weekend }} Attendance — {{ $currentClub->name ?? config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-lg mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        @php $attendanceLogo = $currentClub?->logo_path ?: asset('images/BBSClogo.png'); @endphp
        <img src="{{ $attendanceLogo }}" alt="{{ $currentClub->name ?? 'Logo' }}" class="h-10 w-auto">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Weekend {{ $weekend }} Attendance</h1>
            <p class="text-sm text-gray-500">Tap a trainer to toggle their attendance</p>
        </div>
    </div>

    @if(session('message'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    @php $currentDate = null; @endphp

    @foreach($days as $day)

        {{-- Day heading --}}
        @if($day->date->toDateString() !== $currentDate)
            @php $currentDate = $day->date->toDateString(); @endphp
            <div class="mt-6 mb-3 first:mt-0">
                <h2 class="text-base font-semibold text-gray-700 uppercase tracking-wide">
                    {{ $day->date->format('l, F j') }}
                </h2>
            </div>
        @endif

        {{-- Session card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-4 overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <span class="font-semibold text-gray-800">{{ $day->program }}</span>
                    <span class="ml-2 text-sm text-gray-500">{{ $day->session_time_range }}</span>
                </div>
                @php
                    $total   = $day->availabilities->count();
                    $noShows = $day->availabilities->where('status', 'no_show')->count();
                    $present = $total - $noShows;
                @endphp
                <span class="text-sm font-medium text-gray-600">
                    {{ $present }}/{{ $total }} present
                </span>
            </div>

            @if($day->availabilities->isEmpty())
                <p class="px-4 py-4 text-sm text-gray-400 italic">No trainers assigned</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($day->availabilities->sortBy('user.name') as $av)
                        @php $noShow = $av->status === 'no_show'; @endphp
                        <li>
                            <form method="POST"
                                  action="{{ URL::signedRoute('attendance.toggle', ['weekend' => $weekend, 'availability' => $av->id]) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="w-full flex items-center justify-between px-4 py-3 text-left transition
                                               {{ $noShow ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50' }}">
                                    <span class="font-medium {{ $noShow ? 'text-red-600 line-through' : 'text-gray-800' }}">
                                        {{ $av->user->name }}
                                    </span>
                                    @if($noShow)
                                        <span class="flex items-center gap-1 text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            No show
                                        </span>
                                    @else
                                        <span class="flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-100 px-2 py-1 rounded-full">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Present
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    @endforeach

    <p class="mt-8 text-center text-xs text-gray-400">
        TrainerSync &middot; Weekend {{ $weekend }}
    </p>

</div>

</body>
</html>
