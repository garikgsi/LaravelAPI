<?php

namespace App;

use App\ABPTable;
use App\Common\ABPCache;
use App\NotificationType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


class Notification extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('notifications');

        $this->model([
            ["name"=>"text","type"=>"string","title"=>"Текст","require"=>true,"index"=>"index","show_in_table"=>false,"show_in_form"=>true],
            ["name"=>"user_id","type"=>"select","table"=>"users","table_class"=>"User","title"=>"Получатель","require"=>true,"default"=>1,"index"=>"index","show_in_table"=>true,"show_in_form"=>true],
            ["name"=>"notification_type_id","type"=>"select","table"=>"notification_types","table_class"=>"NotificationType","title"=>"Вид уведомления","require"=>true,"default"=>1,"index"=>"index","show_in_table"=>true,"show_in_form"=>true],
            ["name"=>"is_readed","type"=>"boolean","title"=>"Прочтено","require"=>0,"index"=>"index","show_in_table"=>true,"show_in_form"=>true],
            ["name"=>"documentable", "title"=>"Основание документа", "type"=>"morph", "tables"=>[["table"=>"sklad_move", "title"=>"Перемещение", "type"=>"App\\SkladMove"],["table"=>"sklad_receive", "title"=>"Поступление", "type"=>"App\\SkladReceive"]],"require"=>false],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends,['documentable_title','notification_type','color','link']);

    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('current_user', function(Builder $builder) {
            if (Auth::hasUser()) {
                $user_id = Auth::user()->id;
                $builder->where('user_id', $user_id);
            }
        });
    }

    // полиморфная связь с сотрудником или клиентом
    public function documentable()
    {
        return $this->morphTo();
    }

    // тип уведомления
    public function n_type() {
        return $this->belongsTo('App\NotificationType');
    }

    // читатели
    // значение полиморфной таблицы
    public function getDocumentableTitleAttribute()
    {
        if (isset($this->attributes["documentable_type"]) && $this->attributes['documentable_id']>0  && isset($this->attributes["documentable_id"])) {
            $model_table = $this->attributes["documentable_type"];
            $morphModel = new $model_table();
            return ABPCache::get_select_list($morphModel->table(),$this->attributes["documentable_id"]);
        }
        return null;
    }

    // тип уведомления
    public function getNotificationTypeAttribute() {
        if (isset($this->attributes['notification_type_id'])) {
            $value = $this->attributes['notification_type_id'];
            return ABPCache::get_select_list('notification_types',$value);
        }
    }

    // цвет
    public function getColorAttribute() {
        if (isset($this->attributes['notification_type_id'])) {
            return NotificationType::find($this->attributes['notification_type_id'])->color;
        }
    }

    // для формирования линки и перехода в документ
    public function getLinkAttribute() {
        if (isset($this->attributes["documentable_type"]) && $this->attributes['documentable_id']>0  && isset($this->attributes["documentable_id"])) {
            $model_table = $this->attributes["documentable_type"];
            $morphModel = new $model_table();
            return ["table"=>$morphModel->table(), "id"=>$this->attributes["documentable_id"]];
        }
    }

}
