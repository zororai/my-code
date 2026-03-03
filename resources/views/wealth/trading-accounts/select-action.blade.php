@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Connect Your Account</h1>
            <p class="text-gray-600">Choose how you'd like to connect with {{ $broker_name }}</p>
        </div>

        <!-- Selected Broker Info -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-sm font-semibold text-gray-500 mb-2">Selected Broker</h3>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $broker_name }}</h2>
                    <p class="text-gray-600">{{ $country }} • {{ ucfirst(str_replace('_', ' ', $asset_type)) }}</p>
                </div>
            </div>
        </div>

        <!-- Action Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Create New Account -->
            <form action="{{ route('trading-accounts.create-with-broker') }}" method="POST" class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-all border-2 border-transparent hover:border-green-500">
                @csrf
                <input type="hidden" name="account_type" value="{{ $account_type }}">
                <input type="hidden" name="country" value="{{ $country }}">
                <input type="hidden" name="asset_type" value="{{ $asset_type }}">
                <input type="hidden" name="financial_market" value="{{ $financial_market }}">
                <input type="hidden" name="broker_name" value="{{ $broker_name }}">
                <input type="hidden" name="broker_code" value="{{ $broker_code }}">

                <div class="flex flex-col items-center text-center mb-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Create New Account</h3>
                    <p class="text-gray-600 text-sm mb-6">Register a new account with {{ $broker_name }}. You'll be redirected to their platform for registration and compliance.</p>
                </div>

                <ul class="space-y-2 mb-6 text-left">
                    <li class="flex items-start text-sm text-gray-700">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Complete broker registration
                    </li>
                    <li class="flex items-start text-sm text-gray-700">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Verify your identity (KYC)
                    </li>
                    <li class="flex items-start text-sm text-gray-700">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Auto-sync after approval
                    </li>
                </ul>

                <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    Create New Account
                </button>
            </form>

            <!-- Link Existing Account -->
            <form action="{{ route('trading-accounts.link-existing-form') }}" method="POST" class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-all border-2 border-transparent hover:border-blue-500">
                @csrf
                <input type="hidden" name="account_type" value="{{ $account_type }}">
                <input type="hidden" name="country" value="{{ $country }}">
                <input type="hidden" name="asset_type" value="{{ $asset_type }}">
                <input type="hidden" name="financial_market" value="{{ $financial_market }}">
                <input type="hidden" name="broker_name" value="{{ $broker_name }}">
                <input type="hidden" name="broker_code" value="{{ $broker_code }}">

                <div class="flex flex-col items-center text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Link Existing Account</h3>
                    <p class="text-gray-600 text-sm mb-6">Already have an account with {{ $broker_name }}? Connect it to sync your portfolio data.</p>
                </div>

                <ul class="space-y-2 mb-6 text-left">
                    <li class="flex items-start text-sm text-gray-700">
                        <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enter account details
                    </li>
                    <li class="flex items-start text-sm text-gray-700">
                        <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Accept terms & consent
                    </li>
                    <li class="flex items-start text-sm text-gray-700">
                        <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Instant portfolio sync
                    </li>
                </ul>

                <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Link Existing Account
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
