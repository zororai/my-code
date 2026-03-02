<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExchangeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'fx_provider_id',
        'source_currency',
        'destination_currency',
        'source_amount',
        'destination_amount',
        'exchange_rate',
        'processing_fee',
        'provider_fee',
        'total_fees',
        'user_source_account',
        'user_destination_account',
        'provider_source_account',
        'provider_destination_account',
        'status',
        'transaction_reference',
        'user_payment_confirmed_at',
        'provider_payment_confirmed_at',
        'accepted_at',
        'rejected_at',
        'completed_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'source_amount' => 'decimal:2',
        'destination_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'processing_fee' => 'decimal:2',
        'provider_fee' => 'decimal:2',
        'total_fees' => 'decimal:2',
        'user_payment_confirmed_at' => 'datetime',
        'provider_payment_confirmed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that created the exchange request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the FX provider handling this request.
     */
    public function fxProvider()
    {
        return $this->belongsTo(FxProvider::class, 'fx_provider_id');
    }

    /**
     * Generate a unique transaction reference.
     */
    public static function generateReference()
    {
        return 'EXR-' . strtoupper(uniqid()) . '-' . time();
    }

    /**
     * Scope to get pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get accepted requests.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope to get completed requests.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get requests for a specific provider.
     */
    public function scopeForProvider($query, $providerId)
    {
        return $query->where('fx_provider_id', $providerId);
    }

    /**
     * Scope to get requests for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if request is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is accepted.
     */
    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if user has confirmed payment.
     */
    public function isUserPaymentConfirmed()
    {
        return $this->status === 'user_payment_confirmed';
    }

    /**
     * Check if provider has confirmed payment.
     */
    public function isProviderPaymentConfirmed()
    {
        return $this->status === 'provider_payment_confirmed';
    }

    /**
     * Check if request is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Accept the exchange request.
     */
    public function accept($providerId)
    {
        $this->update([
            'fx_provider_id' => $providerId,
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Reject the exchange request.
     */
    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Confirm user payment.
     */
    public function confirmUserPayment()
    {
        $this->update([
            'status' => 'user_payment_confirmed',
            'user_payment_confirmed_at' => now(),
        ]);
    }

    /**
     * Confirm provider payment.
     */
    public function confirmProviderPayment($providerSourceAccount)
    {
        $this->update([
            'status' => 'provider_payment_confirmed',
            'provider_payment_confirmed_at' => now(),
            'provider_source_account' => $providerSourceAccount,
            'completed_at' => now(),
        ]);
        
        // Mark as completed after provider confirms
        $this->update(['status' => 'completed']);
    }
}
