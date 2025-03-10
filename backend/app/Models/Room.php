<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
  protected static function boot()
  {
    parent::boot();

    static::deleting(function ($room) {
      $room->images()->delete();
      $room->tenant()->delete();
      $room->invitations()->delete();
    });
  }

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

  /**
   * Accessor: Get the main image filename for the room.
   * 
   * @return string
   */
  public function getMainImageUrlAttribute()
  {
    if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
      return $this->images->first()->image_path;
    }
    return 'private/images/rooms/default.jpg';
  }

  /**
   * Accessor: Get a collection of filenames for all images associated with the room.
   */
  public function getImagesUrlAttribute()
  {
    return $this->images->map(function ($image) {
      return $image->image_path;
    });
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
