@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Connect New Trading Account</h1>
            <p class="text-gray-600">Link accounts from brokers, exchanges, and investment platforms worldwide</p>
        </div>

        <!-- Account Type Selection -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Stock Brokers -->
            <a href="{{ route('trading-accounts.select-broker', ['type' => 'stock_broker']) }}" class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-all transform hover:scale-105 border-2 border-transparent hover:border-blue-500">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Stock Brokers</h3>
                    <p class="text-gray-600 text-sm">Connect to stock trading platforms and brokerage accounts</p>
                </div>
            </a>

            <!-- Crypto Exchanges -->
            <a href="{{ route('trading-accounts.select-broker', ['type' => 'crypto_exchange']) }}" class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-all transform hover:scale-105 border-2 border-transparent hover:border-yellow-500">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Crypto Exchanges</h3>
                    <p class="text-gray-600 text-sm">Link cryptocurrency exchange accounts and digital wallets</p>
                </div>
            </a>

            <!-- Investment Platforms -->
            <a href="{{ route('trading-accounts.select-broker', ['type' => 'investment_platform']) }}" class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition-all transform hover:scale-105 border-2 border-transparent hover:border-green-500">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Investment Platforms</h3>
                    <p class="text-gray-600 text-sm">Connect to robo-advisors and investment management platforms</p>
                </div>
            </a>

        </div>

        <!-- Info Section -->
        <div class="mt-12 bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
            <div class="flex">
                <svg class="w-6 h-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h4 class="text-lg font-semibold text-blue-900 mb-2">Secure Connection</h4>
                    <p class="text-blue-800 text-sm">Your account credentials are encrypted and never stored on our servers. We use industry-standard security protocols to protect your data.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
