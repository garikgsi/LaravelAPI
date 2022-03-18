<?php

namespace App\Triggers;

use App\UserInfo;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\TriggerException;


class UserInfoObserver
{
    public function saving(UserInfo $ui)
    {
        // пользователь
        $user = Auth::user();
        // проверим, не указан ли userable-объект уже в каком-нибудь пользователе
        if ($ui->userable_id > 1 && !is_null($ui->userable_type)) {
            $userable_id = $ui->userable_id;
            $userable = UserInfo::where('user_id', '<>', $ui->user_id)->whereHasMorph('userable', $ui->userable_type, function ($query) use ($userable_id) {
                $query->where('id', $userable_id);
            });
            if ($userable->count() > 0) {
                $user = $userable->first()->user;
                $msg = 'уже закреплен за пользователем ' . $user->name . '(' . $user->email . ')';
                switch ($ui->userable_type) {
                    case 'App\\Sotrudnik': {
                            $msg = 'Сотрудник ' . $msg;
                        };
                }
                throw new TriggerException($msg);
                // abort(421, $msg);
                return false;
            }
        }
    }
}