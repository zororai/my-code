<?php

namespace App\Http\Controllers;

use App\MerchantTerminal;
use App\SoftPOSTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SoftPOSController extends Controller
{
    /**
     * Display merchant dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $terminal = MerchantTerminal::where('user_id', $user->id)->first();

        if (!$terminal) {
            return redirect()->route('softpos.setup');
        }

        $todayTransactions = $terminal->todayTransactions()->count();
        $todayVolume = $terminal->todayTransactions()->sum('amount');
        $totalTransactions = $terminal->total_transactions;
        $totalVolume = $terminal->total_volume;

        $recentTransactions = $terminal->transactions()
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'today_transactions' => $todayTransactions,
            'today_volume' => $todayVolume,
            'total_transactions' => $totalTransactions,
            'total_volume' => $totalVolume,
            'success_rate' => $this->calculateSuccessRate($terminal),
            'daily_limit_used' => ($terminal->daily_processed / $terminal->daily_limit) * 100,
        ];

        return view('softpos.dashboard', compact('terminal', 'stats', 'recentTransactions'));
    }

    /**
     * Show terminal setup form.
     */
    public function setup()
    {
        $user = Auth::user();
        $terminal = MerchantTerminal::where('user_id', $user->id)->first();

        if ($terminal) {
            return redirect()->route('softpos.dashboard');
        }

        return view('softpos.setup');
    }

    /**
     * Store new terminal.
     */
    public function storeTerminal(Request $request)
    {
        $validated = $request->validate([
            'terminal_name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
        ]);

        $terminal = MerchantTerminal::create([
            'user_id' => Auth::id(),
            'terminal_id' => 'TERM-' . strtoupper(uniqid()),
            'terminal_name' => $validated['terminal_name'],
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'] ?? 'Retail',
            'address' => $validated['address'],
            'city' => $validated['city'],
            'country' => 'Zimbabwe',
            'device_type' => 'mobile',
            'is_active' => true,
            'is_verified' => true,
            'verified_at' => now(),
            'accepted_payment_methods' => ['card', 'mobile_money', 'qr_code'],
        ]);

        return redirect()->route('softpos.dashboard')
            ->with('success', 'Terminal setup completed successfully!');
    }

    /**
     * Show payment processing form.
     */
    public function processPayment()
    {
        $user = Auth::user();
        $terminal = MerchantTerminal::where('user_id', $user->id)->first();

        if (!$terminal) {
            return redirect()->route('softpos.setup');
        }

        return view('softpos.process-payment', compact('terminal'));
    }

    /**
     * Process payment transaction.
     */
    public function submitPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:card,mobile_money,qr_code',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string|max:20',
            'card_number' => 'required_if:payment_method,card|nullable|string',
            'mobile_number' => 'required_if:payment_method,mobile_money|nullable|string',
            'mobile_network' => 'required_if:payment_method,mobile_money|nullable|string',
        ]);

        $user = Auth::user();
        $terminal = MerchantTerminal::where('user_id', $user->id)->first();

        if (!$terminal) {
            return redirect()->back()->with('error', 'Terminal not found.');
        }

        if ($terminal->isDailyLimitReached()) {
            return redirect()->back()->with('error', 'Daily transaction limit reached.');
        }

        if ($terminal->exceedsTransactionLimit($validated['amount'])) {
            return redirect()->back()->with('error', 'Transaction amount exceeds limit.');
        }

        $merchantFee = $validated['amount'] * 0.029; // 2.9%
        $processingFee = 0.30; // Fixed fee
        $netAmount = $validated['amount'] - $merchantFee - $processingFee;

        $transaction = SoftPOSTransaction::create([
            'merchant_terminal_id' => $terminal->id,
            'transaction_id' => SoftPOSTransaction::generateTransactionId(),
            'reference_number' => SoftPOSTransaction::generateReferenceNumber(),
            'amount' => $validated['amount'],
            'currency' => 'USD',
            'payment_method' => $validated['payment_method'],
            'payment_provider' => $this->getPaymentProvider($validated),
            'card_type' => $validated['payment_method'] === 'card' ? 'Visa' : null,
            'card_last_four' => $validated['payment_method'] === 'card' ? substr($validated['card_number'], -4) : null,
            'card_brand' => $validated['payment_method'] === 'card' ? 'Visa' : null,
            'mobile_number' => $validated['mobile_number'] ?? null,
            'mobile_network' => $validated['mobile_network'] ?? null,
            'status' => 'approved',
            'status_message' => 'Payment approved',
            'authorization_code' => 'AUTH-' . strtoupper(uniqid()),
            'merchant_fee' => $merchantFee,
            'processing_fee' => $processingFee,
            'net_amount' => $netAmount,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'receipt_number' => SoftPOSTransaction::generateReceiptNumber(),
            'processed_at' => now(),
        ]);

        $terminal->updateStatistics($validated['amount']);

        return redirect()->route('softpos.receipt', $transaction->id)
            ->with('success', 'Payment processed successfully!');
    }

    /**
     * Show transaction receipt.
     */
    public function receipt($id)
    {
        $transaction = SoftPOSTransaction::with('merchantTerminal')->findOrFail($id);
        
        if ($transaction->merchantTerminal->user_id !== Auth::id()) {
            abort(403);
        }

        return view('softpos.receipt', compact('transaction'));
    }

    /**
     * Show transaction history.
     */
    public function transactions()
    {
        $user = Auth::user();
        $terminal = MerchantTerminal::where('user_id', $user->id)->first();

        if (!$terminal) {
            return redirect()->route('softpos.setup');
        }

        $transactions = $terminal->transactions()
            ->latest()
            ->paginate(20);

        return view('softpos.transactions', compact('terminal', 'transactions'));
    }

    /**
     * Calculate success rate.
     */
    private function calculateSuccessRate($terminal)
    {
        $total = $terminal->transactions()->count();
        if ($total === 0) {
            return 0;
        }

        $successful = $terminal->successfulTransactions()->count();
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get payment provider from request.
     */
    private function getPaymentProvider($validated)
    {
        if ($validated['payment_method'] === 'card') {
            return 'Visa';
        } elseif ($validated['payment_method'] === 'mobile_money') {
            return $validated['mobile_network'] ?? 'EcoCash';
        }
        return 'QR Code';
    }
}
