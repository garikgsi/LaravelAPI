<?php

namespace App;

use App\ABPTable;
use App\User;
use App\Sotrudnik;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Common\ABPCache;
use App\Common\ABPStorage;


class UserInfo extends ABPTable
{

    // читатели
    protected $appends = ['avatar_preview', 'permissions', 'user', 'sklad_keeper', 'userable_title'];

    public function __construct()
    {
        parent::__construct();

        $this->table('user_info');

        $this->model([
            ["name" => "user_id", "type" => "key", "title" => "user_id", "require" => true, "show_in_form" => false, "index" => "index"],
            ["name" => "phone", "type" => "phone", "title" => "Телефон", "require" => false, "out_index" => 2],
            ["name" => "avatar", "type" => "image", "title" => "Аватар", "require" => false, "out_index" => 3, "size" => 4],
            ["name" => "userable", "title" => "Указать в качестве", "type" => "morph", "tables" => [["table" => "sotrudniks", "title" => "Сотрудника", "type" => "App\\Sotrudnik"]], "require" => false, "out_index" => 4, "size" => 4],
        ]);
    }

    // пользователь
    public function user_()
    {
        return $this->belongsTo('App\User');
    }
    // public function ui() {
    //     return Auth::user()->ui;
    // }

    // полиморфная связь с сотрудником или клиентом
    public function userable()
    {
        return $this->morphTo();
    }

    // сотрудник
    public function sotrudnik()
    {
        if (isset($this->attributes['userable_id']) && $this->attributes['userable_id'] > 0 && isset($this->attributes['userable_type'])) {
            $sotrudnik = $this->userable;
            if ($sotrudnik) {
                if ($sotrudnik instanceof Sotrudnik) {
                    return $sotrudnik;
                }
            }
        }
        return null;
    }

    // администратор?
    public function is_admin()
    {
        $user = Auth::user();
        return $user->hasRole('admin');
    }

    // читатели
    // разрешения
    public function getPermissionsAttribute()
    {
        $p = [
            "show" => 0,
            "edit" => 0,
            "copy" => 0,
            "delete" => 0
        ];
        if (isset($this->user_id)) {
            $user = Auth::user();
            if ($user) {
                if ($this->user_id == $user->id) {
                    $p["show"] = 1;
                    $p["edit"] = 1;
                }

                if ($user->hasRole('admin')) {
                    $p["show"] = 1;
                    $p["edit"] = 1;
                    $p["copy"] = 1;
                    $p["delete"] = 1;
                }
            }
        }
        return $p;
    }

    // значение полиморфной таблицы
    public function getUserableTitleAttribute()
    {
        if (isset($this->attributes["userable_type"]) && $this->attributes['userable_id'] > 0  && isset($this->attributes["userable_id"])) {
            $model_table = $this->attributes["userable_type"];
            $morphModel = new $model_table();
            return ABPCache::get_select_list($morphModel->table(), $this->attributes["userable_id"]);
        }
        return null;
    }

    // аватар
    public function getAvatarAttribute($filename)
    {
        if (Storage::disk('local')->exists($filename)) {
            return asset(Storage::disk('local')->url($filename));
        }
        return $filename;
    }
    public function getAvatarPreviewAttribute()
    {
        return str_replace("/image/", "/thumbs/", $this->avatar);
        // if (isset($this->attributes['avatar'])) {
        //     // $disk = new ABPStorage('local');
        //     $value = $this->attributes['avatar'];
        //     if (Storage::disk('local')->exists("thumbs/" . $value)) {
        //         // return Storage::disk('local')->url("storage/thumbs/".$value);
        //         return asset("storage/thumbs/" . $value);
        //     }
        // }
        // return null;
    }
    // пользователь
    public function getUserAttribute()
    {
        if (isset($this->attributes['user_id'])) {
            $this_user = User::where('id', $this->attributes['user_id'])->first();
            if ($this_user) {
                return $this_user;
            }
        }
        return null;
    }
    // массив складов, где этот юзер - кладовщик
    public function getSkladKeeperAttribute()
    {
        $this_user_info = clone $this;
        $sotrudnik = $this_user_info->sotrudnik();
        // если это вообще сотрудник?
        if ($sotrudnik) {
            $collection_sklad = $sotrudnik->store_keeper()->get();
            if ($collection_sklad) {
                return $collection_sklad->pluck('id')->all();
            }
        }
        return null;
    }


    // политики прав доступа
    // просмотр|редактирование только себя или администраторам любого пользователя
    public function policy_show_edit_user(User $current_user, User $user)
    {
        if ($current_user->hasRole('admin')) {
            return true;
        } else {
            if ($current_user->id == $user->id) {
                return true;
            }
        }
        return false;
    }
}