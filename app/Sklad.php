<?php

namespace App;

use App\ABPTable;
use App\Common\ABPCache;

class Sklad extends ABPTable

{
    // экспорт в 1С
    use Traits\Trait1C;
    /*
        "name": "Catalog_Склады"
            {
            "Ref_Key": "fefc5aa3-8857-11e5-a85a-3085a93ddca2",
            "DataVersion": "AAAAAQAAAAA=",
            "DeletionMark": false,
            "Parent_Key": "00000000-0000-0000-0000-000000000000",
            "IsFolder": false,
            "Code": "БП-000001",
            "Description": "Основной склад",
            "Комментарий": "",
            "ТипЦенРозничнойТорговли_Key": "00000000-0000-0000-0000-000000000000",
            "ТипСклада": "ОптовыйСклад",
            "ПодразделениеОрганизации_Key": "00000000-0000-0000-0000-000000000000",
            "НоменклатурнаяГруппа_Key": "00000000-0000-0000-0000-000000000000",
            "ДополнительныеРеквизиты": [],
            "Predefined": false,
            "PredefinedDataName": ""
            }
 */

    public function __construct()
    {
        parent::__construct();

        $this->name_1c('Catalog_Склады');
        $this->table('sklads');
        $this->has_folders_1c(true);
        $this->has_files(true);

        $this->model([
            ["name" => "keeper_id", "type" => "select", "table" => "sotrudniks", "table_class" => "Firm", "title" => "Кладовщик", "require" => false, "default" => 1, "index" => "index", "show_in_table" => true, "out_index" => 2],
            ["name" => "commission_member1", "type" => "select", "table" => "sotrudniks", "table_class" => "Firm", "title" => "Член комиссии №1", "require" => false, "default" => 1, "index" => "index", "show_in_table" => true, "out_index" => 3],
            ["name" => "commission_member2", "type" => "select", "table" => "sotrudniks", "table_class" => "Firm", "title" => "Член комиссии №2", "require" => false, "default" => 1, "index" => "index", "show_in_table" => true, "out_index" => 4],
            ["name" => "commission_chairman", "type" => "select", "table" => "sotrudniks", "table_class" => "Firm", "title" => "Председатель комиссии", "require" => false, "default" => 1, "index" => "index", "show_in_table" => true, "out_index" => 5],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['keeper']);
    }

    // связь склад - кладовщик
    public function keeper()
    {
        return $this->belongsTo('App\Sotrudnik', 'keeper_id');
    }

    // читатели
    // выдаем кладовщика
    public function getKeeperAttribute()
    {
        if (isset($this->attributes['keeper_id'])) {
            $value = $this->attributes['keeper_id'];
            return ABPCache::get_select_list('sotrudniks', $value);
        }
    }

    // регистры накопления по складу
    public function sklad_register()
    {
        return $this->hasMany('App\SkladRegister', 'sklad_id', 'id');
    }
}