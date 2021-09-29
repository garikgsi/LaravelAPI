<?php

namespace App;

use App\ABPTable;

class Bank extends ABPTable

{
    // экспорт в 1С
    use Traits\Trait1C;

    /*
    "name": "Catalog_Банки"
        {
        "Ref_Key": "0da9dfe4-d7ac-11e5-0a9b-000c295e30eb",
        "DataVersion": "AAAAMgAAAAA=",
        "DeletionMark": false,
        "Parent_Key": "a0b4f74c-b849-11e5-d193-000c295e30eb",
        "IsFolder": false,
        "Code": "044525976",
        "Description": "АКБ \"АБСОЛЮТ БАНК\" (ПАО)",
        "КоррСчет": "30101810500000000976",
        "Город": "г. Москва",
        "Адрес": "Цветной б-р, 18",
        "Телефоны": "(495) 777-71-71",
        "РучноеИзменение": 0,
        "СВИФТБИК": "ABSLRUMMXXX",
        "Страна_Key": "c4ccb0a9-8857-11e5-a85a-3085a93ddca2",
        "Predefined": false,
        "PredefinedDataName": "",
        "Parent@navigationLinkUrl": "Catalog_Банки(guid'0da9dfe4-d7ac-11e5-0a9b-000c295e30eb')/Parent",
        "Страна@navigationLinkUrl": "Catalog_Банки(guid'0da9dfe4-d7ac-11e5-0a9b-000c295e30eb')/Страна"
        }
 */

    public function __construct()
    {
        parent::__construct();

        $this->name_1c('Catalog_Банки');
        $this->table('banks');
        $this->has_folders_1c(true);
        // уникальный ключ
        $this->unique_key('bik');

        // $this->fillable(['bik','ks','city','address','phone']);

        $this->model([
            ["name" => "bik", "name_1c" => "Code", "type" => "string", "title" => "БИК", "require" => true, "index" => "index"],
            ["name" => "ks", "name_1c" => "КоррСчет", "type" => "string", "title" => "Кор.счет", "require" => true, "index" => "index"],
            ["name" => "city", "name_1c" => "Город", "type" => "string", "title" => "Город", "require" => false, "default" => "", "index" => "index"],
            ["name" => "address", "name_1c" => "Адрес", "type" => "string", "title" => "Адрес", "require" => false, "default" => "", "index" => "index"],
            ["name" => "phone", "name_1c" => "Телефоны", "type" => "phone", "title" => "Телефон", "require" => false, "default" => "", "index" => "index"],
        ]);
    }
}