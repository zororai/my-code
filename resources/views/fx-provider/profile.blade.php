@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">My Profile</h1>

            <div class="space-y-6">
                <!-- User Information -->
                <div class="border-b pb-4">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">User Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Name</label>
                            <p class="mt-1 text-gray-900">{{ $user->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Email</label>
                            <p class="mt-1 text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Phone</label>
                            <p class="mt-1 text-gray-900">{{ $user->phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Account Status</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Provider Information -->
                @if($fxProvider)
                <div class="border-b pb-4">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Provider Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Provider Name</label>
                            <p class="mt-1 text-gray-900">{{ $fxProvider->provider_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Contact Email</label>
                            <p class="mt-1 text-gray-900">{{ $fxProvider->contact_email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Contact Phone</label>
                            <p class="mt-1 text-gray-900">{{ $fxProvider->contact_phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Verification Status</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $fxProvider->is_verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $fxProvider->is_verified ? 'Verified' : 'Pending Verification' }}
                                </span>
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600">Address</label>
                            <p class="mt-1 text-gray-900">
                                {{ $fxProvider->address ?? 'Not provided' }}
                                @if($fxProvider->city || $fxProvider->country)
                                    <br>{{ $fxProvider->city }}{{ $fxProvider->city && $fxProvider->country ? ', ' : '' }}{{ $fxProvider->country }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Rating</label>
                            <p class="mt-1 text-gray-900">{{ number_format($fxProvider->rating, 2) }} / 5.00</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Total Transactions</label>
                            <p class="mt-1 text-gray-900">{{ number_format($fxProvider->total_transactions) }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('fx-provider.edit-account') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Edit Profile
                    </a>
                    <a href="{{ route('fx-provider.security') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        Security Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
