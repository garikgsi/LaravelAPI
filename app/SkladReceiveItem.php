<?php

namespace App;

use App\ABPTable;

class SkladReceiveItem extends ABPTable
{
    /*
    "Товары": [],
    "Услуги": [
        {
        "Ref_Key": "c5a0384c-e9e4-11e5-4487-000c295e30eb",
        "LineNumber": "1",
        "Номенклатура_Key": "f1c74c20-e9c7-11e5-4487-000c295e30eb",
        "Содержание": "Аренда нежилого помещения",
        "Количество": 1,
        "Цена": 20000,
        "Сумма": 20000,
        "СтавкаНДС": "БезНДС",
        "СуммаНДС": 0,
        "СчетЗатрат_Key": "bb0224f1-8857-11e5-a85a-3085a93ddca2",
        "ПодразделениеЗатрат_Key": "00000000-0000-0000-0000-000000000000",
        "Субконто1": "fefc5ab4-8857-11e5-a85a-3085a93ddca2",
        "Субконто1_Type": "StandardODATA.Catalog_СтатьиЗатрат",
        "Субконто2": "",
        "Субконто2_Type": "StandardODATA.Undefined",
        "Субконто3": "",
        "Субконто3_Type": "StandardODATA.Undefined",
        "СчетЗатратНУ_Key": "bb0224f1-8857-11e5-a85a-3085a93ddca2",
        "СубконтоНУ1": "fefc5ab4-8857-11e5-a85a-3085a93ddca2",
        "СубконтоНУ1_Type": "StandardODATA.Catalog_СтатьиЗатрат",
        "СубконтоНУ2": "",
        "СубконтоНУ2_Type": "StandardODATA.Undefined",
        "СубконтоНУ3": "",
        "СубконтоНУ3_Type": "StandardODATA.Undefined",
        "СчетУчетаНДС_Key": "bb0224e4-8857-11e5-a85a-3085a93ddca2",
        "ОтражениеВУСН": "Принимаются",
        "СпособУчетаНДС": "ПринимаетсяКВычету"
        }
    ]
 */

    // серийные номера и регистр хранения
    use Traits\SerialNumbersTrait, Traits\SkladRegisterTrait;

    public function __construct()
    {
        parent::__construct();

        $this->table('sklad_receive_items');
        // $this->fillable(['sklad_receive_id','nomenklatura_id','nomenklatura_name','kolvo','price','summa','summa_nds','stavka_nds']);
        $this->table_type('sub_table');
        // серийники
        $this->has_series(true);

        $this->model([
            ["name" => "npp", "name_1c" => "LineNumber", "type" => "integer", "title" => "№ п/п", "default" => 0, "require" => false, "index" => "index", "show_in_table" => false],
            ["name" => "sklad_receive_id", "name_1c" => "Ref_Key", "type" => "key", "table" => "sklad_receives", "table_class" => "SkladReceive", "title" => "ID поступления на склад", "require" => false, "index" => "index", "show_in_table" => false],
            ["name" => "nomenklatura_id", "name_1c" => "Номенклатура", "type" => "select", "table" => "nomenklatura", "table_class" => "Nomenklatura", "title" => "Номенклатура", "require" => true, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 1],
            ["name" => "nomenklatura_name", "name_1c" => "Содержание", "type" => "string", "title" => "Содержание", "require" => false, "index" => "index", "show_in_table" => false],
            ["name" => "kolvo", "name_1c" => "Количество", "type" => "kolvo", "title" => "Количество", "require" => true, 'default' => 1, "index" => "index", "show_in_table" => true, "out_index" => 3, "show_in_form" => true],
            ["name" => "price", "name_1c" => "Цена", "type" => "money", "title" => "Цена без НДС", "require" => true, "index" => "index", "show_in_table" => true, "out_index" => 2, "show_in_form" => true],
            ["name" => "summa", "name_1c" => "Сумма", "type" => "money", "title" => "Сумма без НДС", "require" => true, "index" => "index", "show_in_table" => true, "out_index" => 4, "show_in_form" => true],
            ["name" => "summa_nds", "name_1c" => "СуммаНДС", "type" => "money", "title" => "Сумма НДС", "require" => false, "index" => "index", "show_in_table" => false],
            ["name" => "nds_id", "name_1c" => "СтавкаНДС", "type" => "select", "table" => "nds", "title" => "Ставка НДС", "require" => true, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => true, "out_index" => 5]
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['nomenklatura', 'ed_ism', 'is_usluga', 'stavka_nds', 'summa_nds']);
    }

    function nomenklatura_()
    {
        return $this->belongsTo('App\Nomenklatura', 'nomenklatura_id', 'id');
    }

    function nds_()
    {
        return $this->belongsTo('App\NDS', 'nds_id');
    }

    function sklad_receive()
    {
        return $this->belongsTo('App\SkladReceive', 'sklad_receive_id', 'id');
    }

    // читатели
    // выдаем номенклатуру
    public function getNomenklaturaAttribute()
    {
        $n = $this->nomenklatura_()->first();
        return $n ? $n->getSelectListTitleAttribute() : '';
    }
    // единица измерения номенклатуры
    public function getEdIsmAttribute()
    {
        $n = $this->nomenklatura_()->first();
        return $n ? $n->ed_ism : '';
    }
    // номенклатура == услуга
    public function getIsUslugaAttribute()
    {
        $n = $this->nomenklatura_()->first();
        return $n ? boolval($n->is_usluga) : false;
    }
    // ставка НДС
    public function getStavkaNdsAttribute()
    {
        $n = $this->nds_()->first();
        return $n ? $n->name : 'БезНДС';
    }
    // сумма НДС
    public function getSummaNdsAttribute()
    {
        $n = $this->nds_()->first();
        if ($n) {
            return floatVal($this->summa * $n->stavka);
        }
        return 0;
    }
}