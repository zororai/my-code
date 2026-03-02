<?php

namespace App\Http\Controllers;

use App\TradingAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradingAccountController extends Controller
{
    /**
     * Show account type selection page.
     */
    public function selectAccountType()
    {
        return view('wealth.trading-accounts.select-type');
    }

    /**
     * Show broker selection flow.
     */
    public function selectBroker(Request $request)
    {
        $accountType = $request->query('type'); // stock_broker, crypto_exchange, investment_platform
        
        if (!in_array($accountType, ['stock_broker', 'crypto_exchange', 'investment_platform'])) {
            return redirect()->route('trading-accounts.select-type');
        }

        // Get countries list
        $countries = $this->getCountries();
        
        return view('wealth.trading-accounts.select-broker', compact('accountType', 'countries'));
    }

    /**
     * Get brokers based on filters (AJAX).
     */
    public function getBrokers(Request $request)
    {
        $country = $request->input('country');
        $assetType = $request->input('asset_type');
        $financialMarket = $request->input('financial_market');

        $brokers = $this->getBrokersList($country, $assetType, $financialMarket);

        return response()->json(['brokers' => $brokers]);
    }

    /**
     * Show action selection (Create or Link).
     */
    public function selectAction(Request $request)
    {
        $validated = $request->validate([
            'account_type' => 'required|in:stock_broker,crypto_exchange,investment_platform',
            'country' => 'required|string',
            'asset_type' => 'required|string',
            'financial_market' => 'nullable|string',
            'broker_name' => 'required|string',
            'broker_code' => 'nullable|string',
        ]);

        return view('wealth.trading-accounts.select-action', $validated);
    }

    /**
     * Redirect to broker platform for account creation.
     */
    public function createWithBroker(Request $request)
    {
        $brokerName = $request->input('broker_name');
        $brokerUrl = $this->getBrokerRegistrationUrl($brokerName);

        // Store pending connection in session
        session([
            'pending_trading_account' => [
                'account_type' => $request->input('account_type'),
                'country' => $request->input('country'),
                'asset_type' => $request->input('asset_type'),
                'financial_market' => $request->input('financial_market'),
                'broker_name' => $brokerName,
                'broker_code' => $request->input('broker_code'),
                'connection_type' => 'created',
            ]
        ]);

        return view('wealth.trading-accounts.redirect-to-broker', [
            'broker_name' => $brokerName,
            'broker_url' => $brokerUrl,
        ]);
    }

    /**
     * Show link existing account form.
     */
    public function linkExistingForm(Request $request)
    {
        $data = [
            'account_type' => $request->input('account_type'),
            'country' => $request->input('country'),
            'asset_type' => $request->input('asset_type'),
            'financial_market' => $request->input('financial_market'),
            'broker_name' => $request->input('broker_name'),
            'broker_code' => $request->input('broker_code'),
        ];

        return view('wealth.trading-accounts.link-existing', $data);
    }

    /**
     * Process linking existing account.
     */
    public function linkExisting(Request $request)
    {
        $validated = $request->validate([
            'account_type' => 'required|in:stock_broker,crypto_exchange,investment_platform',
            'country' => 'required|string',
            'asset_type' => 'required|string',
            'financial_market' => 'nullable|string',
            'broker_name' => 'required|string',
            'broker_code' => 'nullable|string',
            'account_holder_name' => 'required|string|max:255',
            'trading_account_number' => 'required|string|max:255',
            'terms_accepted' => 'required|accepted',
        ]);

        // Create trading account
        $tradingAccount = TradingAccount::create([
            'user_id' => Auth::id(),
            'account_type' => $validated['account_type'],
            'country' => $validated['country'],
            'asset_type' => $validated['asset_type'],
            'financial_market' => $validated['financial_market'],
            'broker_name' => $validated['broker_name'],
            'broker_code' => $validated['broker_code'],
            'account_holder_name' => $validated['account_holder_name'],
            'trading_account_number' => $validated['trading_account_number'],
            'connection_type' => 'linked',
            'is_connected' => true,
            'connected_at' => now(),
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
        ]);

        // Simulate pulling data from broker API
        $this->pullAccountData($tradingAccount);

        return redirect()->route('wealth.portfolio')
            ->with('success', 'Trading account connected successfully! Your portfolio data is being synced.');
    }

    /**
     * Handle callback from broker after account creation.
     */
    public function brokerCallback(Request $request)
    {
        $pendingAccount = session('pending_trading_account');

        if (!$pendingAccount) {
            return redirect()->route('trading-accounts.select-type')
                ->with('error', 'No pending account connection found.');
        }

        // In real implementation, verify callback from broker
        $accountNumber = $request->input('account_number') ?? 'AUTO-' . strtoupper(uniqid());
        $accountHolderName = $request->input('account_holder_name') ?? Auth::user()->name;

        $tradingAccount = TradingAccount::create([
            'user_id' => Auth::id(),
            'account_type' => $pendingAccount['account_type'],
            'country' => $pendingAccount['country'],
            'asset_type' => $pendingAccount['asset_type'],
            'financial_market' => $pendingAccount['financial_market'],
            'broker_name' => $pendingAccount['broker_name'],
            'broker_code' => $pendingAccount['broker_code'],
            'account_holder_name' => $accountHolderName,
            'trading_account_number' => $accountNumber,
            'connection_type' => 'created',
            'is_connected' => true,
            'connected_at' => now(),
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
        ]);

        // Pull account data
        $this->pullAccountData($tradingAccount);

        session()->forget('pending_trading_account');

        return redirect()->route('wealth.portfolio')
            ->with('success', 'Trading account created and connected successfully!');
    }

    /**
     * Get list of countries.
     */
    private function getCountries()
    {
        return [
            'ZW' => 'Zimbabwe',
            'ZA' => 'South Africa',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'SG' => 'Singapore',
            'HK' => 'Hong Kong',
        ];
    }

    /**
     * Get brokers list based on filters.
     */
    private function getBrokersList($country, $assetType, $financialMarket)
    {
        // Sample brokers data - in production, this would come from database
        $allBrokers = [
            'ZW' => [
                'stocks' => [
                    ['name' => 'Morgan & Co Securities', 'code' => 'MORGAN', 'market' => 'Zimbabwe Stock Exchange'],
                    ['name' => 'IH Securities', 'code' => 'IHS', 'market' => 'Zimbabwe Stock Exchange'],
                ],
                'cryptocurrency' => [
                    ['name' => 'Golix', 'code' => 'GOLIX', 'market' => 'Zimbabwe Crypto Market'],
                ],
            ],
            'ZA' => [
                'stocks' => [
                    ['name' => 'Standard Bank Securities', 'code' => 'SBS', 'market' => 'Johannesburg Stock Exchange'],
                    ['name' => 'Investec', 'code' => 'INVESTEC', 'market' => 'Johannesburg Stock Exchange'],
                ],
            ],
            'US' => [
                'stocks' => [
                    ['name' => 'Charles Schwab', 'code' => 'SCHWAB', 'market' => 'NYSE'],
                    ['name' => 'TD Ameritrade', 'code' => 'TDA', 'market' => 'NYSE'],
                    ['name' => 'E*TRADE', 'code' => 'ETRADE', 'market' => 'NASDAQ'],
                ],
                'cryptocurrency' => [
                    ['name' => 'Coinbase', 'code' => 'COINBASE', 'market' => 'US Crypto Market'],
                    ['name' => 'Kraken', 'code' => 'KRAKEN', 'market' => 'US Crypto Market'],
                ],
            ],
        ];

        return $allBrokers[$country][$assetType] ?? [];
    }

    /**
     * Get broker registration URL.
     */
    private function getBrokerRegistrationUrl($brokerName)
    {
        $urls = [
            'Morgan & Co Securities' => 'https://morgan.co.zw/register',
            'Charles Schwab' => 'https://www.schwab.com/open-account',
            'Coinbase' => 'https://www.coinbase.com/signup',
        ];

        return $urls[$brokerName] ?? '#';
    }

    /**
     * Pull account data from broker API.
     */
    private function pullAccountData($tradingAccount)
    {
        // Simulate pulling data - in production, integrate with broker APIs
        $sampleHoldings = [
            [
                'symbol' => 'AAPL',
                'name' => 'Apple Inc.',
                'quantity' => 10,
                'price' => 150.00,
                'value' => 1500.00,
            ],
            [
                'symbol' => 'GOOGL',
                'name' => 'Alphabet Inc.',
                'quantity' => 5,
                'price' => 2800.00,
                'value' => 14000.00,
            ],
        ];

        $totalValue = collect($sampleHoldings)->sum('value');

        $tradingAccount->update([
            'holdings' => $sampleHoldings,
            'total_value' => $totalValue,
            'last_synced_at' => now(),
        ]);
    }
}
