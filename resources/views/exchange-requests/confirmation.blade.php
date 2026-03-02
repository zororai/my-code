@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-600 to-blue-600 py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between text-white">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('exchange-requests.index') }}" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold">Exchange Market</h1>
                        <h2 class="text-2xl font-semibold">Transaction Confirmation</h2>
                        <p class="text-sm text-purple-100 mt-1">Review and confirm your currency exchange with premium global provider</p>
                    </div>
                </div>
                <div class="px-4 py-2 bg-white/20 rounded-lg backdrop-blur-sm">
                    <span class="text-sm font-semibold">Global Exchange</span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Exchange Provider Details -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex items-center mb-6">
                    <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-900">Exchange Provider Details</h3>
                </div>

                <!-- Provider Info -->
                <div class="mb-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-purple-600 rounded-xl flex items-center justify-center">
                            <span class="text-2xl font-bold text-white">{{ substr($exchangeRequest->fxProvider->provider_name ?? 'XE', 0, 1) }}</span>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-bold text-gray-900">{{ $exchangeRequest->fxProvider->provider_name ?? 'XE Currency' }}</h4>
                            <div class="flex items-center mt-1">
                                <svg class="w-4 h-4 text-blue-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-sm text-gray-600">US</span>
                                <span class="ml-3 flex items-center">
                                    <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-700">4.8</span>
                                    <span class="text-xs text-gray-500 ml-1">Verified</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Processing Details -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Processing Time</p>
                        <p class="text-lg font-bold text-gray-900">5 min</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Security Level</p>
                        <p class="text-lg font-bold text-gray-900">Enterprise</p>
                    </div>
                </div>

                <!-- Licenses -->
                <div class="mb-6">
                    <p class="text-xs text-gray-500 mb-2">Licenses & Certifications</p>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">SARB</span>
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">FSCA</span>
                    </div>
                </div>

                <!-- Provider Features -->
                <div>
                    <p class="text-xs text-gray-500 mb-2">Provider Features</p>
                    <div class="space-y-2">
                        <div class="px-3 py-2 bg-green-50 text-green-700 text-sm font-medium rounded-lg">Mobile Integration</div>
                        <div class="px-3 py-2 bg-green-50 text-green-700 text-sm font-medium rounded-lg">Real-time Rates</div>
                        <div class="px-3 py-2 bg-green-50 text-green-700 text-sm font-medium rounded-lg">Local Expertise</div>
                    </div>
                </div>
            </div>

            <!-- Transaction Summary -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex items-center mb-6">
                    <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-900">Transaction Summary</h3>
                </div>

                <!-- Exchange Amount -->
                <div class="mb-6 p-6 bg-gradient-to-br from-green-50 to-blue-50 rounded-xl">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">You Send</p>
                            <p class="text-4xl font-bold text-green-600">{{ number_format($exchangeRequest->source_amount, 2) }}</p>
                            <p class="text-2xl font-bold text-green-600">{{ $exchangeRequest->source_currency }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600 mb-1">You Receive</p>
                            <p class="text-4xl font-bold text-blue-600">{{ number_format($exchangeRequest->destination_amount ?? $exchangeRequest->source_amount * 0.85, 2) }}</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $exchangeRequest->destination_currency }}</p>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">Exchange Rate</p>
                        <p class="text-lg font-bold text-gray-900">1 {{ $exchangeRequest->source_currency }} = {{ number_format($exchangeRequest->exchange_rate ?? 0.85, 2) }} {{ $exchangeRequest->destination_currency }}</p>
                    </div>
                </div>

                <!-- Fee Breakdown -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Fee Breakdown</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">PANETA Processing Fee (0.99%)</span>
                            <span class="font-semibold text-gray-900">{{ number_format($exchangeRequest->processing_fee ?? 9.90, 2) }} {{ $exchangeRequest->source_currency }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Provider Fee (0.20%)</span>
                            <span class="font-semibold text-gray-900">{{ number_format($exchangeRequest->provider_fee ?? 2.00, 2) }} {{ $exchangeRequest->source_currency }}</span>
                        </div>
                        <div class="pt-2 border-t border-gray-200 flex justify-between">
                            <span class="font-semibold text-gray-700">Total Fees</span>
                            <span class="font-bold text-red-600">{{ number_format($exchangeRequest->total_fees ?? 11.90, 2) }} {{ $exchangeRequest->source_currency }}</span>
                        </div>
                    </div>
                </div>

                <!-- Net Amount -->
                <div class="mb-6 p-4 bg-purple-50 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-700">Net Amount</span>
                        <span class="text-2xl font-bold text-purple-600">{{ number_format(($exchangeRequest->source_amount ?? 0) - ($exchangeRequest->total_fees ?? 11.90), 2) }} {{ $exchangeRequest->source_currency }}</span>
                    </div>
                </div>

                <!-- User Payment Status -->
                @if($exchangeRequest->status === 'accepted')
                    <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg">
                        <p class="text-sm font-semibold text-yellow-800">Waiting for user payment confirmation</p>
                        <p class="text-xs text-yellow-700 mt-1">The user needs to confirm their payment before you can proceed.</p>
                    </div>
                @elseif($exchangeRequest->status === 'user_payment_confirmed')
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                        <p class="text-sm font-semibold text-green-800">✓ User has confirmed payment</p>
                        <p class="text-xs text-green-700 mt-1">Please complete the exchange by confirming your payment below.</p>
                    </div>
                @endif

                <!-- Payment Confirmation Form (Only shown when user has confirmed) -->
                @if($exchangeRequest->status === 'user_payment_confirmed')
                    <form action="{{ route('exchange-requests.provider-confirm-payment', $exchangeRequest->id) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <!-- Currency and Amount (Auto-filled) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Currency and Amount to be Paid to Counterparty</label>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($exchangeRequest->destination_amount ?? 0, 2) }} {{ $exchangeRequest->destination_currency }}</p>
                            </div>
                        </div>

                        <!-- Account Holder Name (Auto-filled) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Name of Account Holder</label>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <p class="text-lg font-semibold text-gray-900">{{ $exchangeRequest->user->name }}</p>
                                <p class="text-sm text-gray-600">{{ $exchangeRequest->user->email }}</p>
                            </div>
                        </div>

                        <!-- Destination Account (Auto-filled) -->
                        @if($exchangeRequest->user_destination_account)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Destination Account of Counterparty User</label>
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <p class="text-sm font-semibold text-gray-900">{{ $exchangeRequest->user_destination_account }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Source Account Selection (Provider selects) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Your Source Account (Acquirer)</label>
                            <select name="provider_source_account" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Select an account...</option>
                                @foreach($providerAccounts as $account)
                                    <option value="{{ $account }}">{{ $account }}</option>
                                @endforeach
                            </select>
                            @error('provider_source_account')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Payment Button -->
                        <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-bold text-lg rounded-lg hover:from-purple-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transform transition-all hover:scale-105">
                            Confirm Payment & Complete Exchange
                        </button>
                    </form>
                @endif

                <!-- Transaction Reference -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500">Transaction Reference</p>
                    <p class="text-sm font-mono font-semibold text-gray-900">{{ $exchangeRequest->transaction_reference }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
