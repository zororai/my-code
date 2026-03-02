<?php

namespace App\Http\Controllers;

use App\FxOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FxOfferController extends Controller
{
    /**
     * Display a listing of the FX offers (Marketplace).
     */
    public function marketplace()
    {
        $offers = FxOffer::with('user')
            ->active()
            ->latest()
            ->paginate(20);

        return view('fx.marketplace', compact('offers'));
    }

    /**
     * Show the form for creating a new FX offer.
     */
    public function create()
    {
        // Check if user has FX Provider role
        if (!Auth::user()->hasRole(['Zimbabwe Currency Exchange (ZimFX)', 'Global FX Solutions'])) {
            abort(403, 'Unauthorized access. Only FX Providers can create offers.');
        }

        return view('fx.create-offer');
    }

    /**
     * Store a newly created FX offer in storage.
     */
    public function store(Request $request)
    {
        // Check if user has FX Provider role
        if (!Auth::user()->hasRole(['Zimbabwe Currency Exchange (ZimFX)', 'Global FX Solutions'])) {
            abort(403, 'Unauthorized access. Only FX Providers can create offers.');
        }

        $validated = $request->validate([
            'source_accounts' => 'required|array|min:1',
            'source_accounts.*' => 'required|string',
            'destination_accounts' => 'required|array|min:1',
            'destination_accounts.*' => 'required|string',
            'buy_rate' => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
            'settlement_methods' => 'required|array|min:1',
            'min_trade_value' => 'required|numeric|min:0',
            'max_trade_value' => 'required|numeric|min:0|gte:min_trade_value',
            'available_amounts' => 'required|array|min:1',
            'available_amounts.*.amount' => 'required|numeric|min:0',
            'available_amounts.*.currency' => 'required|string',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i|after:open_time',
            'trading_currencies' => 'required|array|min:1',
            'trading_currencies.*' => 'required|string',
            'processing_fee_percentage' => 'required|numeric|min:0|max:100',
        ]);

        // Get provider name from user's role
        $providerName = Auth::user()->getRoleNames()->first();

        $offer = FxOffer::create([
            'user_id' => Auth::id(),
            'provider_name' => $providerName,
            'source_accounts' => $validated['source_accounts'],
            'destination_accounts' => $validated['destination_accounts'],
            'buy_rate' => $validated['buy_rate'],
            'sell_rate' => $validated['sell_rate'],
            'settlement_methods' => $validated['settlement_methods'],
            'min_trade_value' => $validated['min_trade_value'],
            'max_trade_value' => $validated['max_trade_value'],
            'available_amounts' => $validated['available_amounts'],
            'open_time' => $validated['open_time'],
            'close_time' => $validated['close_time'],
            'trading_currencies' => $validated['trading_currencies'],
            'processing_fee_percentage' => $validated['processing_fee_percentage'],
            'status' => 'active',
        ]);

        return redirect()->route('fx.marketplace')
            ->with('success', 'FX Offer created successfully and is now visible on the marketplace!');
    }

    /**
     * Display the specified FX offer.
     */
    public function show($id)
    {
        $offer = FxOffer::with('user')->findOrFail($id);
        return view('fx.show', compact('offer'));
    }

    /**
     * Show the form for editing the specified FX offer.
     */
    public function edit($id)
    {
        $offer = FxOffer::where('user_id', Auth::id())->findOrFail($id);
        return view('fx.edit-offer', compact('offer'));
    }

    /**
     * Update the specified FX offer in storage.
     */
    public function update(Request $request, $id)
    {
        $offer = FxOffer::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'source_accounts' => 'required|array|min:1',
            'source_accounts.*' => 'required|string',
            'destination_accounts' => 'required|array|min:1',
            'destination_accounts.*' => 'required|string',
            'buy_rate' => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
            'settlement_methods' => 'required|array|min:1',
            'min_trade_value' => 'required|numeric|min:0',
            'max_trade_value' => 'required|numeric|min:0|gte:min_trade_value',
            'available_amounts' => 'required|array|min:1',
            'available_amounts.*.amount' => 'required|numeric|min:0',
            'available_amounts.*.currency' => 'required|string',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i|after:open_time',
            'trading_currencies' => 'required|array|min:1',
            'trading_currencies.*' => 'required|string',
            'processing_fee_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $offer->update($validated);

        return redirect()->route('fx.marketplace')
            ->with('success', 'FX Offer updated successfully!');
    }

    /**
     * Remove the specified FX offer from storage.
     */
    public function destroy($id)
    {
        $offer = FxOffer::where('user_id', Auth::id())->findOrFail($id);
        $offer->delete();

        return redirect()->route('fx.marketplace')
            ->with('success', 'FX Offer deleted successfully!');
    }

    /**
     * Display FX Provider's own offers.
     */
    public function myOffers()
    {
        $offers = FxOffer::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('fx.my-offers', compact('offers'));
    }
}
