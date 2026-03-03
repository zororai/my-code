# Account Types & Access Control Guide

## Overview
The system supports two types of user accounts with different service access levels:

1. **Individual Users** - Personal accounts with standard services
2. **Business Users** - Corporate accounts with full service package

---

## Individual Users

### Available Services
✅ All standard PANĒTA services including:
- Personal Banking
- Send Money (Domestic & Cross-Border)
- Linked Accounts
- Currency Exchange
- Wealth & Investments
- Trading Account Connections
- Payment Requests
- QR Payments
- Payment Links

### Restricted Services
❌ Individual users **CANNOT** access:
1. **Merchant SoftPOS** - Point of sale terminal functionality
2. **FX Dealership Rights** - Foreign exchange provider/dealer capabilities
3. **Batch Payments** - Bulk payment processing
4. **Multiple Accounts** - Cannot have multiple sub-accounts under same account

---

## Business Users

### Available Services
✅ **Full Package** - All services including:
- All Individual User services (listed above)
- **Merchant SoftPOS** - Accept payments via mobile POS
- **FX Dealership Rights** - Operate as FX provider
- **Batch Payments** - Process multiple payments at once
- **Multiple Accounts** - Create and manage sub-accounts

---

## Implementation Details

### Database Schema

**Users Table Fields:**
```php
- account_type: enum('individual', 'business') DEFAULT 'individual'
- business_name: string (nullable)
- business_registration_number: string (nullable)
- tax_id: string (nullable)
```

### User Model Methods

```php
// Check account type
$user->isBusinessAccount()      // Returns true if business
$user->isIndividualAccount()    // Returns true if individual

// Check feature access
$user->canAccessMerchantSoftPOS()   // Business only
$user->hasFXDealershipRights()      // Business only
$user->canUseBatchPayments()        // Business only
$user->canHaveMultipleAccounts()    // Business only
```

### Middleware

**RequireBusinessAccount Middleware:**
- Protects business-only routes
- Returns 403 error for individual users
- Provides upgrade message

### Route Protection

**Apply to business-only routes:**
```php
Route::middleware(['auth', 'business.account'])->group(function () {
    // Merchant SoftPOS routes
    Route::get('/softpos/dashboard', 'SoftPOSController@dashboard');
    
    // Batch Payments routes
    Route::get('/batch-payments', 'BatchPaymentController@index');
    
    // FX Dealership routes (if not using role-based)
    Route::get('/fx-dealer/dashboard', 'FXDealerController@dashboard');
});
```

### UI/Blade Directives

**Hide features for individual users:**
```blade
@if(auth()->user()->canAccessMerchantSoftPOS())
    <!-- Show Merchant SoftPOS menu/button -->
@endif

@if(auth()->user()->canUseBatchPayments())
    <!-- Show Batch Payments option -->
@endif

@if(auth()->user()->hasFXDealershipRights())
    <!-- Show FX Dealership features -->
@endif

@if(auth()->user()->canHaveMultipleAccounts())
    <!-- Show Add Sub-Account button -->
@endif
```

---

## Upgrade Path

### Individual → Business Upgrade

Users can upgrade from Individual to Business account by:
1. Providing business information
2. Submitting business registration documents
3. Verification by admin
4. Account type changed to 'business'

**Upgrade Form Fields:**
- Business Name
- Business Registration Number
- Tax ID
- Business Type
- Industry
- Supporting Documents

---

## Registration Flow

### New User Registration

**Step 1: Account Type Selection**
- Individual Account (Default)
- Business Account

**Step 2: Personal Information**
- Name
- Email
- Phone
- Password

**Step 3: Business Information (if Business selected)**
- Business Name
- Registration Number
- Tax ID
- Business Address

**Step 4: Verification**
- Email verification
- Document verification (for business)
- Account activation

---

## Migration Instructions

**Run migration:**
```bash
php artisan migrate
```

**Register middleware in `app/Http/Kernel.php`:**
```php
protected $routeMiddleware = [
    // ... existing middleware
    'business.account' => \App\Http\Middleware\RequireBusinessAccount::class,
];
```

**Update existing users (optional):**
```php
// Set all existing users to individual by default
DB::table('users')->update(['account_type' => 'individual']);

// Manually set business accounts
DB::table('users')
    ->whereIn('email', ['business@example.com'])
    ->update(['account_type' => 'business']);
```

---

## Testing

### Test Individual User Access
1. Login as individual user
2. Attempt to access `/softpos/dashboard` → Should get 403
3. Attempt to access batch payments → Should get 403
4. Verify UI hides business-only features

### Test Business User Access
1. Login as business user
2. Access `/softpos/dashboard` → Should work
3. Access batch payments → Should work
4. Verify all features visible in UI

---

## Feature Matrix

| Feature | Individual | Business |
|---------|-----------|----------|
| Personal Banking | ✅ | ✅ |
| Send Money | ✅ | ✅ |
| Currency Exchange | ✅ | ✅ |
| Wealth & Investments | ✅ | ✅ |
| Trading Accounts | ✅ | ✅ |
| Payment Requests | ✅ | ✅ |
| QR Payments | ✅ | ✅ |
| Payment Links | ✅ | ✅ |
| **Merchant SoftPOS** | ❌ | ✅ |
| **FX Dealership** | ❌ | ✅ |
| **Batch Payments** | ❌ | ✅ |
| **Multiple Accounts** | ❌ | ✅ |

---

## Support & Troubleshooting

### Common Issues

**Issue:** Individual user sees business features
- **Solution:** Check middleware is applied to routes
- Verify UI conditionals are implemented

**Issue:** Business user cannot access features
- **Solution:** Verify `account_type` is set to 'business'
- Check middleware is registered in Kernel.php

**Issue:** Upgrade not working
- **Solution:** Ensure migration has run
- Verify business information is saved correctly
