<?php

namespace App;

use App\ABPTable;

class SiteContent extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('site_contents');
        $this->has_files(false);
        // $this->fillable(["menu_point_id","content","preview","index_page_text","start_from","meta_title",
        // "meta_keywords","meta_description","surl","img"]);

        $this->model([
            ["name"=>"menu_point_id","type"=>"select","table"=>"site_menu_points","table_class"=>"SiteMenuPoint","title"=>"Раздел сайта","require"=>true,"index"=>"index"],
            ["name"=>"content","type"=>"text","title"=>"Контент","require"=>false],
            ["name"=>"preview","type"=>"text","title"=>"Превью","require"=>false],
            ["name"=>"index_page_text","type"=>"text","title"=>"Текст на стартовой","require"=>false],
            ["name"=>"start_from","type"=>"datetime","title"=>"Дата начала показа","require"=>false,"index"=>"index"],
            ["name"=>"meta_title","type"=>"string","title"=>"Метатег TITLE","require"=>false,"index"=>"index"],
            ["name"=>"meta_keywords","type"=>"string","title"=>"Метатег KEYWORDS","require"=>false,"index"=>"index"],
            ["name"=>"meta_description","type"=>"string","title"=>"Метатег DESCRIPTION","require"=>false,"index"=>"index"],
            ["name"=>"surl","type"=>"string","title"=>"URI","require"=>true,"index"=>"index"],
            ["name"=>"img","type"=>"image","title"=>"Изображение","require"=>false,"index"=>"index", "max"=>1000],
        ]);
    }


    public function site_menu_points()
    {
        return $this->belongsTo('App\SiteMenuPoint','menu_point_id','id');
    }
}
