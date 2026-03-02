@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('fx-provider.dashboard') }}" class="text-blue-600 hover:text-blue-700 font-semibold mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-4">Initiate Exchange Transaction</h1>
            <p class="text-gray-600 mt-2">Transfer funds between your currency accounts</p>
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

        @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg p-8">
            <form action="{{ route('fx-provider.process-exchange') }}" method="POST" id="exchangeForm">
                @csrf
                
                <!-- Source Account -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Source Account (Debit From)</label>
                    <select name="source_account_id" id="sourceAccount" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="updateSourceInfo()">
                        <option value="">Select source account...</option>
                        @foreach($accounts as $currency => $currencyAccounts)
                            <optgroup label="{{ $currency }} Accounts">
                                @foreach($currencyAccounts as $account)
                                    <option value="{{ $account->id }}" 
                                        data-currency="{{ $account->currency }}" 
                                        data-balance="{{ $account->available_balance }}"
                                        data-name="{{ $account->account_name }}">
                                        {{ $account->account_name }} - {{ \App\Helpers\AccountHelper::maskAccountNumber($account->account_number) }} (Available: {{ number_format($account->available_balance, 2) }} {{ $account->currency }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <div id="sourceInfo" class="mt-2 hidden">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-700"><span class="font-semibold">Available Balance:</span> <span id="sourceBalance"></span></p>
                        </div>
                    </div>
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Amount to Exchange</label>
                    <div class="relative">
                        <input type="number" name="amount" id="amount" step="0.01" min="0" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="0.00" onchange="calculateDestination()">
                        <span id="sourceCurrency" class="absolute right-4 top-3 text-gray-500 font-semibold"></span>
                    </div>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Exchange Rate -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Exchange Rate</label>
                    <div class="relative">
                        <input type="number" name="exchange_rate" id="exchangeRate" step="0.000001" min="0" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="0.00" onchange="calculateDestination()">
                        <span class="absolute right-4 top-3 text-gray-500 text-sm">
                            <span id="rateDisplay"></span>
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Enter the conversion rate from source to destination currency</p>
                </div>

                <!-- Destination Account -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Destination Account (Credit To)</label>
                    <select name="destination_account_id" id="destinationAccount" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="updateDestinationInfo()">
                        <option value="">Select destination account...</option>
                        @foreach($accounts as $currency => $currencyAccounts)
                            <optgroup label="{{ $currency }} Accounts">
                                @foreach($currencyAccounts as $account)
                                    <option value="{{ $account->id }}" 
                                        data-currency="{{ $account->currency }}"
                                        data-name="{{ $account->account_name }}">
                                        {{ $account->account_name }} - {{ \App\Helpers\AccountHelper::maskAccountNumber($account->account_number) }} ({{ $account->currency }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <div id="destinationInfo" class="mt-2 hidden">
                        <div class="p-3 bg-green-50 rounded-lg">
                            <p class="text-sm text-gray-700"><span class="font-semibold">Will Receive:</span> <span id="destinationAmount" class="text-lg font-bold text-green-600"></span></p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Add any notes about this transaction..."></textarea>
                </div>

                <!-- Transaction Summary -->
                <div id="transactionSummary" class="mb-6 p-6 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg border-2 border-blue-200 hidden">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Transaction Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-700">From:</span>
                            <span class="font-semibold text-gray-900" id="summarySource"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">To:</span>
                            <span class="font-semibold text-gray-900" id="summaryDestination"></span>
                        </div>
                        <div class="h-px bg-gray-300"></div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Amount to Debit:</span>
                            <span class="font-semibold text-red-600" id="summaryDebit"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Amount to Credit:</span>
                            <span class="font-semibold text-green-600" id="summaryCredit"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Exchange Rate:</span>
                            <span class="font-semibold text-gray-900" id="summaryRate"></span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold text-lg rounded-lg hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Process Exchange Transaction
                    </button>
                    <a href="{{ route('fx-provider.dashboard') }}" class="px-6 py-4 bg-gray-500 text-white font-bold text-lg rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateSourceInfo() {
    const select = document.getElementById('sourceAccount');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        const balance = option.getAttribute('data-balance');
        const currency = option.getAttribute('data-currency');
        
        document.getElementById('sourceBalance').textContent = parseFloat(balance).toFixed(2) + ' ' + currency;
        document.getElementById('sourceCurrency').textContent = currency;
        document.getElementById('sourceInfo').classList.remove('hidden');
        
        calculateDestination();
    } else {
        document.getElementById('sourceInfo').classList.add('hidden');
        document.getElementById('sourceCurrency').textContent = '';
    }
}

function updateDestinationInfo() {
    calculateDestination();
}

function calculateDestination() {
    const sourceSelect = document.getElementById('sourceAccount');
    const destSelect = document.getElementById('destinationAccount');
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const rate = parseFloat(document.getElementById('exchangeRate').value) || 0;
    
    if (sourceSelect.value && destSelect.value && amount > 0 && rate > 0) {
        const sourceOption = sourceSelect.options[sourceSelect.selectedIndex];
        const destOption = destSelect.options[destSelect.selectedIndex];
        
        const sourceCurrency = sourceOption.getAttribute('data-currency');
        const destCurrency = destOption.getAttribute('data-currency');
        const destinationAmount = amount * rate;
        
        document.getElementById('destinationAmount').textContent = destinationAmount.toFixed(2) + ' ' + destCurrency;
        document.getElementById('destinationInfo').classList.remove('hidden');
        document.getElementById('rateDisplay').textContent = '1 ' + sourceCurrency + ' = ' + rate + ' ' + destCurrency;
        
        // Update summary
        document.getElementById('summarySource').textContent = sourceOption.getAttribute('data-name') + ' (' + sourceCurrency + ')';
        document.getElementById('summaryDestination').textContent = destOption.getAttribute('data-name') + ' (' + destCurrency + ')';
        document.getElementById('summaryDebit').textContent = amount.toFixed(2) + ' ' + sourceCurrency;
        document.getElementById('summaryCredit').textContent = destinationAmount.toFixed(2) + ' ' + destCurrency;
        document.getElementById('summaryRate').textContent = '1 ' + sourceCurrency + ' = ' + rate + ' ' + destCurrency;
        document.getElementById('transactionSummary').classList.remove('hidden');
    } else {
        document.getElementById('destinationInfo').classList.add('hidden');
        document.getElementById('transactionSummary').classList.add('hidden');
    }
}
</script>
@endsection
