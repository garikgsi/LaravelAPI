<?php

namespace App;

use App\ABPTable;

class ProductionReplace extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('production_replaces');
        $this->table_type('sub_table');
        // уникальный ключ (production_id - подразумевается связью)
        $this->unique_key(['component_id', 'nomenklatura_from_id', 'nomenklatura_to_id']);

        $this->model([
            ["name" => "production_id", "type" => "key", "table" => "productions", "table_class" => "Production", "title" => "ID производства", "require" => true, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "component_id", "type" => "select", "table" => "production_components", "table_class" => "ProductionComponent", "title" => "ID компонента", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "nomenklatura_from_id", "type" => "select", "table" => "nomenklatura", "title" => "Что заменяем", "require" => true, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 1],
            ["name" => "nomenklatura_to_id", "type" => "select", "table" => "nomenklatura", "title" => "На что заменяем", "require" => true, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 1],
            ["name" => "kolvo_from", "type" => "kolvo", "title" => "Количество заменяемого", "require" => true, 'default' => 1, "index" => "index", "show_in_table" => true, "out_index" => 3, "show_in_form" => true],
            ["name" => "kolvo_to", "type" => "kolvo", "title" => "Количество заменителя", "require" => true, 'default' => 1, "index" => "index", "show_in_table" => true, "out_index" => 3, "show_in_form" => true],
            ["name" => "save_to_recipe", "type" => "boolean", "title" => "Сохранить в рецептуре", "require" => true, 'default' => 1, "index" => "index", "show_in_table" => true, "out_index" => 3, "show_in_form" => true],
        ]);
        // добавляем читателей
        $this->appends = array_merge($this->appends, ['nomenklatura_from', 'nomenklatura_to']);
    }

    // производство
    function production()
    {
        return $this->belongsTo('App\Production', 'production_id', 'id');
    }

    // заменяемая номенклатура
    function replace_from()
    {
        return $this->belongsTo('App\Nomenklatura', 'nomenklatura_from_id');
    }
    // замещающая  номенклатура
    function replace_to()
    {
        return $this->belongsTo('App\Nomenklatura', 'nomenklatura_to_id');
    }

    // читатели
    // выдаем заменяемую номенклатуру
    public function getNomenklaturaFromAttribute()
    {
        return $this->replace_from()->first()->getSelectListTitleAttribute();
    }
    // выдаем замещающую номенклатуру
    public function getNomenklaturaToAttribute()
    {
        return $this->replace_to()->first()->getSelectListTitleAttribute();
    }
}
