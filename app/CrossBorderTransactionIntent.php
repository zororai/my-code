<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrossBorderTransactionIntent extends Model
{
    protected $fillable = [
        'user_id',
        'fx_provider_id',
        'source_currency',
        'destination_currency',
        'source_amount',
        'destination_amount',
        'exchange_rate',
        'status',
        'transaction_reference',
        'settlement_method',
        'processing_fee',
    ];

    protected $casts = [
        'source_amount' => 'decimal:2',
        'destination_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'processing_fee' => 'decimal:2',
    ];

    /**
     * Get the user that owns the transaction intent.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the FX provider for this transaction.
     */
    public function fxProvider()
    {
        return $this->belongsTo(FxProvider::class, 'fx_provider_id');
    }

    /**
     * Scope to get only executed transactions.
     */
    public function scopeExecuted($query)
    {
        return $query->where('status', 'executed');
    }

    /**
     * Scope to get transactions for a specific provider.
     */
    public function scopeForProvider($query, $providerId)
    {
        return $query->where('fx_provider_id', $providerId);
    }
}
