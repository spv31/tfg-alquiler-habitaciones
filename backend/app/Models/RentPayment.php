<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentPayment extends Model
{
    protected $fillable = [
        'contract_id',
        'period_start',
        'period_end',
        'due_date',
        'amount',
        'status',
        'stripe_payment_intent_id',
        'stripe_mandate_id',
        'paid_at'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    /**
     * Contract associated to payment
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Payments associated to same rent payment
     */
    public function payments(): HasMany     
    {
        return $this->hasMany(Payment::class);
    }
}
