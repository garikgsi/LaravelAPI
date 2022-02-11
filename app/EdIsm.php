<?php

namespace App;

use App\ABPTable;

class EdIsm extends ABPTable
{
    // экспорт в 1С
    use Traits\Trait1C;

    public function __construct()
    {
        parent::__construct();

        $this->name_1c('Catalog_КлассификаторЕдиницИзмерения');
        $this->table('ed_ism');
        // $this->fillable(['okei']);
        $this->model([
            ["name" => "okei", "name_1c" => "Code", "type" => "string", "title" => "Код ОКЕИ", "require" => true, "index" => "index"]
        ]);
        // уникальный ключ
        $this->unique_key('okei');
    }

    public function nomenklatura()
    {
        return $this->hasMany('App\Nomenklatura');
    }
}
