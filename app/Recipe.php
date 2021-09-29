<?php

namespace App;

use App\ABPTable;

class Recipe extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('recipes');
        $this->has_files(true);
        $this->has_images(true);
        $this->icon('mdi-file-table-box-outline');

        $this->model([
            ["name"=>"nomenklatura_id","type"=>"key","table"=>"nomenklatura","table_class"=>"Nomenklatura","title"=>"Выпускаемое изделие","require"=>true,"index"=>"index","show_in_form"=>false,"show_in_table"=>false],
        ]);

        $this->sub_tables([
            ["table"=>"recipe_items", "class"=>"Recipe","method"=>"items","title"=>"Компоненты", "item_class"=>"App\RecipeItem", "belongs_method"=>"recipe"],
        ]);
    }

    public function items() {
        return $this->hasMany('App\RecipeItem');
    }

    public function nomenklatura() {
        return $this->belongsTo('App\Nomenklatura');
    }

    // читатели
    // public function getSelectListTitleAttribute() {
    //     return isset($this->attributes["name"])?$this->attributes["name"]:'';
    //     // if (isset($this->id)) {
    //     //     $rec = $this->find($this->id);
    //     //     return $rec->nomenklatura()->first()->getSelectListTitleAttribute()." (".$rec->name.")";
    //     // }
    // }
}
