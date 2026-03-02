@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">FX Marketplace</h1>
            <p class="text-gray-600 mt-2">Browse and compare foreign exchange offers from verified providers</p>
        </div>
        @auth
            @if(auth()->user()->hasRole(['Zimbabwe Currency Exchange (ZimFX)', 'Global FX Solutions']))
                <a href="{{ route('fx.offers.create') }}" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    + Create New Offer
                </a>
            @endif
        @endauth
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

    @if($offers->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($offers as $offer)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <!-- Provider Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold">{{ $offer->provider_name }}</h3>
                                <p class="text-sm text-blue-100">Verified FX Provider</p>
                            </div>
                            <div class="flex items-center">
                                @if($offer->isWithinTradingHours())
                                    <span class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">OPEN</span>
                                @else
                                    <span class="px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-full">CLOSED</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Offer Details -->
                    <div class="p-6">
                        <!-- Exchange Rates -->
                        <div class="mb-4 grid grid-cols-2 gap-4">
                            <div class="bg-green-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Buy Rate</p>
                                <p class="text-2xl font-bold text-green-600">{{ number_format($offer->buy_rate, 6) }}</p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Sell Rate</p>
                                <p class="text-2xl font-bold text-blue-600">{{ number_format($offer->sell_rate, 6) }}</p>
                            </div>
                        </div>

                        <!-- Trading Hours -->
                        <div class="mb-4 flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Trading Hours: {{ date('H:i', strtotime($offer->open_time)) }} - {{ date('H:i', strtotime($offer->close_time)) }}</span>
                        </div>

                        <!-- Trade Limits -->
                        <div class="mb-4 bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-2">Trade Value Range</p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ number_format($offer->min_trade_value, 2) }} - {{ number_format($offer->max_trade_value, 2) }}
                            </p>
                        </div>

                        <!-- Available Amounts -->
                        <div class="mb-4">
                            <p class="text-xs text-gray-600 mb-2">Available Amounts</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($offer->available_amounts as $amount)
                                    <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">
                                        {{ number_format($amount['amount'], 2) }} {{ $amount['currency'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Trading Currencies -->
                        <div class="mb-4">
                            <p class="text-xs text-gray-600 mb-2">Trading Currencies</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($offer->trading_currencies as $currency)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                        {{ $currency }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Settlement Methods -->
                        <div class="mb-4">
                            <p class="text-xs text-gray-600 mb-2">Settlement Methods</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($offer->settlement_methods as $method)
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                        {{ $method }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Processing Fee -->
                        <div class="mb-4 flex items-center justify-between text-sm">
                            <span class="text-gray-600">Processing Fee</span>
                            <span class="font-semibold text-gray-800">{{ $offer->processing_fee_percentage }}%</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 mt-4">
                            <a href="{{ route('fx.offers.show', $offer->id) }}" class="flex-1 px-4 py-2 bg-blue-600 text-white text-center font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                View Details
                            </a>
                            @auth
                                @if($offer->user_id === auth()->id())
                                    <a href="{{ route('fx.offers.edit', $offer->id) }}" class="px-4 py-2 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                        Edit
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-3 text-xs text-gray-500">
                        Posted {{ $offer->created_at->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $offers->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Offers Available</h3>
            <p class="text-gray-500">There are currently no FX offers in the marketplace.</p>
            @auth
                @if(auth()->user()->hasRole(['Zimbabwe Currency Exchange (ZimFX)', 'Global FX Solutions']))
                    <a href="{{ route('fx.offers.create') }}" class="mt-4 inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">
                        Create Your First Offer
                    </a>
                @endif
            @endauth
        </div>
    @endif
</div>
@endsection
