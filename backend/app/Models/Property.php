<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
  protected static function boot()
  {
    parent::boot();

    static::deleting(function ($property) {
      $property->images()->delete();
      $property->tenants()->delete();
      $property->invitations()->delete();
    });
  }

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
   * Accesor: Get the main image filename for the property.
   * 
   * @return string
   */
  public function getMainImageUrlAttribute()
  {
    if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
      return $this->images->first()->image_path;
    }
    return 'private/images/properties/default.jpg';
  }

  /**
   * Accesor: Get a Collection of filenames for all images associated with the property.
   */
  public function getImagesUrlAttribute()
  {
    return $this->images->map(function ($image) {
      return $image->image_path;
    });
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
