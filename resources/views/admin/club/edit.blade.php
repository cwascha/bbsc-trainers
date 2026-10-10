@extends('layouts.admin')
@section('content')

<div class="max-w-2xl space-y-6">

    @if(session('welcome'))
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
        <h2 class="text-base font-semibold text-blue-800 mb-1">Welcome to TrainerSync! 🎉</h2>
        <p class="text-sm text-blue-700">
            Start by uploading your club logo and setting your colors below. This gives your team a branded experience.
            Once saved, you'll be taken to your dashboard.
        </p>
        <form method="POST" action="{{ route('admin.club.welcome-dismiss') }}" class="mt-3 inline">
            @csrf
            <button class="text-xs text-blue-500 underline">Skip for now</button>
        </form>
    </div>
    @endif

    <h1 class="text-2xl font-bold text-gray-800">Club Branding</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.club.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PATCH')

            {{-- Club name --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Club Name</label>
                <input type="text" name="name" value="{{ old('name', $club->name) }}" required
                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Logo --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Logo</label>
                @if($club->logo_path)
                    <div class="mb-3 flex items-center gap-4">
                        <img src="{{ $club->logo_path }}"
                             alt="Current logo" class="h-16 w-auto rounded border border-gray-200 bg-gray-50 p-1">
                        <span class="text-sm text-gray-400">Current logo</span>
                    </div>
                @endif
                <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">PNG, JPG, SVG or WebP · max 2 MB · recommended: square, transparent background</p>
                @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Colors --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Primary Color
                        <span class="font-normal text-gray-400 ml-1">(navbar background)</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color" id="primary_color"
                               value="{{ old('primary_color', $club->primary_color) }}"
                               class="h-10 w-16 rounded border border-gray-300 cursor-pointer p-0.5">
                        <input type="text" id="primary_color_hex"
                               value="{{ old('primary_color', $club->primary_color) }}"
                               class="w-28 rounded-lg border-gray-300 text-sm font-mono"
                               placeholder="#1e3a5f" pattern="^#[0-9a-fA-F]{6}$">
                    </div>
                    @error('primary_color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Accent Color
                        <span class="font-normal text-gray-400 ml-1">(buttons &amp; highlights)</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="accent_color" id="accent_color"
                               value="{{ old('accent_color', $club->accent_color) }}"
                               class="h-10 w-16 rounded border border-gray-300 cursor-pointer p-0.5">
                        <input type="text" id="accent_color_hex"
                               value="{{ old('accent_color', $club->accent_color) }}"
                               class="w-28 rounded-lg border-gray-300 text-sm font-mono"
                               placeholder="#3b82f6" pattern="^#[0-9a-fA-F]{6}$">
                    </div>
                    @error('accent_color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Live preview --}}
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">Navbar Preview</p>
                <div id="preview-nav" class="rounded-lg px-4 h-14 flex items-center gap-3 transition-colors"
                     style="background-color: {{ $club->primary_color }};">
                    <div class="w-8 h-8 rounded bg-white/20 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($club->name, 0, 2)) }}
                    </div>
                    <span id="preview-name" class="text-white font-bold text-sm">{{ $club->name }} Admin</span>
                    <div class="ml-auto flex gap-2">
                        <span class="text-white/60 text-xs px-2 py-1 rounded hover:bg-white/10 cursor-default">Sessions</span>
                        <span class="text-white/60 text-xs px-2 py-1 rounded hover:bg-white/10 cursor-default">Trainers</span>
                        <span class="text-white/60 text-xs px-2 py-1 rounded hover:bg-white/10 cursor-default">Payroll</span>
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t flex justify-end">
            {{-- Roster SMS recipients --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Roster SMS Recipients</label>
                <input type="text" name="roster_notify_phones"
                       value="{{ old('roster_notify_phones', $club->roster_notify_phones) }}"
                       placeholder="7342768619, 2488021988"
                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-400">
                    Comma-separated phone numbers (digits only) that receive the Friday roster SMS.
                    Leave blank to disable automatic roster texts.
                </p>
                @error('roster_notify_phones')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

                <button type="submit" class="px-6 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition">
                    Save Branding
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function syncColor(pickerId, hexId, previewProp) {
    const picker = document.getElementById(pickerId);
    const hex    = document.getElementById(hexId);
    const nav    = document.getElementById('preview-nav');

    picker.addEventListener('input', () => {
        hex.value = picker.value;
        nav.style[previewProp] = picker.value;
        document.documentElement.style.setProperty(
            previewProp === 'backgroundColor' ? '--club-primary' : '--club-accent', picker.value
        );
    });
    hex.addEventListener('input', () => {
        if (/^#[0-9a-fA-F]{6}$/.test(hex.value)) {
            picker.value = hex.value;
            nav.style[previewProp] = hex.value;
        }
    });
}

syncColor('primary_color', 'primary_color_hex', 'backgroundColor');
syncColor('accent_color',  'accent_color_hex',  'accentColor');

// Keep name preview in sync
document.querySelector('[name="name"]')?.addEventListener('input', function() {
    const preview = document.getElementById('preview-name');
    if (preview) preview.textContent = (this.value || 'Club') + ' Admin';
});
</script>

@endsection
