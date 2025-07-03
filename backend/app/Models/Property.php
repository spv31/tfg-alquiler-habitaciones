<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
  use HasFactory;

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
    if (!$this->relationLoaded('images')) {
      $this->load('images');
    }

    return $this->images->isNotEmpty()
      ? route('image.property.show', ['property' => $this->id, 'filename' => $this->images->first()->image_path])
      : null;
  }

  /**
   * Accesor: Get a Collection of filenames for all images associated with the property.
   */
  public function getImagesUrlAttribute()
  {
    return $this->images->isNotEmpty()
      ? $this->images->map(fn($image) => route('image.property.show', ['property' => $this->id, 'filename' => $image->image_path]))
      : null;
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

  /**
   *  All bills issued by the owner for this property.
   */
  public function utilityBills()
  {
    return $this->hasMany(UtilityBill::class);
  }
}
