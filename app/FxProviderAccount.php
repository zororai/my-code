<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FxProviderAccount extends Model
{
    protected $fillable = [
        'fx_provider_id',
        'account_name',
        'account_number',
        'currency',
        'bank_name',
        'account_type',
        'current_balance',
        'available_balance',
        'reserved_balance',
        'daily_limit',
        'monthly_limit',
        'is_active',
        'is_primary',
        'description',
        'metadata',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'reserved_balance' => 'decimal:2',
        'daily_limit' => 'decimal:2',
        'monthly_limit' => 'decimal:2',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the FX provider that owns this account.
     */
    public function fxProvider()
    {
        return $this->belongsTo(FxProvider::class, 'fx_provider_id');
    }

    /**
     * Scope to get only active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get accounts by currency.
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    /**
     * Scope to get primary account.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Update account balance.
     */
    public function updateBalance($amount, $type = 'current')
    {
        $field = $type . '_balance';
        $this->increment($field, $amount);
    }

    /**
     * Reserve funds.
     */
    public function reserveFunds($amount)
    {
        if ($this->available_balance >= $amount) {
            $this->decrement('available_balance', $amount);
            $this->increment('reserved_balance', $amount);
            return true;
        }
        return false;
    }

    /**
     * Release reserved funds.
     */
    public function releaseReservedFunds($amount)
    {
        $this->decrement('reserved_balance', $amount);
        $this->increment('available_balance', $amount);
    }

    /**
     * Get formatted balance.
     */
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->current_balance, 2) . ' ' . $this->currency;
    }
}
