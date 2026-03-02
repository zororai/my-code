@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Create FX Offer</h1>
                <p class="text-gray-600 mt-2">Create a new foreign exchange offer for the marketplace</p>
            </div>

            <form action="{{ route('fx.offers.store') }}" method="POST" id="offerForm">
                @csrf

                <!-- Source Accounts -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Source Accounts</label>
                    <p class="text-xs text-gray-500 mb-3">Add multiple source accounts for currency exchange</p>
                    <div id="sourceAccountsContainer">
                        <div class="flex gap-2 mb-2 source-account-row">
                            <input type="text" name="source_accounts[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., USD Account - Bank of America" required>
                            <button type="button" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 remove-row hidden">Remove</button>
                        </div>
                    </div>
                    <button type="button" id="addSourceAccount" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">+ Add Source Account</button>
                    @error('source_accounts')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Destination Accounts -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Destination Accounts</label>
                    <p class="text-xs text-gray-500 mb-3">Add multiple destination accounts for currency exchange</p>
                    <div id="destinationAccountsContainer">
                        <div class="flex gap-2 mb-2 destination-account-row">
                            <input type="text" name="destination_accounts[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., ZWL Account - CBZ Bank" required>
                            <button type="button" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 remove-row hidden">Remove</button>
                        </div>
                    </div>
                    <button type="button" id="addDestinationAccount" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">+ Add Destination Account</button>
                    @error('destination_accounts')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Exchange Rates -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Buy Rate</label>
                        <input type="number" step="0.000001" name="buy_rate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 1.234567" required value="{{ old('buy_rate') }}">
                        @error('buy_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sell Rate</label>
                        <input type="number" step="0.000001" name="sell_rate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 1.234567" required value="{{ old('sell_rate') }}">
                        @error('sell_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Settlement Methods -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Preferred Settlement Methods</label>
                    <p class="text-xs text-gray-500 mb-3">Select all applicable settlement methods</p>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="settlement_methods[]" value="Bank Transfer" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-gray-700">Bank Transfer</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="settlement_methods[]" value="Mobile Wallet" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-gray-700">Mobile Wallet</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="settlement_methods[]" value="Card Payment" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-gray-700">Card Payment</span>
                        </label>
                    </div>
                    @error('settlement_methods')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Trade Value Limits -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Minimum Trade Value</label>
                        <input type="number" step="0.01" name="min_trade_value" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 100.00" required value="{{ old('min_trade_value') }}">
                        @error('min_trade_value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Maximum Trade Value</label>
                        <input type="number" step="0.01" name="max_trade_value" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 100000.00" required value="{{ old('max_trade_value') }}">
                        @error('max_trade_value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Available Total Amounts -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Available Total Amounts</label>
                    <p class="text-xs text-gray-500 mb-3">Add multiple amounts and currencies you can exchange</p>
                    <div id="availableAmountsContainer">
                        <div class="flex gap-2 mb-2 available-amount-row">
                            <input type="number" step="0.01" name="available_amounts[0][amount]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Amount" required>
                            <input type="text" name="available_amounts[0][currency]" class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Currency" required>
                            <button type="button" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 remove-amount hidden">Remove</button>
                        </div>
                    </div>
                    <button type="button" id="addAvailableAmount" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">+ Add Amount</button>
                    @error('available_amounts')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Trading Hours -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Open Time</label>
                        <input type="time" name="open_time" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required value="{{ old('open_time') }}">
                        @error('open_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Close Time</label>
                        <input type="time" name="close_time" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required value="{{ old('close_time') }}">
                        @error('close_time')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Permissible Trading Currencies -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Permissible Trading Currencies</label>
                    <p class="text-xs text-gray-500 mb-3">Add all currencies you can manage to exchange</p>
                    <div id="tradingCurrenciesContainer">
                        <div class="flex gap-2 mb-2 trading-currency-row">
                            <input type="text" name="trading_currencies[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., USD, EUR, GBP, ZWL" required>
                            <button type="button" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 remove-row hidden">Remove</button>
                        </div>
                    </div>
                    <button type="button" id="addTradingCurrency" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">+ Add Currency</button>
                    @error('trading_currencies')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Processing Fee -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Processing Fee (%)</label>
                    <input type="number" step="0.01" name="processing_fee_percentage" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 2.5" required value="{{ old('processing_fee_percentage') }}">
                    <p class="text-xs text-gray-500 mt-1">Percentage fee per trade</p>
                    @error('processing_fee_percentage')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Create Offer
                    </button>
                    <a href="{{ route('fx.marketplace') }}" class="px-6 py-3 bg-gray-500 text-white font-semibold rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let amountIndex = 1;

    // Add Source Account
    document.getElementById('addSourceAccount').addEventListener('click', function() {
        const container = document.getElementById('sourceAccountsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'flex gap-2 mb-2 source-account-row';
        newRow.innerHTML = `
            <input type="text" name="source_accounts[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., USD Account - Bank of America" required>
            <button type="button" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 remove-row">Remove</button>
        `;
        container.appendChild(newRow);
        updateRemoveButtons('source-account-row');
    });

    // Add Destination Account
    document.getElementById('addDestinationAccount').addEventListener('click', function() {
        const container = document.getElementById('destinationAccountsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'flex gap-2 mb-2 destination-account-row';
        newRow.innerHTML = `
            <input type="text" name="destination_accounts[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., ZWL Account - CBZ Bank" required>
            <button type="button" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 remove-row">Remove</button>
        `;
        container.appendChild(newRow);
        updateRemoveButtons('destination-account-row');
    });

    // Add Available Amount
    document.getElementById('addAvailableAmount').addEventListener('click', function() {
        const container = document.getElementById('availableAmountsContainer');
        const newRow = document.createElement('div');
        newRow.className = 'flex gap-2 mb-2 available-amount-row';
        newRow.innerHTML = `
            <input type="number" step="0.01" name="available_amounts[${amountIndex}][amount]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Amount" required>
            <input type="text" name="available_amounts[${amountIndex}][currency]" class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Currency" required>
            <button type="button" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 remove-amount">Remove</button>
        `;
        container.appendChild(newRow);
        amountIndex++;
        updateRemoveButtons('available-amount-row');
    });

    // Add Trading Currency
    document.getElementById('addTradingCurrency').addEventListener('click', function() {
        const container = document.getElementById('tradingCurrenciesContainer');
        const newRow = document.createElement('div');
        newRow.className = 'flex gap-2 mb-2 trading-currency-row';
        newRow.innerHTML = `
            <input type="text" name="trading_currencies[]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., USD, EUR, GBP, ZWL" required>
            <button type="button" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 remove-row">Remove</button>
        `;
        container.appendChild(newRow);
        updateRemoveButtons('trading-currency-row');
    });

    // Remove row functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row') || e.target.classList.contains('remove-amount')) {
            e.target.closest('div').remove();
            updateRemoveButtons('source-account-row');
            updateRemoveButtons('destination-account-row');
            updateRemoveButtons('available-amount-row');
            updateRemoveButtons('trading-currency-row');
        }
    });

    function updateRemoveButtons(className) {
        const rows = document.querySelectorAll('.' + className);
        rows.forEach((row, index) => {
            const removeBtn = row.querySelector('.remove-row, .remove-amount');
            if (rows.length > 1) {
                removeBtn.classList.remove('hidden');
            } else {
                removeBtn.classList.add('hidden');
            }
        });
    }
});
</script>
@endsection
