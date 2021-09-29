<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        'App\User' => 'App\Policies\UserPolicy',
        'App\UserInfo' => 'App\Policies\UserInfoPolicy',
        'App\Sklad' => 'App\Policies\SkladPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Passport::routes();

        // полные права для пользователей имеющих роль 'super admin'
        Gate::before(function ($user) {
            return $user->hasRole('super admin') ? true : null;
        });

        // разрешаем смотреть и изменять данные пользователя только ему самому и админам
        Gate::define('show-user', 'App\UserInfo@policy_show_edit_user');
        Gate::define('edit-user', 'App\UserInfo@policy_show_edit_user');

        // политики по умолчанию - не описанные в классах моделей
        Gate::define('viewAny', function () {
            return true;
        });
        Gate::define('view', function () {
            return true;
        });
        Gate::define('create', function () {
            return true;
        });
        Gate::define('update', function () {
            return true;
        });
        Gate::define('delete', function () {
            return true;
        });
        Gate::define('restore', function () {
            return true;
        });
        Gate::define('forceDelete', function (User $user) {
            return $user->hasRole('admin');
        });
    }
}