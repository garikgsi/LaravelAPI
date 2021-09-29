<?php

namespace App;

use App\ABPTable;


class DocType extends ABPTable
{
    // экспорт в 1С
    use Traits\Trait1C;


    public function __construct()
    {
        parent::__construct();

        $this->name_1c('Catalog_ВидыНоменклатуры');
        $this->table('doc_types');
        // уникальный ключ
        $this->unique_key('name');
    }

    public function nomenklatura()
    {
        return $this->hasMany('App\Nomenklatura');
    }
}