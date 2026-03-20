@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Admin Users</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Add Admin Form --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-800 mb-1">Add Admin User</h2>
            <p class="text-sm text-gray-500 mb-4">
                The new admin will receive an email invitation with a link to set their password.
            </p>
            <form method="POST" action="{{ route('admin.admins.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="block w-full rounded border-gray-300 text-sm focus:ring-gray-500 focus:border-gray-500"
                           placeholder="Jane Smith">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="block w-full rounded border-gray-300 text-sm focus:ring-gray-500 focus:border-gray-500"
                           placeholder="jane@bbscsoccer.com">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="pt-1">
                    <button type="submit"
                            class="w-full px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">
                        Send Invitation
                    </button>
                </div>
            </form>
        </div>

        {{-- Info Panel --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-sm text-blue-800 space-y-2">
            <p class="font-semibold">About Admin Accounts</p>
            <ul class="list-disc list-inside space-y-1 text-blue-700">
                <li>Admin users have full access to the admin panel</li>
                <li>They can manage trainers, sessions, training plans, and reports</li>
                <li>The invitation link expires after 60 minutes</li>
                <li>If they miss it, they can use "Forgot Password" on the login page</li>
            </ul>
        </div>

    </div>

    {{-- Admin Users Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">Current Admins</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Added</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($admins as $admin)
                <tr>
                    <td class="px-6 py-3 font-medium text-gray-800">
                        {{ $admin->name }}
                        @if($admin->id === auth()->id())
                            <span class="ml-1 text-xs text-gray-400">(you)</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ $admin->email }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $admin->created_at->format('M j, Y') }}</td>
                    <td class="px-6 py-3">
                        @if($admin->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}"
                                  onsubmit="return confirm('Remove {{ addslashes($admin->name) }} as an admin?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Remove</button>
                            </form>
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
