<?php

namespace App\Policies;

use App\User;
use App\Sklad;
use Illuminate\Auth\Access\HandlesAuthorization;

class SkladPolicy
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

    public function create(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return false;
    }

    public function update(User $user, Sklad $sklad)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return false;
    }

    public function delete(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return false;
    }


}
