<?php

namespace App;

use App\ABPTable;
use App\Common\ABPCache;

class ShippingCompany extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('shipping_companies');
        $this->has_files(true);
        $this->has_images(true);
        $this->has_groups(true);

        // добавляем читателей
        $this->appends = array_merge($this->appends,['kontragent']);

        $this->model([
            ["name"=>"kontragent_id","type"=>"select","table"=>"kontragents","table_class"=>"Kontragent","title"=>"Контрагент","require"=>false,'default'=>1,"index"=>"index"],
            ["name"=>"www","type"=>"string","title"=>"Сайт","require"=>false,"index"=>"index"],
            ["name"=>"phone","type"=>"phone","title"=>"Телефон","require"=>false,"index"=>"index"],
        ]);
    }
    // читатели
    // выдаем наименование контрагента
    public function getkontragentAttribute()
    {
        if (isset($this->attributes['kontragent_id'])) {
            $value = $this->attributes['kontragent_id'];
            return ABPCache::get_select_list('kontragent',$value);
        }
    }

}
