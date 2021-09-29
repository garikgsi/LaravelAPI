<?php

namespace App;

use App\ABPTable;
use App\Common\ABPCache;

class Sotrudnik extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('sotrudniks');
        $this->has_files(true);
        $this->has_images(true);
        $this->has_groups(true);

        $this->model([
            ["name" => "birthday", "type" => "date", "title" => "День рождения", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => true, "out_index" => 6],
            ["name" => "gender", "size" => "1", "type" => "radio", "title" => "Пол", "items" => [["id" => "Мужской", "text" => "М", "color" => "blue"], ["id" => "Женский", "text" => "Ж", "color" => "red"]], "require" => true, "index" => "index", "show_in_table" => false, "show_in_form" => true, "out_index" => 5],
            ["name" => "inn", "type" => "string", "max" => 32, "title" => "ИНН", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => true, "out_index" => 7],
            ["name" => "snils", "type" => "string", "max" => 32, "title" => "СНИЛС", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => true, "out_index" => 7],
            ["name" => "sure_name", "type" => "string", "max" => 255, "title" => "Фамилия", "require" => true, "index" => "index", "show_in_table" => false, "show_in_form" => true, "out_index" => 1],
            ["name" => "first_name", "type" => "string", "max" => 255, "title" => "Имя", "require" => true, "index" => "index", "show_in_table" => false, "show_in_form" => true, "out_index" => 2],
            ["name" => "patronymic", "type" => "string", "max" => 255, "title" => "Отчество", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => true, "out_index" => 3],
            ["name" => "fired", "type" => "boolean", "title" => "Уволен", "require" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 7],
            ["name" => "firm_position_id", "type" => "select", "table" => "firm_positions", "table_class" => "FirmPosition", "title" => "Должность", "require" => true, "default" => 1, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 4],
            ["name" => "fio", "type" => "text", "title" => "ФИО", "virtual" => true, "show_in_table" => true, "show_in_form" => false, "out_index" => 0],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['fio', 'short_fio', 'firm_position']);
    }
    // привязка к пользователю системы
    public function user()
    {
        return $this->morphOne('App\UserInfo', 'userable');
    }

    // склады, в которых сотрудник является кладовщиком
    public function store_keeper()
    {
        return $this->hasMany('App\Sklad', 'keeper_id');
    }

    public function is_keeper($sklad_id)
    {
        if (isset($this->attributes['id'])) {
            $is_ok = $this->store_keeper->where('id', $sklad_id)->where('keeper_id', $this->attributes['id'])->count();
            return $is_ok > 0;
        }
        return false;
    }


    // читатели
    // ФИО полностью
    public function getFioAttribute()
    {
        return $this->attributes["sure_name"] . " " . $this->attributes["first_name"] . " " . $this->attributes["patronymic"];
    }

    // фамилия, инициалы
    public function getShortFioAttribute()
    {
        return $this->attributes["sure_name"] . " " . mb_substr($this->attributes["first_name"], 0, 1, 'UTF-8') . ". " . mb_substr($this->attributes["patronymic"], 0, 1, 'UTF-8') . ".";
    }

    // выдаем должность
    public function getFirmPositionAttribute()
    {
        if (isset($this->attributes['firm_position_id'])) {
            $value = $this->attributes['firm_position_id'];
            return ABPCache::get_select_list('firm_positions', $value);
        }
    }
}