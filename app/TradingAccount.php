<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TradingAccount extends Model
{
    protected $fillable = [
        'user_id',
        'account_type',
        'country',
        'asset_type',
        'financial_market',
        'broker_name',
        'broker_code',
        'broker_logo_url',
        'account_holder_name',
        'trading_account_number',
        'account_status',
        'connection_type',
        'is_connected',
        'connected_at',
        'last_synced_at',
        'total_value',
        'currency',
        'holdings',
        'api_key',
        'api_secret',
        'api_credentials',
        'terms_accepted',
        'terms_accepted_at',
        'metadata',
    ];

    protected $casts = [
        'is_connected' => 'boolean',
        'terms_accepted' => 'boolean',
        'connected_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'total_value' => 'decimal:2',
        'holdings' => 'array',
        'api_credentials' => 'array',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
        'api_credentials',
    ];

    /**
     * Get the user that owns the trading account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get masked account number.
     */
    public function getMaskedAccountNumber()
    {
        return \App\Helpers\AccountHelper::maskAccountNumber($this->trading_account_number);
    }

    /**
     * Check if account is active.
     */
    public function isActive()
    {
        return $this->account_status === 'active' && $this->is_connected;
    }

    /**
     * Get account type label.
     */
    public function getAccountTypeLabel()
    {
        $labels = [
            'stock_broker' => 'Stock Broker',
            'crypto_exchange' => 'Crypto Exchange',
            'investment_platform' => 'Investment Platform',
        ];

        return $labels[$this->account_type] ?? 'Unknown';
    }

    /**
     * Sync account data.
     */
    public function syncData()
    {
        // This would integrate with broker APIs
        $this->update(['last_synced_at' => now()]);
    }
}
