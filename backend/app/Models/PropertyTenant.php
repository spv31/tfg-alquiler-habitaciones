<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyTenant extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rentable_id',
        'rentable_type',
        'tenant_id',
    ];

    /**
     * Polymorphic Relationship: Tenant could be realted to Property or Room
     *
     * @return void
     */
    public function rentable()
    {
        return $this->morphTo();
    }

    /**
     * Tenant who rents Property or Room
     *
     * @return void
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }
}
