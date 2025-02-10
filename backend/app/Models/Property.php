<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'address',
        'cadastral_reference',
        'description',
        'rental_type',
        'status',
        'total_rooms',
    ];

    /**
     * Relationship with Owner 
     *
     * @return void
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship with Property Details
     *
     * @return void
     */
    public function details()
    {
        return $this->hasOne(PropertyDetail::class);
    }

    /**
     * Relationship with Room
     *
     * @return void
     */
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Polymorphic relationship with Images
     *
     * @return void
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Polymorphic relationship with Invitation
     *
     * @return void
     */
    public function invitations()
    {
        return $this->morphMany(Invitation::class, 'rentable');
    }
    
    /**
     * Polymorphic relationship with PropertyTenant
     *
     * @return void
     */
    public function tenants()
    {
        return $this->morphMany(PropertyTenant::class, 'rentable');
    }

}
