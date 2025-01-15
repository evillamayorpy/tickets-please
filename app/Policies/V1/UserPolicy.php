<?php

namespace App\Policies\V1;

use App\Models\Ticket;
use App\Models\User;
use App\Permissions\V1\Abilities;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function store(User $user)
    {
        return  $user->tokenCan(Abilities::CreateUser);
    }

    public function replace(User $user, User $model)
    {
        return $user->tokenCan(Abilities::ReplaceUser);
    }

    public function delete(User $user, User $model)
    {
        return $user->tokenCan(Abilities::DeleteTicket);
    }

    public function update(User $user, User $model)
    {
        return  $user->tokenCan(Abilities::UpdateUser);
    }
}
