<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Get Started — Pitchside</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-4xl mx-auto px-4 py-16">

    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-3">Simple, transparent pricing</h1>
        <p class="text-lg text-gray-500">Start with a {{ config('services.stripe.trial_days', 14) }}-day free trial. No credit card charged until your trial ends.</p>
    </div>

    {{-- Pricing card --}}
    <div class="max-w-md mx-auto bg-white rounded-2xl shadow-lg overflow-hidden mb-12">
        <div class="bg-gray-900 px-8 py-6 text-center">
            <p class="text-blue-400 font-semibold text-sm uppercase tracking-wide mb-2">Pitchside Pro</p>
            <div class="flex items-end justify-center gap-1">
                <span class="text-5xl font-bold text-white">$99</span>
                <span class="text-gray-400 mb-2">/ month</span>
            </div>
            <p class="text-gray-400 text-sm mt-1">per club · unlimited trainers</p>
        </div>
        <div class="px-8 py-6">
            <ul class="space-y-3 text-sm text-gray-700 mb-8">
                @foreach([
                    'Unlimited trainers & sessions',
                    'Automated weekly assignment SMS',
                    'Trainer availability portal',
                    'Payroll tracking & CSV export',
                    'Training plan distribution',
                    'White-label branding (logo + colors)',
                    'Team roster management',
                    'Bulk & individual SMS',
                    'SMS confirmation & cancellation',
                    'Season & training day management',
                ] as $feature)
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>

            {{-- Signup form --}}
            <form method="POST" action="{{ route('signup.checkout') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Club Name</label>
                    <input type="text" name="club_name" value="{{ old('club_name') }}" required
                           placeholder="e.g. Valley United SC"
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('club_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Your Name</label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                           placeholder="Jane Smith"
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('admin_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Work Email</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                           placeholder="jane@valleyunited.com"
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('admin_email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition text-sm">
                    Start Free Trial →
                </button>
                <p class="text-xs text-center text-gray-400">
                    You'll be taken to Stripe to enter payment details.<br>
                    Your card won't be charged for {{ config('services.stripe.trial_days', 14) }} days.
                </p>
            </form>
        </div>
    </div>

    {{-- Testimonial --}}
    <blockquote class="max-w-xl mx-auto text-center">
        <p class="text-gray-600 italic text-base mb-3">
            "We used to manage 60+ trainers across weekend sessions with group texts and a spreadsheet.
            Now the whole process — sign-ups, assignments, confirmations, payroll — runs itself."
        </p>
        <cite class="text-sm text-gray-400 not-italic">— Nico Francia, Director of Coaching, BBSC</cite>
    </blockquote>
</div>

</body>
</html>
