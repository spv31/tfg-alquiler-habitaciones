<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillShare extends Model
{
    protected $fillable = [
        'utility_bill_id',          
        'tenant_id',               
        'amount',               
        'status',                     // 'pending'|'paid'|'cancelled'
        'stripe_payment_intent_id', 
        'stripe_mandate_id', 
        'stripe_checkout_session_id',
        'paid_at',                    
    ];

    protected $casts = [
        'amount'                        => 'decimal:2',
        'paid_at'                       => 'datetime',
        'stripe_payment_intent_id'      => 'string',
        'stripe_mandate_id'             => 'string',
        'stripe_checkout_session_id'    => 'string',
    ];

    /**
     * The overall bill of which this share is a part.
     */
    public function utilityBill(): BelongsTo
    {
        return $this->belongsTo(UtilityBill::class);
    }

    /**
     * The tenant who assumes this amount.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Payment history (usually one, but you can allow several attempts).
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
