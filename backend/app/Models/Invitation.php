<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'rentable_id',
        'rentable_type',
        'owner_id',
        'status',
    ];

    public function scopeExpireOld($query)
    {
        return $query->where('status', 'pending')
            ->where('created_at', '<', Carbon::now()->subDays(7));
    }

    /**
     * Polymorphic Relationship: Invitation could be related to Property or Room
     *
     * @return void
     */
    public function rentable()
    {
        return $this->morphTo();
    }

    /**
     * Relationship with User who sent invitation
     *
     * @return void
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
