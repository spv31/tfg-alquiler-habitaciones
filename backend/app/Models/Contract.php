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
        'iban',
        'pdf_path_signed',
        'final_content',
        'token_values',
        'pdf_path_signed_owner',
        'pdf_path_signed_tenant',
        'signed_by_owner_at',
        'signed_by_tenant_at',
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
        'iban' => 'string',
        'token_values' => 'array',
        'signed_by_owner_at'  => 'datetime',
        'signed_by_tenant_at' => 'datetime',
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

    /**
     * RentPayments related
     *
     * @return void
     */
    public function rentPayments()          
    {
        return $this->hasMany(RentPayment::class);
    }
}
