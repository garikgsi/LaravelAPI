<?php

namespace App;

use App\ABPTable;
use App\Common\ABPCache;


class TableTag extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('table_tags');
        // добавляем читателей
        $this->appends = array_merge($this->appends,['tag']);

        $this->model([
            ["name"=>"tag_id","type"=>"select","table"=>"tags","table_class"=>"Tag","title"=>"ID группы","require"=>true,"default"=>1,"index"=>"index"],
            ["name"=>"table_id","type"=>"select","table"=>"polymorph","title"=>"ID владельца","require"=>true,"default"=>1,"index"=>"index"],
            ["name"=>"table_type","type"=>"polymorph_table","title"=>"таблица владельца","require"=>true,"index"=>"index"],
        ]);
    }

    // модель группы
    public function tag() {
        return $this->belongsTo('App\Tag');
    }

    // читатели
    // выдаем наименование тега
    public function getTagAttribute()
    {
        if (isset($this->attributes['tag_id'])) {
            $value = $this->attributes['tag_id'];
            return ABPCache::get_select_list('tags', $value);
        }
    }

}
