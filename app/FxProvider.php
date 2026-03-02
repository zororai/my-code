<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FxProvider extends Model
{
    protected $fillable = [
        'user_id',
        'provider_name',
        'business_registration_number',
        'contact_email',
        'contact_phone',
        'address',
        'is_active',
        'verification_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user associated with this FX provider.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transaction intents for this provider.
     */
    public function transactionIntents()
    {
        return $this->hasMany(CrossBorderTransactionIntent::class, 'fx_provider_id');
    }

    /**
     * Get the FX offers for this provider.
     */
    public function fxOffers()
    {
        return $this->hasMany(FxOffer::class, 'user_id', 'user_id');
    }

    /**
     * Scope to get only active providers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only verified providers.
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }
}
