<?php

namespace App;

use App\ABPTable;

class Valuta extends ABPTable

{
    // экспорт в 1С
    use Traits\Trait1C;

    /*
    "name": "Catalog_Валюты"
        {
        "Ref_Key": "d190f831-8857-11e5-a85a-3085a93ddca2",
        "DataVersion": "AAAAAQAAAAA=",
        "DeletionMark": false,
        "Code": "643",
        "Description": "руб.",
        "ЗагружаетсяИзИнтернета": false,
        "НаименованиеПолное": "Российский рубль",
        "Наценка": 0,
        "ОсновнаяВалюта_Key": "00000000-0000-0000-0000-000000000000",
        "ПараметрыПрописи": "рубль, рубля, рублей, м, копейка, копейки, копеек, ж, 2",
        "ФормулаРасчетаКурса": "",
        "СпособУстановкиКурса": "",
        "Predefined": false,
        "PredefinedDataName": ""
        }

 */

    public function __construct()
    {
        parent::__construct();

        $this->name_1c('Catalog_Валюты');
        $this->table('valuta');

        // уникальный ключ
        $this->unique_key('code');

        $this->model([
            ["name" => "code", "name_1c" => "Code", "type" => "string", "title" => "Код валюты", "require" => true, "default" => "", "index" => "index"]
        ]);
    }
}