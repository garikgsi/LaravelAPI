<?php

namespace App;

use App\ABPTable;

class RS extends ABPTable

{
    // экспорт в 1С
    use Traits\Trait1C;

    /*
    "name": "Catalog_БанковскиеСчета"
        {
        "Ref_Key": "25192496-f63d-11e5-0185-000c295e30eb",
        "DataVersion": "AAAAAQAAAAA=",
        "DeletionMark": false,
        "Owner": "a78ac434-d62b-11e5-0a9b-000c295e30eb",
        "Owner_Type": "StandardODATA.Catalog_Контрагенты",
        "Code": "00-000065",
        "Description": "40701810200001000144, ОТДЕЛЕНИЕ 1 МОСКВА",
        "НомерСчета": "40701810200001000144",
        "Банк_Key": "5fceaea4-bb8b-11e5-d193-000c295e30eb",
        "Валютный": false,
        "ВалютаДенежныхСредств_Key": "d190f831-8857-11e5-a85a-3085a93ddca2",
        "НомерИДатаРазрешения": "",
        "ДатаОткрытия": "0001-01-01T00:00:00",
        "ДатаЗакрытия": "0001-01-01T00:00:00",
        "ПодразделениеОрганизации_Key": "00000000-0000-0000-0000-000000000000",
        "БанкДляРасчетов_Key": "00000000-0000-0000-0000-000000000000",
        "ВидСчета": "Расчетный",
        "ТекстКорреспондента": "",
        "ТекстНазначения": "",
        "МесяцПрописью": false,
        "СуммаБезКопеек": false,
        "ВсегдаУказыватьКПП": false,
        "ГосударственныйКонтракт_Key": "00000000-0000-0000-0000-000000000000",
        "СчетКорпоративныхРасчетов": false,
        "СчетБанк_Key": "00000000-0000-0000-0000-000000000000",
        "Predefined": false,
        "PredefinedDataName": "",
        "Банк@navigationLinkUrl": "Catalog_БанковскиеСчета(guid'25192496-f63d-11e5-0185-000c295e30eb')/Банк",
        "ВалютаДенежныхСредств@navigationLinkUrl": "Catalog_БанковскиеСчета(guid'25192496-f63d-11e5-0185-000c295e30eb')/ВалютаДенежныхСредств"
        }

        Catalog_Банки

 */

    public function __construct()
    {
        parent::__construct();

        $this->name_1c('Catalog_БанковскиеСчета');
        $this->table('rs');
        // // уникальный ключ
        // $this->unique_key('s_num');

        $this->model([
            ["name" => "s_num", "name_1c" => "НомерСчета", "type" => "string", "title" => "Расчетный счет", "require" => true, "index" => "index"],
            ["name" => "bank_id", "name_1c" => "Банк_Key", "type" => "select", "table" => "banks", "table_class" => "Banks", "title" => "Банк", "require" => true, "default" => 1, "index" => "index"],
            ["name" => "valuta_id", "name_1c" => "ВалютаДенежныхСредств_Key", "type" => "select", "table" => "valuta", "table_class" => "Valuta", "title" => "Валюта счета", "require" => true, "default" => 1, "index" => "index"],
            ["name" => "rs_table", "name_1c" => ["Owner_Type", "Owner"], "title" => "Владелец счета", "type" => "morph", "tables" => [["table" => "firms", "title" => "Организация", "type" => "App\\Firm"], ["table" => "kontragents", "title" => "Контрагент", "type" => "App\\Kontragent"], ["table" => "fizlica", "title" => "Физ.лицо", "type" => "App\\FizLico"]], "require" => true, "out_index" => 0],
        ]);
    }

    // владелец расчетного счета
    public function rs_table()
    {
        return $this->morphTo();
    }
}