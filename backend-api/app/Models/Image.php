<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'image_path',
    ];

    /**
     * Polymorphic Relationship: Image could be realted to Property or Room
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
