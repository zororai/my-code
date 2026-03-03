@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('trading-accounts.select-type') }}" class="text-blue-600 hover:text-blue-700 mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Select Your Broker</h1>
            <p class="text-gray-600">Filter by country, asset type, and financial market</p>
        </div>

        <!-- Filter Form -->
        <form action="{{ route('trading-accounts.select-action') }}" method="POST" id="brokerFilterForm">
            @csrf
            <input type="hidden" name="account_type" value="{{ $accountType }}">
            
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <!-- Step 1: Country Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">1. Select Country</label>
                    <select name="country" id="countrySelect" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Choose a country...</option>
                        @foreach($countries as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Step 2: Asset Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">2. Select Asset Type</label>
                    <select name="asset_type" id="assetTypeSelect" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Choose asset type...</option>
                        <option value="stocks">Stocks</option>
                        <option value="bonds">Bonds & Fixed Income</option>
                        <option value="commodities">Commodities</option>
                        <option value="real_estate">Real Estate</option>
                        <option value="cryptocurrency">Digital Assets (Cryptocurrency)</option>
                        <option value="etf">ETFs</option>
                        <option value="mutual_funds">Mutual Funds</option>
                    </select>
                </div>

                <!-- Step 3: Financial Market Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">3. Select Financial Market (Optional)</label>
                    <select name="financial_market" id="financialMarketSelect" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Choose financial market...</option>
                    </select>
                </div>

                <!-- Step 4: Broker Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">4. Select Broker</label>
                    <div id="brokersList" class="space-y-3">
                        <p class="text-gray-500 text-center py-8">Please select country and asset type to see available brokers</p>
                    </div>
                    <input type="hidden" name="broker_name" id="selectedBrokerName">
                    <input type="hidden" name="broker_code" id="selectedBrokerCode">
                </div>

                <!-- Submit Button -->
                <button type="submit" id="continueBtn" disabled class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed">
                    Continue
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('countrySelect');
    const assetTypeSelect = document.getElementById('assetTypeSelect');
    const financialMarketSelect = document.getElementById('financialMarketSelect');
    const brokersList = document.getElementById('brokersList');
    const continueBtn = document.getElementById('continueBtn');
    const selectedBrokerName = document.getElementById('selectedBrokerName');
    const selectedBrokerCode = document.getElementById('selectedBrokerCode');

    let selectedBroker = null;

    // Financial markets by country
    const markets = {
        'ZW': ['Zimbabwe Stock Exchange', 'Zimbabwe Crypto Market'],
        'ZA': ['Johannesburg Stock Exchange', 'Cape Town Stock Exchange'],
        'US': ['NYSE', 'NASDAQ', 'US Crypto Market'],
        'GB': ['London Stock Exchange', 'UK Crypto Market'],
        'SG': ['Singapore Exchange'],
        'HK': ['Hong Kong Stock Exchange']
    };

    countrySelect.addEventListener('change', function() {
        const country = this.value;
        financialMarketSelect.innerHTML = '<option value="">Choose financial market...</option>';
        
        if (country && markets[country]) {
            markets[country].forEach(market => {
                const option = document.createElement('option');
                option.value = market;
                option.textContent = market;
                financialMarketSelect.appendChild(option);
            });
        }
        
        loadBrokers();
    });

    assetTypeSelect.addEventListener('change', loadBrokers);
    financialMarketSelect.addEventListener('change', loadBrokers);

    function loadBrokers() {
        const country = countrySelect.value;
        const assetType = assetTypeSelect.value;
        const financialMarket = financialMarketSelect.value;

        if (!country || !assetType) {
            brokersList.innerHTML = '<p class="text-gray-500 text-center py-8">Please select country and asset type to see available brokers</p>';
            return;
        }

        brokersList.innerHTML = '<p class="text-gray-500 text-center py-4">Loading brokers...</p>';

        fetch(`/wealth/trading-accounts/get-brokers?country=${country}&asset_type=${assetType}&financial_market=${financialMarket}`)
            .then(response => response.json())
            .then(data => {
                if (data.brokers && data.brokers.length > 0) {
                    brokersList.innerHTML = '';
                    data.brokers.forEach(broker => {
                        const brokerCard = document.createElement('div');
                        brokerCard.className = 'p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-colors broker-card';
                        brokerCard.innerHTML = `
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-900">${broker.name}</h4>
                                    <p class="text-sm text-gray-600">${broker.market}</p>
                                </div>
                                <div class="w-6 h-6 border-2 border-gray-300 rounded-full broker-checkbox"></div>
                            </div>
                        `;
                        brokerCard.addEventListener('click', () => selectBroker(broker, brokerCard));
                        brokersList.appendChild(brokerCard);
                    });
                } else {
                    brokersList.innerHTML = '<p class="text-gray-500 text-center py-8">No brokers found for the selected criteria</p>';
                }
            })
            .catch(error => {
                console.error('Error loading brokers:', error);
                brokersList.innerHTML = '<p class="text-red-500 text-center py-8">Error loading brokers. Please try again.</p>';
            });
    }

    function selectBroker(broker, card) {
        // Remove selection from all cards
        document.querySelectorAll('.broker-card').forEach(c => {
            c.classList.remove('border-blue-500', 'bg-blue-50');
            c.querySelector('.broker-checkbox').classList.remove('bg-blue-500', 'border-blue-500');
        });

        // Add selection to clicked card
        card.classList.add('border-blue-500', 'bg-blue-50');
        card.querySelector('.broker-checkbox').classList.add('bg-blue-500', 'border-blue-500');

        selectedBroker = broker;
        selectedBrokerName.value = broker.name;
        selectedBrokerCode.value = broker.code;
        continueBtn.disabled = false;
    }
});
</script>
@endsection
