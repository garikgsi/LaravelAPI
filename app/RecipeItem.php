<?php

namespace App;

use App\ABPTable;
use App\Common\ABPCache;
use App\Nomenklatura;

class RecipeItem extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('recipe_items');
        $this->table_type('sub_table');
        // добавляем читателей
        $this->appends = array_merge($this->appends,['nomenklatura','ed_ism','has_replace']);
        // подчиненные таблицы
        $this->sub_tables([
            ["table"=>"recipe_item_replaces", "class"=>"RecipeItem","method"=>"replaces","title"=>"Замены","item_class"=>"App\RecipeItemReplace", "belongs_method"=>"recipe_item"],
        ]);

        $this->model([
            ["name"=>"recipe_id","type"=>"key","table"=>"recipes","table_class"=>"Recipe","title"=>"Рецептура","require"=>false,"index"=>"index","show_in_form"=>false,"show_in_table"=>false],
            ["name"=>"nomenklatura_id","type"=>"select","table"=>"nomenklatura","table_class"=>"Nomenklatura","title"=>"Компонент","require"=>true,"index"=>"index","show_in_form"=>true,"show_in_table"=>true],
            ["name"=>"kolvo","type"=>"kolvo","title"=>"Количество","require"=>true,"index"=>"index","show_in_form"=>true,"show_in_table"=>true],
            ["name"=>"ed_ism","type"=>"text","title"=>"Ед.изм","require"=>false,"virtual"=>true,"show_in_form"=>false,"show_in_table"=>true],
            ["name"=>"has_replace","type"=>"boolean","title"=>"Есть замены","require"=>false,"virtual"=>true,"show_in_form"=>false,"show_in_table"=>true],
        ]);
    }

    public function recipe()
    {
        return $this->belongsTo('App\Recipe','recipe_id','id');
    }

    public function component() {
        return $this->belongsTo('App\Nomenklatura');
    }

    public function replaces() {
        return $this->hasMany('App\RecipeItemReplace');
    }

    // читатели
    // выдаем наименование компонента
    public function getNomenklaturaAttribute()
    {
        if (isset($this->attributes['nomenklatura_id'])) {
            return ABPCache::get_select_list('nomenklatura',$this->attributes['nomenklatura_id']);
        }
        return '';
    }
    public function getEdIsmAttribute()
    {
        if (isset($this->attributes['nomenklatura_id'])) {
            $n = Nomenklatura::find($this->attributes['nomenklatura_id']);
            if ($n) {
                return ABPCache::get_select_list('ed_ism',$n->ed_ism_id);
            }
            return '';
        }
    }
    // есть замены
    public function getHasReplaceAttribute() {
        return $this->replaces()->count() > 0 ? true : false;
    }

}
