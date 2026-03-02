<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FxOffer extends Model
{
    protected $fillable = [
        'user_id',
        'provider_name',
        'source_accounts',
        'destination_accounts',
        'buy_rate',
        'sell_rate',
        'settlement_methods',
        'min_trade_value',
        'max_trade_value',
        'available_amounts',
        'open_time',
        'close_time',
        'trading_currencies',
        'processing_fee_percentage',
        'status'
    ];

    protected $casts = [
        'source_accounts' => 'array',
        'destination_accounts' => 'array',
        'settlement_methods' => 'array',
        'available_amounts' => 'array',
        'trading_currencies' => 'array',
        'buy_rate' => 'decimal:6',
        'sell_rate' => 'decimal:6',
        'min_trade_value' => 'decimal:2',
        'max_trade_value' => 'decimal:2',
        'processing_fee_percentage' => 'decimal:2',
    ];

    /**
     * Get the user that owns the FX offer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the offer is currently active based on trading hours.
     */
    public function isWithinTradingHours()
    {
        $currentTime = now()->format('H:i:s');
        return $currentTime >= $this->open_time && $currentTime <= $this->close_time;
    }

    /**
     * Scope to get only active offers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get offers within trading hours.
     */
    public function scopeWithinTradingHours($query)
    {
        $currentTime = now()->format('H:i:s');
        return $query->whereTime('open_time', '<=', $currentTime)
                     ->whereTime('close_time', '>=', $currentTime);
    }
}
