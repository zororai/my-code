<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MerchantTerminal extends Model
{
    protected $fillable = [
        'user_id',
        'terminal_id',
        'terminal_name',
        'business_name',
        'business_type',
        'merchant_category_code',
        'device_type',
        'device_model',
        'os_version',
        'app_version',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'is_active',
        'is_verified',
        'verified_at',
        'last_active_at',
        'daily_limit',
        'transaction_limit',
        'daily_processed',
        'total_transactions',
        'total_volume',
        'accepted_payment_methods',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'last_active_at' => 'datetime',
        'daily_limit' => 'decimal:2',
        'transaction_limit' => 'decimal:2',
        'daily_processed' => 'decimal:2',
        'total_volume' => 'decimal:2',
        'accepted_payment_methods' => 'array',
        'settings' => 'array',
    ];

    /**
     * Get the user that owns the terminal.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for this terminal.
     */
    public function transactions()
    {
        return $this->hasMany(SoftPOSTransaction::class);
    }

    /**
     * Get today's transactions.
     */
    public function todayTransactions()
    {
        return $this->transactions()
            ->whereDate('created_at', today())
            ->where('status', 'approved');
    }

    /**
     * Get successful transactions.
     */
    public function successfulTransactions()
    {
        return $this->transactions()->where('status', 'approved');
    }

    /**
     * Check if daily limit is reached.
     */
    public function isDailyLimitReached()
    {
        return $this->daily_processed >= $this->daily_limit;
    }

    /**
     * Check if transaction exceeds limit.
     */
    public function exceedsTransactionLimit($amount)
    {
        return $amount > $this->transaction_limit;
    }

    /**
     * Reset daily processed amount (should be run daily).
     */
    public function resetDailyProcessed()
    {
        $this->update(['daily_processed' => 0]);
    }

    /**
     * Update terminal statistics.
     */
    public function updateStatistics($amount)
    {
        $this->increment('total_transactions');
        $this->increment('total_volume', $amount);
        $this->increment('daily_processed', $amount);
        $this->update(['last_active_at' => now()]);
    }
}
