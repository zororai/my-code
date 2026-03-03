@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Link Existing Account</h1>
            <p class="text-gray-600">Connect your {{ $broker_name }} account</p>
        </div>

        <!-- Form -->
        <form action="{{ route('trading-accounts.link-existing') }}" method="POST" class="bg-white rounded-xl shadow-lg p-8">
            @csrf
            <input type="hidden" name="account_type" value="{{ $account_type }}">
            <input type="hidden" name="country" value="{{ $country }}">
            <input type="hidden" name="asset_type" value="{{ $asset_type }}">
            <input type="hidden" name="financial_market" value="{{ $financial_market }}">
            <input type="hidden" name="broker_name" value="{{ $broker_name }}">
            <input type="hidden" name="broker_code" value="{{ $broker_code }}">

            <!-- Broker Info -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-semibold text-gray-900 mb-1">{{ $broker_name }}</h3>
                <p class="text-sm text-gray-600">{{ $country }} • {{ ucfirst(str_replace('_', ' ', $asset_type)) }}</p>
            </div>

            <!-- Account Holder Name -->
            <div class="mb-6">
                <label for="account_holder_name" class="block text-sm font-semibold text-gray-700 mb-2">
                    1. Account Holder Name *
                </label>
                <input 
                    type="text" 
                    name="account_holder_name" 
                    id="account_holder_name" 
                    required
                    placeholder="Enter registered trading name"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('account_holder_name') border-red-500 @enderror"
                    value="{{ old('account_holder_name') }}"
                >
                @error('account_holder_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Enter your name as registered with {{ $broker_name }}</p>
            </div>

            <!-- Trading Account Number -->
            <div class="mb-6">
                <label for="trading_account_number" class="block text-sm font-semibold text-gray-700 mb-2">
                    2. Trading (Investment) Account Number *
                </label>
                <input 
                    type="text" 
                    name="trading_account_number" 
                    id="trading_account_number" 
                    required
                    placeholder="Enter account number"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('trading_account_number') border-red-500 @enderror"
                    value="{{ old('trading_account_number') }}"
                >
                @error('trading_account_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Your unique account number with {{ $broker_name }}</p>
            </div>

            <!-- Terms and Consent -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">3. Terms & Consent</h3>
                
                <div class="p-4 bg-gray-50 rounded-lg mb-4 max-h-60 overflow-y-auto">
                    <h4 class="font-semibold text-gray-900 mb-2">Data Access Agreement</h4>
                    <div class="text-sm text-gray-700 space-y-2">
                        <p>By connecting your {{ $broker_name }} account, you authorize PANĒTA Wealth & Investments to:</p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            <li>Access your account information and portfolio holdings</li>
                            <li>Retrieve transaction history and performance data</li>
                            <li>Display your investment data on the Portfolio Management dashboard</li>
                            <li>Sync data periodically to keep your portfolio up-to-date</li>
                        </ul>
                        <p class="mt-3"><strong>Security & Privacy:</strong></p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            <li>Your credentials are encrypted using bank-level security</li>
                            <li>We never store your login password</li>
                            <li>You can disconnect your account at any time</li>
                            <li>Your data is protected under our Privacy Policy</li>
                        </ul>
                        <p class="mt-3"><strong>Data Usage:</strong></p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            <li>Portfolio aggregation and analysis</li>
                            <li>Performance tracking and reporting</li>
                            <li>Investment insights and recommendations</li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-start">
                    <input 
                        type="checkbox" 
                        name="terms_accepted" 
                        id="terms_accepted" 
                        required
                        class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 @error('terms_accepted') border-red-500 @enderror"
                    >
                    <label for="terms_accepted" class="ml-3 text-sm text-gray-700">
                        I have read and agree to the Data Access Agreement and authorize PANĒTA to access my {{ $broker_name }} account data. *
                    </label>
                </div>
                @error('terms_accepted')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Security Notice -->
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-green-900">Secure Connection</h4>
                        <p class="text-xs text-green-800 mt-1">Your account information is encrypted and protected. We use industry-standard security protocols.</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4">
                <a href="{{ route('trading-accounts.select-type') }}" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 text-center font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Connect Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
