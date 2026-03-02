@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Security Settings</h1>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Change Password -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Change Password</h2>
                
                <form action="{{ route('fx-provider.update-password') }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password *</label>
                            <input type="password" name="current_password" id="current_password" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">New Password *</label>
                            <input type="password" name="password" id="password" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Password must be at least 8 characters long.</p>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password *</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Update Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Account Information -->
            <div class="border-t pt-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Account Information</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Email Address</p>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $user->email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $user->email_verified_at ? 'Verified' : 'Not Verified' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Account Status</p>
                            <p class="text-sm text-gray-500">Your account is currently {{ $user->is_active ? 'active' : 'inactive' }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Last Login</p>
                            <p class="text-sm text-gray-500">{{ $user->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back to Profile -->
            <div class="mt-6 flex justify-end">
                <a href="{{ route('fx-provider.profile') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Back to Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
