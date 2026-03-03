<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'profile_picture', 'is_active', 'is_super_admin', 'phone', 'must_change_password',
        'account_type', 'business_name', 'business_registration_number', 'tax_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if user is a business account.
     */
    public function isBusinessAccount()
    {
        return $this->account_type === 'business';
    }

    /**
     * Check if user is an individual account.
     */
    public function isIndividualAccount()
    {
        return $this->account_type === 'individual';
    }

    /**
     * Check if user has access to Merchant SoftPOS.
     */
    public function canAccessMerchantSoftPOS()
    {
        return $this->isBusinessAccount();
    }

    /**
     * Check if user has FX Dealership rights.
     */
    public function hasFXDealershipRights()
    {
        return $this->isBusinessAccount();
    }

    /**
     * Check if user can use Batch Payments.
     */
    public function canUseBatchPayments()
    {
        return $this->isBusinessAccount();
    }

    /**
     * Check if user can have multiple accounts under same account.
     */
    public function canHaveMultipleAccounts()
    {
        return $this->isBusinessAccount();
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function parent()
    {
        return $this->hasOne(Parents::class);
    }
}
