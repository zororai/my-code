@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Consolidated Accounts Summary</h1>
        <p class="text-gray-600 mt-2">{{ $fxProvider->provider_name }} - Currency Exchange Dashboard</p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Volume -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Today's Volume</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <p class="text-3xl font-bold">${{ number_format($summary['total_volume_today'], 2) }}</p>
            <p class="text-sm opacity-80 mt-1">{{ $summary['total_transactions_today'] }} transactions</p>
        </div>

        <!-- Monthly Volume -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Monthly Volume</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold">${{ number_format($summary['total_volume_month'], 2) }}</p>
            <p class="text-sm opacity-80 mt-1">{{ $summary['total_transactions_month'] }} transactions</p>
        </div>

        <!-- Revenue Today -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Revenue Today</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold">${{ number_format($summary['revenue_today'], 2) }}</p>
            <p class="text-sm opacity-80 mt-1">From fees</p>
        </div>

        <!-- Success Rate -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Success Rate</h3>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold">{{ $transactionStats['success_rate'] }}%</p>
            <p class="text-sm opacity-80 mt-1">{{ $transactionStats['completed'] }}/{{ $transactionStats['total'] }} completed</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Currency Exchange Accounts Balances -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Currency Exchange Accounts</h2>
                <a href="{{ route('fx-provider.accounts') }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700">
                    Manage Accounts
                </a>
            </div>

            @if($accounts->count() > 0)
                <div class="space-y-4">
                    @foreach($accounts as $currency => $accountData)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-lg font-bold text-blue-600">{{ $currency }}</span>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $currency }} Accounts</h3>
                                        <p class="text-sm text-gray-500">{{ $accountData['accounts_count'] }} account(s)</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Total Balance</p>
                                    <p class="text-lg font-bold text-gray-900">{{ number_format($accountData['total_balance'], 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Available</p>
                                    <p class="text-lg font-bold text-green-600">{{ number_format($accountData['available_balance'], 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Reserved</p>
                                    <p class="text-lg font-bold text-orange-600">{{ number_format($accountData['reserved_balance'], 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <p class="text-gray-500 mb-4">No accounts configured yet</p>
                    <a href="{{ route('fx-provider.accounts') }}" class="text-blue-600 hover:text-blue-700 font-semibold">Add your first account</a>
                </div>
            @endif
        </div>

        <!-- Transaction Stats -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Transaction Statistics</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Completed</p>
                            <p class="text-lg font-bold text-gray-900">{{ $transactionStats['completed'] }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-green-600">{{ $transactionStats['success_rate'] }}%</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Pending</p>
                            <p class="text-lg font-bold text-gray-900">{{ $transactionStats['pending'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Rejected</p>
                            <p class="text-lg font-bold text-gray-900">{{ $transactionStats['rejected'] }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-red-600">{{ $transactionStats['failure_rate'] }}%</span>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('fx-provider.initiate-exchange') }}" class="block w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-center font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700">
                    Initiate Exchange Transaction
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Settlement Summary -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Settlement Summary</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pending Settlements</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $settlementSummary['pending_settlements'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 mb-1">Total Value</p>
                        <p class="text-lg font-semibold text-gray-900">${{ number_format($settlementSummary['pending_value'], 2) }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Awaiting User Payment</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $settlementSummary['awaiting_user_payment'] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-green-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Settled Today</p>
                        <p class="text-2xl font-bold text-green-600">{{ $settlementSummary['settled_today'] }}</p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">This Week</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $settlementSummary['settled_this_week'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Activity</h2>
            
            @if($recentActivity->count() > 0)
                <div class="space-y-3">
                    @foreach($recentActivity->take(5) as $activity)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 
                                    @if($activity->status === 'completed') bg-green-100
                                    @elseif($activity->status === 'pending') bg-yellow-100
                                    @elseif($activity->status === 'rejected') bg-red-100
                                    @else bg-blue-100
                                    @endif
                                    rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold
                                        @if($activity->status === 'completed') text-green-600
                                        @elseif($activity->status === 'pending') text-yellow-600
                                        @elseif($activity->status === 'rejected') text-red-600
                                        @else text-blue-600
                                        @endif">
                                        {{ $activity->source_currency }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $activity->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity->source_currency }} → {{ $activity->destination_currency }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">${{ number_format($activity->source_amount, 2) }}</p>
                                <p class="text-xs 
                                    @if($activity->status === 'completed') text-green-600
                                    @elseif($activity->status === 'pending') text-yellow-600
                                    @elseif($activity->status === 'rejected') text-red-600
                                    @else text-blue-600
                                    @endif">
                                    {{ ucfirst($activity->status) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('exchange-requests.index') }}" class="block text-center text-blue-600 hover:text-blue-700 font-semibold text-sm">
                        View All Transactions →
                    </a>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500">No recent activity</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
