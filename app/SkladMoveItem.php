<?php

namespace App;

use App\ABPTable;

class SkladMoveItem extends ABPTable
{
    // серийные номера для поступления на склад
    use Traits\SerialNumbersTrait, Traits\SkladRegisterTrait;

    public function __construct()
    {
        parent::__construct();

        $this->table('sklad_move_items');
        $this->table_type('sub_table');

        // перемещение в разрезе серийных номеров
        $this->has_sub_series(true);

        $this->model([
            ["name" => "sklad_move_id", "type" => "key", "table" => "sklad_moves", "table_class" => "SkladReceive", "title" => "ID перемещения", "require" => true, "index" => "index", "show_in_table" => false],
            ["name" => "nomenklatura_id", "type" => "stock_balance", "sklad_id" => "sklad_out_id", "table_class" => "Nomenklatura", "title" => "Номенклатура", "require" => true, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 1],
            ["name" => "kolvo", "name_1c" => "Количество", "type" => "kolvo", "title" => "Количество", "require" => true, 'default' => 1, "index" => "index", "show_in_table" => true, "out_index" => 3, "show_in_form" => true],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['nomenklatura', 'ed_ism']);
    }


    function nomenklatura_()
    {
        return $this->belongsTo('App\Nomenklatura', 'nomenklatura_id', 'id');
    }

    function sklad_move()
    {
        return $this->belongsTo('App\SkladMove', 'sklad_move_id', 'id');
    }

    // public function register()
    // {
    //     return $this->morphMany('App\SkladRegister', 'registrable');
    // }



    // выдаем номенклатуру
    public function getNomenklaturaAttribute()
    {
        return $this->nomenklatura_()->first()->getSelectListTitleAttribute();
    }
    // единица измерения номенклатуры
    public function getEdIsmAttribute()
    {
        return $this->nomenklatura_()->first()->ed_ism;
    }
}
