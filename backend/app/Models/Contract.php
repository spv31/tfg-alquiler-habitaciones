<?php

namespace App\Models;

use Arr;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'contract_template_id',
        'property_id',
        'room_id',
        'tenant_id',
        'type',
        'price',
        'deposit',
        'utilities_included',
        'utilities_payer',
        'utilities_proportion',
        'start_date',
        'end_date',
        'extension_date',
        'status',
        'pdf_path',
        'pdf_path_signed',
        'final_content',
        'token_values',
    ];

    /**
     * Casts for date attributes and more
     * 
     * @var array
     */
    protected $casts = [
        'utilities_included' => 'boolean',
        'price' => 'float',
        'deposit' => 'float',
        'utilities_proportion' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
        'extension_date' => 'date',
        'token_values' => 'array',
    ];

    /**
     * Relationship with ContractTemplate
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<ContractTemplate, Contract>
     */
    public function contractTemplate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class);
    }

    /**
     * Relationship with Property
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Property, Contract>
     */
    public function property(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Relationship with Room
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Room, Contract>
     */
    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Relationship with user (tenant)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Contract>
     */
    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
