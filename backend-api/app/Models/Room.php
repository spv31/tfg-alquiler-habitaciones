<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property_id',
        'room_number',
        'description',
        'rental_price',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function tenant()
    {
        return $this->morphOne(PropertyTenant::class, 'rentable');
    }

    public function invitations()
    {
        return $this->morphMany(Invitation::class, 'rentable');
    }
}
