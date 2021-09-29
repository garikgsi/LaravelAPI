<?php

namespace App;

use App\ABPTable;
use App\Common\ABPCache;
use Carbon\Carbon;

class SkladMove extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('sklad_moves');
        $this->table_type('document');
        $this->sub_tables([
            ["table" => "sklad_move_items", "class" => "SkladMove", "method" => "items", "title" => "Позиции накладной", "item_class" => "App\SkladMoveItem", "belongs_method" => "sklad_move"],
        ]);

        $this->model([
            ["name" => "firm_id", "type" => "select", "table" => "firms", "table_class" => "Firm", "title" => "Организация", "require" => true, "default" => 1, "index" => "index", "show_in_table" => true, "out_index" => 6],
            ["name" => "sklad_out_id", "type" => "select", "table" => "sklads", "table_class" => "Sklad", "title" => "Склад отправления", "default" => 1, "index" => "index", "require" => true, "show_in_table" => true, "show_in_form" => true, "out_index" => 3],
            ["name" => "sklad_in_id", "name_1c" => "Склад", "type" => "select", "table" => "sklads", "table_class" => "Sklad", "title" => "Склад поступления", "default" => 1, "index" => "index", "require" => true, "show_in_table" => true, "show_in_form" => true, "out_index" => 4],
            ["name" => "is_out", "type" => "boolean", "title" => "Отправлен со склада отправления", "require" => false, 'default' => false, "index" => "index", "readonly" => true, "show_in_form" => true, "post" => true],
            ["name" => "is_in", "type" => "boolean", "title" => "Получен на складе получения", "require" => false, 'default' => false, "index" => "index", "readonly" => true, "show_in_form" => true, "post" => true],
            // правильная полиморфная запись для визуализации
            ["name" => "transitable", "title" => "Через кого", "type" => "morph", "tables" => [["table" => "sotrudniks", "title" => "Сотрудника", "type" => "App\\Sotrudnik"], ["table" => "shipping_companies", "title" => "Транспортную компанию", "type" => "App\\ShippingCompany"]], "require" => true, "out_index" => 5],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['transitable_title', 'sklad_in', 'sklad_out']);
    }

    // строки перемещения
    public function items()
    {
        return $this->hasMany('App\SkladMoveItem');
    }
    // склад отправления
    public function sklad_out_()
    {
        return $this->belongsTo('App\Sklad', 'sklad_out_id');
    }
    // склад получения
    public function sklad_in_()
    {
        return $this->belongsTo('App\Sklad', 'sklad_in_id');
    }
    // через кого поехали товары
    public function transitable()
    {
        return $this->morphTo();
    }

    // уведомления, привязанные к перемещению
    public function notifications()
    {
        return $this->morphMany('App\Notification', 'documentable');
    }

    // читатели
    // для select-ов
    public function getSelectListTitleAttribute()
    {
        $doc_date = Carbon::createFromFormat('Y-m-d', $this->doc_date);
        return 'Перемещение №' . $this->doc_num . ' от ' . $doc_date->format('d.m.Y');
    }
    // значение полиморфной таблицы
    public function getTransitableTitleAttribute()
    {
        if (isset($this->attributes["transitable_type"]) && $this->attributes["transitable_type"] && isset($this->attributes["transitable_id"]) && $this->attributes["transitable_id"] > 1) {
            $model_table = $this->attributes["transitable_type"];
            $morphModel = new $model_table();
            return ABPCache::get_select_list($morphModel->table(), $this->attributes["transitable_id"]);
        }
        return null;
    }
    // склад отправления
    public function getSkladOutAttribute()
    {
        return $this->sklad_out_()->first()->getSelectListTitleAttribute();
    }
    // склад получения
    public function getSkladInAttribute()
    {
        return $this->sklad_in_()->first()->getSelectListTitleAttribute();
    }
}