@extends('layouts.admin')
@section('content')

<div class="max-w-3xl space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">Getting Started</h1>
        <p class="mt-1 text-gray-500">Follow these steps to get your TrainerSync environment fully set up.</p>
    </div>

    @php
    $steps = [
        [
            'number' => 1,
            'title'  => 'Complete Your Club Branding',
            'route'  => route('admin.club.edit'),
            'label'  => 'Go to Branding',
            'icon'   => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            'tasks'  => [
                'Upload your club logo (PNG, JPG, or SVG — square with transparent background works best)',
                'Set your primary color (used for the navbar background)',
                'Set your accent color (used for buttons and highlights)',
                'Confirm your club name is correct',
            ],
            'tip' => 'Your trainers and parents will see this branding on every page they visit.',
        ],
        [
            'number' => 2,
            'title'  => 'Configure Club Settings',
            'route'  => route('admin.admins.index'),
            'label'  => 'Go to Club Settings',
            'icon'   => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'tasks'  => [
                'Enter your contact email (used as the sender on trainer emails)',
                'Set your club timezone (determines when the Friday roster SMS is sent)',
                'Add roster SMS recipient phone numbers — these receive the weekly trainer lineup',
            ],
            'tip' => 'The roster SMS goes out every Friday at 6 PM in your club\'s timezone.',
        ],
        [
            'number' => 3,
            'title'  => 'Create Your First Season',
            'route'  => route('admin.seasons.index'),
            'label'  => 'Go to Seasons',
            'icon'   => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'tasks'  => [
                'Click "New Season" and give it a name (e.g. "Fall 2026")',
                'Add your training days — set the date, session start/end times, program name, and max trainer spots',
                'Use "Generate from Template" if you have a recurring weekly schedule to save time',
                'Double-check dates and spot counts before inviting trainers',
            ],
            'tip' => 'Each training day is a separate session. Trainers mark availability per day.',
        ],
        [
            'number' => 4,
            'title'  => 'Invite Your Trainers',
            'route'  => null,
            'label'  => null,
            'icon'   => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            'tasks'  => [
                'Share your registration link with trainers: <code class="bg-gray-100 px-1 rounded text-xs font-mono">{{ $clubUrl }}/register</code>',
                'Trainers create their own account — they set their name, phone number, email, and password',
                'Their phone number is used to receive SMS assignment and confirmation messages',
                'You can also add admin users in Club Settings if other staff need admin access',
            ],
            'tip' => 'Trainers only see their own assignments and training plans — not payroll or admin data.',
        ],
        [
            'number' => 5,
            'title'  => 'Upload Training Plans',
            'route'  => route('admin.training-plans.index'),
            'label'  => 'Go to Plans',
            'icon'   => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'tasks'  => [
                'Upload a PDF training plan for each weekend and program',
                'Plans are sent to trainers automatically via SMS link when they are assigned',
                'Trainers can also view their plans any time from their dashboard',
            ],
            'tip' => 'You can upload plans before or after trainers are assigned — they\'ll always see the latest version.',
        ],
        [
            'number' => 6,
            'title'  => 'Configure the Teams Page (optional)',
            'route'  => route('admin.admins.index') . '#teams-page',
            'label'  => 'Go to Club Settings',
            'icon'   => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            'tasks'  => [
                'Add your field/venue name and map search query for the embedded map',
                'Add links to league rules and your season schedule',
                'Add jersey/kit purchase info and your head trainer\'s name',
                'Import player rosters via the Teams → Import Rosters page (Excel .xlsx format)',
            ],
            'tip' => 'The public teams page is at <code class="bg-gray-100 px-1 rounded text-xs font-mono">{{ $clubUrl }}/teams</code> — share it with parents.',
        ],
        [
            'number' => 7,
            'title'  => 'Run a Test Weekend',
            'route'  => route('admin.sessions.index'),
            'label'  => 'Go to Sessions',
            'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'tasks'  => [
                'Have one or two trainers mark themselves available for an upcoming weekend',
                'Go to Sessions and manually trigger assignments, or wait for the Thursday auto-assignment',
                'Trainers receive an SMS — reply YES to confirm or NO to cancel',
                'On Friday, the roster SMS is sent to your configured recipient phones',
                'After the session, use Sessions to mark attendance and generate payroll in Payroll',
            ],
            'tip' => 'You can trigger assignments manually any time from the Sessions page — you don\'t have to wait for Thursday.',
        ],
    ];
    @endphp

    @foreach($steps as $step)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-start gap-4 p-6">

            {{-- Step number --}}
            <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white"
                 style="background-color: var(--club-primary);">
                {{ $step['number'] }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <h2 class="text-base font-semibold text-gray-800">{{ $step['title'] }}</h2>
                    @if($step['route'])
                    <a href="{{ $step['route'] }}"
                       class="flex-shrink-0 text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition whitespace-nowrap">
                        {{ $step['label'] }} →
                    </a>
                    @endif
                </div>

                <ul class="mt-3 space-y-2">
                    @foreach($step['tasks'] as $task)
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-300 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{!! $task !!}</span>
                    </li>
                    @endforeach
                </ul>

                @if(!empty($step['tip']))
                <div class="mt-4 flex items-start gap-2 bg-blue-50 rounded-lg px-3 py-2.5">
                    <svg class="w-4 h-4 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-blue-700">{!! $step['tip'] !!}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach

    <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
        <svg class="w-8 h-8 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="font-semibold text-green-800 mb-1">You're all set!</h3>
        <p class="text-sm text-green-700">Once you've completed these steps your club is fully operational. Head to your <a href="{{ route('admin.dashboard') }}" class="underline font-medium">dashboard</a> to manage your season.</p>
    </div>

</div>
@endsection
