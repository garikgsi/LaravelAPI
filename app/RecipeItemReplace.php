<?php

namespace App;

use App\ABPTable;

class RecipeItemReplace extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('recipe_item_replaces');
        $this->table_type('sub_table');
        // уникальный ключ (recipe_item_id - подразумевается связью)
        $this->unique_key('nomenklatura_to_id');
        // // добавляем читателей
        $this->appends = array_merge($this->appends, ['nomenklatura_from_id']);

        $this->model([
            ["name" => "recipe_item_id", "type" => "key", "table" => "recipe_items", "table_class" => "RecipeItem", "title" => "Компонент", "require" => true, "index" => "index", "show_in_form" => false, "show_in_table" => false],
            ["name" => "nomenklatura_to_id", "type" => "select", "table" => "nomenklatura", "table_class" => "Nomenklatura", "title" => "Заменить на", "require" => true, "index" => "index", "show_in_form" => true, "show_in_table" => true],
            ["name" => "kolvo_from", "type" => "kolvo", "title" => "Количество заменяемого", "require" => true, "index" => "index", "show_in_form" => true, "show_in_table" => true],
            ["name" => "kolvo_to", "type" => "kolvo", "title" => "Количество заменителя", "require" => true, "index" => "index", "show_in_form" => true, "show_in_table" => true],
        ]);
    }

    public function recipe_item()
    {
        return $this->belongsTo('App\RecipeItem');
    }

    /**
     * геттер - Заменяемая номенклатура
     *
     * @return void
     */
    public function getNomenklaturaFromIdAttribute()
    {
        return $this->recipe_item->nomenklatura_id;
    }
}