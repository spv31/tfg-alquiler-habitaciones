<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractTemplate extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     * 
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'content',
        'preview_path',
        'type',
        'is_default',
        'user_id',
    ];

    /**
     * Relationship with user
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, ContractTemplate>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * We return all contracts which belong to a template
     * (Not needed)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Contract, ContractTemplate>
     */
    public function contracts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
