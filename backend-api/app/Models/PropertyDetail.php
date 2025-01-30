<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyDetail extends Model
{
    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $fillable = [
        'property_id',
        'purchase_price',
        'is_financed',
        'mortgage_cost',
        'purchase_taxes',
        'renovation_cost',
        'furniture_cost',
        'purchase_date',
        'estimated_value',
        'annual_insurance_cost',
        'annual_property_tax',
        'annual_community_fees',
        'annual_waste_tax',
        'income_tax_percentage',
        'annual_repair_percentage',
        'rental_price',
        'property_size',
    ];

    /**
     * Relationship with Property
     *
     * @return void
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
