<?php

namespace App\Http\Controllers;

use App\FxProvider;
use App\FxProviderAccount;
use App\ExchangeRequest;
use App\CrossBorderTransactionIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FxProviderDashboardController extends Controller
{
    /**
     * Display the consolidated accounts summary dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $fxProvider = FxProvider::where('user_id', $user->id)->first();
        
        if (!$fxProvider) {
            return redirect()->back()->with('error', 'You are not registered as an FX Provider.');
        }
        
        // Get all summary data
        $summary = $this->getConsolidatedSummary($fxProvider->id);
        $accounts = $this->getAccountsBalances($fxProvider->id);
        $transactionStats = $this->getTransactionStats($fxProvider->id);
        $settlementSummary = $this->getSettlementSummary($fxProvider->id);
        $recentActivity = $this->getRecentActivity($fxProvider->id);
        
        return view('fx-provider.dashboard', compact(
            'fxProvider',
            'summary',
            'accounts',
            'transactionStats',
            'settlementSummary',
            'recentActivity'
        ));
    }
    
    /**
     * Get consolidated transactions and exchange summary.
     */
    private function getConsolidatedSummary($providerId)
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        
        return [
            'total_volume_today' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->sum('source_amount'),
            
            'total_volume_month' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->sum('source_amount'),
            
            'total_transactions_today' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->count(),
            
            'total_transactions_month' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->count(),
            
            'total_value_exchanged' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'completed')
                ->sum('source_amount'),
            
            'revenue_today' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->sum('provider_fee'),
            
            'revenue_month' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->sum('provider_fee'),
        ];
    }
    
    /**
     * Get currency exchange accounts balances.
     */
    private function getAccountsBalances($providerId)
    {
        return FxProviderAccount::where('fx_provider_id', $providerId)
            ->active()
            ->get()
            ->groupBy('currency')
            ->map(function ($accounts) {
                return [
                    'currency' => $accounts->first()->currency,
                    'total_balance' => $accounts->sum('current_balance'),
                    'available_balance' => $accounts->sum('available_balance'),
                    'reserved_balance' => $accounts->sum('reserved_balance'),
                    'accounts_count' => $accounts->count(),
                    'accounts' => $accounts,
                ];
            });
    }
    
    /**
     * Get transaction success and failure rates.
     */
    private function getTransactionStats($providerId)
    {
        $total = ExchangeRequest::where('fx_provider_id', $providerId)->count();
        $completed = ExchangeRequest::where('fx_provider_id', $providerId)
            ->where('status', 'completed')
            ->count();
        $rejected = ExchangeRequest::where('fx_provider_id', $providerId)
            ->where('status', 'rejected')
            ->count();
        $pending = ExchangeRequest::where('fx_provider_id', $providerId)
            ->whereIn('status', ['pending', 'accepted', 'user_payment_confirmed'])
            ->count();
        
        return [
            'total' => $total,
            'completed' => $completed,
            'rejected' => $rejected,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'failure_rate' => $total > 0 ? round(($rejected / $total) * 100, 2) : 0,
        ];
    }
    
    /**
     * Get settlement summaries and pending transactions.
     */
    private function getSettlementSummary($providerId)
    {
        return [
            'pending_settlements' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'user_payment_confirmed')
                ->count(),
            
            'pending_value' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'user_payment_confirmed')
                ->sum('destination_amount'),
            
            'awaiting_user_payment' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'accepted')
                ->count(),
            
            'settled_today' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'completed')
                ->whereDate('completed_at', now()->startOfDay())
                ->count(),
            
            'settled_this_week' => ExchangeRequest::where('fx_provider_id', $providerId)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];
    }
    
    /**
     * Get recent activity.
     */
    private function getRecentActivity($providerId)
    {
        return ExchangeRequest::where('fx_provider_id', $providerId)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();
    }
    
    /**
     * Show account management page.
     */
    public function accounts()
    {
        $user = Auth::user();
        $fxProvider = FxProvider::where('user_id', $user->id)->first();
        
        if (!$fxProvider) {
            return redirect()->back()->with('error', 'You are not registered as an FX Provider.');
        }
        
        $accounts = FxProviderAccount::where('fx_provider_id', $fxProvider->id)
            ->latest()
            ->get();
        
        return view('fx-provider.accounts', compact('fxProvider', 'accounts'));
    }
    
    /**
     * Store a new account.
     */
    public function storeAccount(Request $request)
    {
        $user = Auth::user();
        $fxProvider = FxProvider::where('user_id', $user->id)->first();
        
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:fx_provider_accounts,account_number',
            'currency' => 'required|string|max:10',
            'bank_name' => 'required|string|max:255',
            'account_type' => 'required|in:exchange,settlement,reserve',
            'current_balance' => 'required|numeric|min:0',
            'daily_limit' => 'nullable|numeric|min:0',
            'monthly_limit' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        
        $account = FxProviderAccount::create([
            'fx_provider_id' => $fxProvider->id,
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'currency' => $validated['currency'],
            'bank_name' => $validated['bank_name'],
            'account_type' => $validated['account_type'],
            'current_balance' => $validated['current_balance'],
            'available_balance' => $validated['current_balance'],
            'reserved_balance' => 0,
            'daily_limit' => $validated['daily_limit'] ?? null,
            'monthly_limit' => $validated['monthly_limit'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);
        
        return redirect()->route('fx-provider.accounts')
            ->with('success', 'Account added successfully!');
    }
    
    /**
     * Show initiate exchange transaction form.
     */
    public function initiateExchange()
    {
        $user = Auth::user();
        $fxProvider = FxProvider::where('user_id', $user->id)->first();
        
        if (!$fxProvider) {
            return redirect()->back()->with('error', 'You are not registered as an FX Provider.');
        }
        
        $accounts = FxProviderAccount::where('fx_provider_id', $fxProvider->id)
            ->active()
            ->get()
            ->groupBy('currency');
        
        return view('fx-provider.initiate-exchange', compact('fxProvider', 'accounts'));
    }
    
    /**
     * Process initiated exchange transaction.
     */
    public function processExchange(Request $request)
    {
        $validated = $request->validate([
            'source_account_id' => 'required|exists:fx_provider_accounts,id',
            'destination_account_id' => 'required|exists:fx_provider_accounts,id',
            'amount' => 'required|numeric|min:0',
            'exchange_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        
        $sourceAccount = FxProviderAccount::findOrFail($validated['source_account_id']);
        $destinationAccount = FxProviderAccount::findOrFail($validated['destination_account_id']);
        
        // Check if sufficient balance
        if ($sourceAccount->available_balance < $validated['amount']) {
            return redirect()->back()->with('error', 'Insufficient balance in source account.');
        }
        
        // Calculate destination amount
        $destinationAmount = $validated['amount'] * $validated['exchange_rate'];
        
        DB::beginTransaction();
        try {
            // Deduct from source account
            $sourceAccount->decrement('current_balance', $validated['amount']);
            $sourceAccount->decrement('available_balance', $validated['amount']);
            
            // Add to destination account
            $destinationAccount->increment('current_balance', $destinationAmount);
            $destinationAccount->increment('available_balance', $destinationAmount);
            
            DB::commit();
            
            return redirect()->route('fx-provider.dashboard')
                ->with('success', 'Exchange transaction completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }
}
