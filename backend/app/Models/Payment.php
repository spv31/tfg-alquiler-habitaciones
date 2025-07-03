<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_share_id',
        'rent_payment_id',
        'amount',
        'method',
        'stripe_payment_intent_id',
        'paid_at',
    ];

    protected $casts = [
        'amount'                      => 'decimal:2',
        'paid_at'                     => 'datetime',
        'stripe_payment_intent_id'    => 'string',
    ];

    /**
     *  The piece of bill being paid.
     */
    public function billShare(): BelongsTo
    {
        return $this->belongsTo(BillShare::class);
    }

    /**
     *  Payment associated to rent
     */
    public function rentPayment(): BelongsTo
    {
        return $this->belongsTo(RentPayment::class);
    }
}
