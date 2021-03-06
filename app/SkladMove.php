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
            ["name" => "is_out", "type" => "boolean", "title" => "Отправлен", "require" => false, 'default' => false, "index" => "index", "readonly" => true, "show_in_form" => true, "post" => true],
            ["name" => "is_in", "type" => "boolean", "title" => "Получен", "require" => false, 'default' => false, "index" => "index", "readonly" => true, "show_in_form" => true, "post" => true],
            // правильная полиморфная запись для визуализации
            ["name" => "transitable", "title" => "Через кого", "type" => "morph", "tables" => [["table" => "sotrudniks", "title" => "Сотрудника", "type" => "App\\Sotrudnik"], ["table" => "shipping_companies", "title" => "Транспортную компанию", "type" => "App\\ShippingCompany"]], "require" => true, "out_index" => 5, "show_in_table" => false],
            ["name" => "transitable_title", "type" => "string", "virtual" => true, "title" => "Через кого", "show_in_table" => true, "show_in_form" => false],

            // фильтры
            ["name" => "items.nomenklatura_.groups", "type" => "groups", "table" => "nomenklatura", "filter" => true, "virtual" => true, "title" => "Контрагент", "show_in_table" => false, "show_in_form" => false],
            ["name" => "items.nomenklatura_id", "type" => "select", "table" => "nomenklatura", "filter" => true, "virtual" => true, "title" => "Номенклатура", "show_in_table" => false, "show_in_form" => false],

        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['transitable_title', 'sklad_in', 'sklad_out', 'firm']);
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
    // организация
    public function firm_()
    {
        return $this->belongsTo('App\Firm', 'firm_id');
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
        try {
            $doc_date = Carbon::createFromFormat('Y-m-d', $this->doc_date);
            return 'Перемещение №' . $this->doc_num . ' от ' . $doc_date->format('d.m.Y');
        } catch (\Throwable $th) {
            return '';
        }
    }
    // значение полиморфной таблицы
    public function getTransitableTitleAttribute()
    {
        try {
            if (isset($this->attributes["transitable_type"]) && $this->attributes["transitable_type"] && isset($this->attributes["transitable_id"]) && $this->attributes["transitable_id"] > 1) {
                $model_table = $this->attributes["transitable_type"];
                $morphModel = new $model_table();
                return ABPCache::get_select_list($morphModel->table(), $this->attributes["transitable_id"]);
            }
        } catch (\Throwable $th) {
            return null;
        }
    }
    // склад отправления
    public function getSkladOutAttribute()
    {
        try {
            return $this->sklad_out_()->first()->getSelectListTitleAttribute();
        } catch (\Throwable $th) {
            return '';
        }
    }
    // склад получения
    public function getSkladInAttribute()
    {
        try {
            return $this->sklad_in_()->first()->getSelectListTitleAttribute();
        } catch (\Throwable $th) {
            return '';
        }
    }
    // организация
    public function getFirmAttribute()
    {
        try {
            return $this->firm_()->first()->getSelectListTitleAttribute();
        } catch (\Throwable $th) {
            return '';
        }
    }
}
