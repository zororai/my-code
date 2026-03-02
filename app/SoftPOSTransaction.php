<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SoftPOSTransaction extends Model
{
    protected $table = 'softpos_transactions';

    protected $fillable = [
        'merchant_terminal_id',
        'customer_id',
        'transaction_id',
        'reference_number',
        'amount',
        'currency',
        'payment_method',
        'payment_provider',
        'card_type',
        'card_last_four',
        'card_brand',
        'mobile_number',
        'mobile_network',
        'status',
        'status_message',
        'authorization_code',
        'merchant_fee',
        'processing_fee',
        'net_amount',
        'customer_name',
        'customer_email',
        'customer_phone',
        'receipt_data',
        'receipt_number',
        'receipt_sent',
        'latitude',
        'longitude',
        'processed_at',
        'settled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'merchant_fee' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'receipt_sent' => 'boolean',
        'processed_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    /**
     * Get the merchant terminal that owns the transaction.
     */
    public function merchantTerminal()
    {
        return $this->belongsTo(MerchantTerminal::class);
    }

    /**
     * Get the customer that made the transaction.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Check if transaction is successful.
     */
    public function isSuccessful()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending()
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if transaction failed.
     */
    public function isFailed()
    {
        return in_array($this->status, ['declined', 'failed', 'cancelled']);
    }

    /**
     * Generate receipt number.
     */
    public static function generateReceiptNumber()
    {
        return 'RCP-' . strtoupper(uniqid());
    }

    /**
     * Generate transaction ID.
     */
    public static function generateTransactionId()
    {
        return 'TXN-' . strtoupper(uniqid());
    }

    /**
     * Generate reference number.
     */
    public static function generateReferenceNumber()
    {
        return 'REF-' . time() . '-' . rand(1000, 9999);
    }

    /**
     * Mask card number for display.
     */
    public function getMaskedCardNumber()
    {
        return $this->card_last_four ? '****' . $this->card_last_four : null;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColor()
    {
        switch ($this->status) {
            case 'approved':
                return 'green';
            case 'pending':
            case 'processing':
                return 'yellow';
            case 'declined':
            case 'failed':
            case 'cancelled':
                return 'red';
            case 'refunded':
                return 'blue';
            default:
                return 'gray';
        }
    }
}
