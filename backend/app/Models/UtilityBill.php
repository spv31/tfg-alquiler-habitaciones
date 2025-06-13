<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilityBill extends Model
{
    protected $fillable = [
        'property_id',     
        'room_id', 
        'owner_id',      
        'issue_date',       
        'due_date',     
        'period_start',
        'period_end',   
        'total_amount',   
        'category',         // 'utility'|'general'|'tax'
        'description',      
        'attachment_path', 
        'status',           // 'pending'|'split'|'settled'
    ];

    protected $casts = [
        'issue_date'    => 'date',
        'due_date'      => 'date',
        'period_start'  => 'date',
        'period_end'    => 'date',
        'total_amount'  => 'decimal:2',
        'status'        => 'string',
    ];

    /**
     * Property associated
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Room associated (optional)
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);   
    }

    /**
     * Owner who originated bill
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * All BillShares distributed among tenants
     */
    public function billShares(): HasMany
    {
        return $this->hasMany(BillShare::class);
    }
}
