<?php

namespace App;

use App\ABPTable;
use App\Nomenklatura;

class ProductionComponent extends ABPTable
{
    // регистры
    use Traits\SerialNumbersTrait, Traits\SkladRegisterTrait;

    public function __construct()
    {
        parent::__construct();

        $this->table('production_components');
        $this->table_type('sub_table');

        $this->model([
            ["name" => "production_item_id", "type" => "key", "table" => "production_items", "table_class" => "ProductionItem", "title" => "ID произведенного изделия", "require" => true, "index" => "index", "show_in_table" => false],
            ["name" => "nomenklatura_id", "type" => "select", "table" => "nomenklatura", "title" => "Компонент", "require" => true, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 1],
            ["name" => "kolvo", "name_1c" => "Количество", "type" => "kolvo", "title" => "Количество", "require" => true, 'default' => 1, "index" => "index", "show_in_table" => true, "out_index" => 3, "show_in_form" => true],
            // ["name"=>"price","type"=>"money","title"=>"Цена","require"=>false,"index"=>"index","show_in_table"=>true,"out_index"=>2,"show_in_form"=>false],
            // ["name"=>"summa","type"=>"money","title"=>"Сумма","require"=>false,"index"=>"index","show_in_table"=>true,"out_index"=>4,"show_in_form"=>false],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['nomenklatura', 'replaces', 'ed_ism']);
    }

    function component()
    {
        return $this->belongsTo('App\Nomenklatura', 'nomenklatura_id', 'id');
    }

    function production_item()
    {
        return $this->belongsTo('App\ProductionItem', 'production_item_id', 'id');
    }

    function production()
    {
        return $this->production_item->production();
    }

    public function register()
    {
        return $this->morphMany('App\SkladRegister', 'registrable');
    }

    public function replaces()
    {
        return $this->hasMany('App\ProductionReplace', 'component_id', 'id');
    }

    // читатели
    // выдаем номенклатуру
    public function getNomenklaturaAttribute()
    {
        $component = $this->component()->first();
        return $component ? $component->getSelectListTitleAttribute() : '';
    }
    // выдаем единицу измерения номенклатуры
    public function getEdIsmAttribute()
    {
        $component = $this->component()->first();
        return $component ? $component->ed_ism : '';
    }
    // замены
    public function getReplacesAttribute()
    {
        return $this->replaces()->get();
    }
}