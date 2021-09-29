<?php

namespace App;

use App\ABPTable;



class ProductionItem extends ABPTable
{
    // регистры
    use Traits\SerialNumbersTrait, Traits\SkladRegisterTrait;

    public function __construct()
    {
        parent::__construct();

        $this->table('production_items');
        $this->table_type('sub_table');
        $this->sub_tables([
            ["table" => "production_components", "class" => "ProductionItem", "method" => "components", "title" => "Компоненты изделия", "item_class" => "App\ProductionComponent", "belongs_method" => "production_item"],
        ]);

        $this->model([
            ["name" => "serial", "type" => "string", "title" => "Серийный №", "require" => false, "index" => "index", "show_in_table" => true, "show_in_form" => true],
            ["name" => "production_id", "type" => "key", "table" => "productions", "table_class" => "Production", "title" => "ID производства", "require" => true, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "nomenklatura", "type" => "text", "title" => "Изделие", "show_in_table" => true, "show_in_form" => false, "out_index" => 1, "virtual" => true],
            ["name" => "is_producted", "type" => "boolean", "title" => "Произведено", "require" => false, "index" => "index", "default" => 0, "show_in_table" => true, "show_in_form" => false],
            ["name" => "kolvo", "name_1c" => "Количество", "type" => "kolvo", "title" => "Количество", "require" => false, 'default' => 1, "index" => "index", "show_in_table" => false, "out_index" => 3, "show_in_form" => false],
            // ["name"=>"price","type"=>"money","title"=>"Цена","require"=>false,"index"=>"index","show_in_table"=>true,"out_index"=>2,"show_in_form"=>false],
            // ["name"=>"summa","type"=>"money","title"=>"Сумма","require"=>false,"index"=>"index","show_in_table"=>true,"out_index"=>4,"show_in_form"=>false],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['components', 'nomenklatura', 'self_price']);
    }

    // связи
    public function components()
    {
        return $this->hasMany('App\ProductionComponent', 'production_item_id');
    }
    // номенклатура производимого изделия
    function product()
    {
        return $this->production ? $this->production->product() : null;
    }
    // производство
    function production()
    {
        return $this->belongsTo('App\Production', 'production_id', 'id');
    }
    // // регистр остатков на складе
    // public function register() {
    //     return $this->morphMany('App\SkladRegister', 'registrable');
    // }
    // // серийные номера изделия
    // public function serials() {
    //     return $this->morphMany('App\SerialNum', 'seriable');
    // }

    // читатели
    // выдаем номенклатуру
    public function getNomenklaturaAttribute()
    {
        return $this->product() ? $this->product()->getSelectListTitleAttribute() : '';
    }
    // компоненты
    public function getComponentsAttribute()
    {
        return $this->components()->get();
    }
    // себестоимость текущая
    public function getSelfPriceAttribute()
    {
        $components = $this->components();
        $self_price = 0;
        foreach ($components as $component) {
            $n_price = $component->component()->first()->avg_price;
            $self_price += $component->kolvo * $n_price;
        }
        return floatVal($self_price);
    }
}