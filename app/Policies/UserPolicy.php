<?php

namespace App\Policies;

use App\User;
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

    // просмотр списка политик пользователя
    // public function view_user_roles(User $user)
    // {
    //     if ($user->hasRole('admin')) {
    //         return true;
    //     } else {
    //         if ($user->id==$user_info->user_id) {
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    // устанавливаем список политик пользователя
    public function set_user_roles(User $user)
    {
        // dd($user);
        // return true;
        if ($user->hasRole('super admin')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        if ($user->hasRole('super admin')) {
            // dd('im super admin');
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\UserInfo  $userInfo
     * @return mixed
     */
    public function view(User $user, User $user_info)
    {
        //
        if ($user->hasRole('super admin')) {
            return true;
        } else {
            if ($user->id==$user_info->id) {
                return true;
            }
        }
        return false;
    }
}
