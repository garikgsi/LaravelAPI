<?php

namespace App;

use App\ABPTable;


class ActItem extends ABPTable
{
    // регистры
    use Traits\SerialNumbersTrait, Traits\SkladRegisterTrait;

    public function __construct()
    {
        parent::__construct();

        $this->table('act_items');
        $this->has_files(false);
        $this->has_images(false);
        $this->has_groups(false);
        $this->table_type('sub_table');

        $this->model([
            ["name" => "act_id", "type" => "key", "table" => "acts", "table_class" => "Act", "title" => "Реализация", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "nomenklatura_id", "name_1c" => "Номенклатура", "type" => "select", "table" => "nomenklatura", "table_class" => "Nomenklatura", "title" => "Номенклатура", "require" => true, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 1],
            ["name" => "nomenklatura_name", "name_1c" => "Содержание", "type" => "string", "title" => "Содержание", "require" => false, "index" => "index", "show_in_table" => false],
            ["name" => "kolvo", "name_1c" => "Количество", "type" => "kolvo", "title" => "Количество", "require" => true, 'default' => 1, "index" => "index", "show_in_table" => true, "out_index" => 3, "show_in_form" => true],
            ["name" => "price", "name_1c" => "Цена", "type" => "money", "title" => "Цена", "require" => true, "index" => "index", "show_in_table" => true, "out_index" => 2, "show_in_form" => true],
            ["name" => "summa", "name_1c" => "Сумма", "type" => "money", "title" => "Сумма", "require" => true, "index" => "index", "show_in_table" => true, "out_index" => 4, "show_in_form" => true],
            ["name" => "summa_nds", "name_1c" => "СуммаНДС", "type" => "money", "title" => "Сумма НДС", "require" => false, "index" => "index", "show_in_table" => false],
            ["name" => "nds_id", "name_1c" => "СтавкаНДС", "type" => "select", "table" => "nds", "title" => "Ставка НДС", "require" => true, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => true, "out_index" => 5]
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['nomenklatura', 'ed_ism', 'is_usluga', 'stavka_nds', 'summa_nds']);
    }


    // связи
    // заказ
    public function act()
    {
        return $this->belongsTo('App\Act', 'act_id');
    }
    // номенклатура
    function nomenklatura_()
    {
        return $this->belongsTo('App\Nomenklatura', 'nomenklatura_id', 'id');
    }
    // ставка НДС
    function nds_()
    {
        return $this->belongsTo('App\NDS', 'nds_id');
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