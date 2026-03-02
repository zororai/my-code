<?php

namespace App\Http\Controllers;

use App\ExchangeRequest;
use App\FxProvider;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ExchangeRequestNotification;

class ExchangeRequestController extends Controller
{
    /**
     * Display incoming exchange requests for FX Provider.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get the FX provider associated with the logged-in user
        $fxProvider = FxProvider::where('user_id', $user->id)->first();
        
        if (!$fxProvider) {
            return redirect()->back()->with('error', 'You are not registered as an FX Provider.');
        }
        
        // Get all pending requests and requests assigned to this provider
        $requests = ExchangeRequest::with('user')
            ->where(function($query) use ($fxProvider) {
                $query->where('status', 'pending')
                      ->orWhere('fx_provider_id', $fxProvider->id);
            })
            ->latest()
            ->paginate(20);
        
        return view('exchange-requests.index', compact('requests', 'fxProvider'));
    }

    /**
     * Show the details of a specific exchange request.
     */
    public function show($id)
    {
        $request = ExchangeRequest::with(['user', 'fxProvider'])->findOrFail($id);
        
        return view('exchange-requests.show', compact('request'));
    }

    /**
     * Accept an exchange request.
     */
    public function accept(Request $request, $id)
    {
        $exchangeRequest = ExchangeRequest::findOrFail($id);
        
        if (!$exchangeRequest->isPending()) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }
        
        $user = Auth::user();
        $fxProvider = FxProvider::where('user_id', $user->id)->first();
        
        if (!$fxProvider) {
            return redirect()->back()->with('error', 'You are not registered as an FX Provider.');
        }
        
        // Accept the request
        $exchangeRequest->accept($fxProvider->id);
        
        // Send notification to user
        $exchangeRequest->user->notify(new ExchangeRequestNotification($exchangeRequest, 'accepted'));
        
        return redirect()->route('exchange-requests.confirmation', $id)
            ->with('success', 'Exchange request accepted! Waiting for user payment confirmation.');
    }

    /**
     * Reject an exchange request.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);
        
        $exchangeRequest = ExchangeRequest::findOrFail($id);
        
        if (!$exchangeRequest->isPending()) {
            return redirect()->back()->with('error', 'This request has already been processed.');
        }
        
        // Reject the request
        $exchangeRequest->reject($validated['rejection_reason'] ?? 'Request rejected by provider');
        
        // Send notification to user
        $exchangeRequest->user->notify(new ExchangeRequestNotification($exchangeRequest, 'rejected'));
        
        return redirect()->route('exchange-requests.index')
            ->with('success', 'Exchange request rejected.');
    }

    /**
     * Show transaction confirmation page.
     */
    public function confirmation($id)
    {
        $exchangeRequest = ExchangeRequest::with(['user', 'fxProvider'])->findOrFail($id);
        
        $user = Auth::user();
        $fxProvider = FxProvider::where('user_id', $user->id)->first();
        
        // Get provider's available accounts (you can customize this based on your setup)
        $providerAccounts = $this->getProviderAccounts($fxProvider);
        
        return view('exchange-requests.confirmation', compact('exchangeRequest', 'providerAccounts'));
    }

    /**
     * User confirms payment.
     */
    public function userConfirmPayment($id)
    {
        $exchangeRequest = ExchangeRequest::findOrFail($id);
        
        if (!$exchangeRequest->isAccepted()) {
            return redirect()->back()->with('error', 'This request must be accepted first.');
        }
        
        // Confirm user payment
        $exchangeRequest->confirmUserPayment();
        
        // Send notification to FX provider
        if ($exchangeRequest->fxProvider && $exchangeRequest->fxProvider->user) {
            $exchangeRequest->fxProvider->user->notify(
                new ExchangeRequestNotification($exchangeRequest, 'user_payment_confirmed')
            );
        }
        
        return redirect()->back()->with('success', 'Payment confirmed! Waiting for provider to complete the exchange.');
    }

    /**
     * Provider confirms payment and completes the exchange.
     */
    public function providerConfirmPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'provider_source_account' => 'required|string|max:255',
        ]);
        
        $exchangeRequest = ExchangeRequest::findOrFail($id);
        
        if (!$exchangeRequest->isUserPaymentConfirmed()) {
            return redirect()->back()->with('error', 'User must confirm payment first.');
        }
        
        // Confirm provider payment and complete
        $exchangeRequest->confirmProviderPayment($validated['provider_source_account']);
        
        // Send completion notification to user
        $exchangeRequest->user->notify(new ExchangeRequestNotification($exchangeRequest, 'completed'));
        
        // Send completion notification to provider
        if ($exchangeRequest->fxProvider && $exchangeRequest->fxProvider->user) {
            $exchangeRequest->fxProvider->user->notify(
                new ExchangeRequestNotification($exchangeRequest, 'completed')
            );
        }
        
        return redirect()->route('exchange-requests.index')
            ->with('success', 'Exchange completed successfully! All parties have been notified.');
    }

    /**
     * Get provider's available accounts.
     */
    private function getProviderAccounts($fxProvider)
    {
        // This is a placeholder - customize based on your account management system
        return [
            'USD Account - Bank of America',
            'EUR Account - Deutsche Bank',
            'GBP Account - Barclays',
            'ZWL Account - CBZ Bank',
        ];
    }

    /**
     * Create a new exchange request (for users).
     */
    public function create()
    {
        // Get available FX providers
        $fxProviders = FxProvider::active()->get();
        
        return view('exchange-requests.create', compact('fxProviders'));
    }

    /**
     * Store a new exchange request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_currency' => 'required|string|max:10',
            'destination_currency' => 'required|string|max:10',
            'source_amount' => 'required|numeric|min:0',
            'user_source_account' => 'nullable|string|max:255',
            'user_destination_account' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $exchangeRequest = ExchangeRequest::create([
            'user_id' => Auth::id(),
            'source_currency' => $validated['source_currency'],
            'destination_currency' => $validated['destination_currency'],
            'source_amount' => $validated['source_amount'],
            'user_source_account' => $validated['user_source_account'] ?? null,
            'user_destination_account' => $validated['user_destination_account'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'transaction_reference' => ExchangeRequest::generateReference(),
            'status' => 'pending',
        ]);
        
        return redirect()->route('exchange-requests.user-requests')
            ->with('success', 'Exchange request created successfully! FX Providers will review your request.');
    }

    /**
     * Show user's own exchange requests.
     */
    public function userRequests()
    {
        $requests = ExchangeRequest::with('fxProvider')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);
        
        return view('exchange-requests.user-requests', compact('requests'));
    }
}
