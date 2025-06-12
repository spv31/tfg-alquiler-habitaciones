<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function view(User $actor, User $user): bool
    {
        return $actor->id === $user->id
            || $actor->hasRole('admin');
    }

    public function update(User $actor, User $user): bool
    {
        return $this->view($actor, $user);
    }

    public function updateAvatar(User $actor, User $user): bool
    {
        return $this->update($actor, $user);
    }
}
