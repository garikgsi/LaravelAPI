<?php

namespace App;

use App\ABPTable;

class SiteMenuPoint extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('site_menu_points');
        $this->has_files(false);
        // $this->fillable(['id','title','content','content2','parent_menu_point','num_order','meta_title','meta_keywords','meta_description',
        // 'surl','module_id','is_show_in_menu','is_popular']);

        $this->model([
            ["name"=>"title","type"=>"string","title"=>"Название","require"=>true,"index"=>"index"],
            ["name"=>"content","type"=>"text","title"=>"Контент начало","require"=>false],
            ["name"=>"content2","type"=>"text","title"=>"Контент","require"=>false],
            ["name"=>"parent_menu_point","type"=>"select","table"=>"site_menu_points","table_class"=>"SiteMenuPoint","title"=>"родительский раздел","require"=>true,'default'=>0,"index"=>"index"],
            ["name"=>"num_order","type"=>"integer","title"=>"Порядок вывода","require"=>true,'default'=>1],
            ["name"=>"meta_title","type"=>"string","title"=>"Метатег TITLE","require"=>false,"index"=>"index"],
            ["name"=>"meta_keywords","type"=>"string","title"=>"Метатег KEYWORDS","require"=>false,"index"=>"index"],
            ["name"=>"meta_description","type"=>"string","title"=>"Метатег DESCRIPTION","require"=>false,"index"=>"index"],
            ["name"=>"surl","type"=>"string","title"=>"URI","require"=>true,"index"=>"index"],
            ["name"=>"module_id","type"=>"select","table"=>"site_modules","table_class"=>"SiteModule","title"=>"Тип страницы","require"=>true,'default'=>2,"index"=>"index"],
            ["name"=>"is_show_in_menu","type"=>"boolean","title"=>"Отображать в меню","index"=>"index","default"=>true],
            ["name"=>"is_popular","type"=>"boolean","title"=>"Отображать в популярных","index"=>"index","default"=>false],
        ]);
    }

    // модуль
    public function module()
    {
        return $this->belongsTo('App\SiteModule');
    }

    // дочерние разделы
    public function children() {
        return $this->hasMany('App\SiteMenuPoint','parent_menu_point','id');
    }

    // родительский раздел
    public function parent() {
        return $this->hasMany('App\SiteMenuPoint','id','parent_menu_point');
    }

    // списки
    public function getList() {
        return $this->hasMany('App\SiteContent','menu_point_id','id');
    }
}
