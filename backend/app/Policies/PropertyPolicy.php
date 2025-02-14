<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PropertyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Property $property): bool
    {
        return $user->id === $property->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Property $property): bool
    {
        return $user->id === $property->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Property $property): bool
    {
        return $user->id === $property->user_id;
    }

    /**
     * Determine whether the user can store a room
     * 
     * @param \App\Models\User $user
     * @param \App\Models\Property $property
     * @return bool
     */
    public function storeRoom(User $user, Property $property): bool
    {
        return $user->id === $property->user_id;
    }
    
    /**
     * Determine whether the user can update a room 
     * 
     * @param \App\Models\User $user
     * @param \App\Models\Property $property
     * @return bool
     */
    public function updateRoom(User $user, Property $property): bool
    {
        return $user->id === $property->user_id;
    }
}
