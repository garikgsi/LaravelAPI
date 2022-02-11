<?php

namespace App;

use App\ABPTable;
use Carbon\Carbon;

class SkladReceive extends ABPTable

{
    // экспорт в 1С
    use Traits\Trait1C;

    /*     "odata.metadata": "http://10.0.1.21/1c/odata/standard.odata/$metadata#Document_ПоступлениеТоваровУслуг",

    "Ref_Key": "c5a0384c-e9e4-11e5-4487-000c295e30eb",
    "DataVersion": "AAAAAQAAAAA=",
    "DeletionMark": false,
    "Number": "К100-000001",
    "Date": "2015-12-31T23:59:59",
    "Posted": true,
    "ВидОперации": "Услуги",
    "Организация_Key": "39d6d856-b849-11e5-d193-000c295e30eb",
    "Склад_Key": "fefc5aa3-8857-11e5-a85a-3085a93ddca2",
    "ПодразделениеОрганизации_Key": "00000000-0000-0000-0000-000000000000",
    "Контрагент_Key": "a348aa8c-d629-11e5-0a9b-000c295e30eb",
    "ДоговорКонтрагента_Key": "acfb9eb8-d629-11e5-0a9b-000c295e30eb",
    "СпособЗачетаАвансов": "Автоматически",
    "СчетУчетаРасчетовСКонтрагентом_Key": "bb02252b-8857-11e5-a85a-3085a93ddca2",
    "СчетУчетаРасчетовПоАвансам_Key": "bb02252c-8857-11e5-a85a-3085a93ddca2",
    "СчетУчетаРасчетовПоТаре_Key": "00000000-0000-0000-0000-000000000000",
    "ВалютаДокумента_Key": "d190f831-8857-11e5-a85a-3085a93ddca2",
    "СчетНаОплатуПоставщика_Key": "00000000-0000-0000-0000-000000000000",
    "НомерВходящегоДокумента": "1",
    "ДатаВходящегоДокумента": "2015-12-31T00:00:00",
    "Грузоотправитель_Key": "00000000-0000-0000-0000-000000000000",
    "Грузополучатель_Key": "00000000-0000-0000-0000-000000000000",
    "Ответственный_Key": "dc3c4cb4-b837-11e5-d193-000c295e30eb",
    "Комментарий": "",
    "КратностьВзаиморасчетов": "1",
    "КурсВзаиморасчетов": 1,
    "НДСВключенВСтоимость": true,
    "СуммаВключаетНДС": false,
    "СуммаДокумента": 20000,
    "ТипЦен_Key": "00000000-0000-0000-0000-000000000000",
    "РучнаяКорректировка": false,
    "УдалитьУчитыватьНДС": false,
    "УдалитьПредъявленСчетФактура": false,
    "УдалитьНомерВходящегоСчетаФактуры": "",
    "УдалитьДатаВходящегоСчетаФактуры": "0001-01-01T00:00:00",
    "УдалитьНДСПредъявленКВычету": false,
    "УдалитьКодВидаОперации": "",
    "УдалитьКодСпособаПолучения": 0,
    "КодВидаТранспорта": "  ",
    "НДСНеВыделять": false,
    "УдалитьТТНВходящаяЕГАИС_Key": "00000000-0000-0000-0000-000000000000",
    "ЕстьМаркируемаяПродукцияГИСМ": false,
    "МОЛ_Key": "00000000-0000-0000-0000-000000000000",
    "МестонахождениеОС_Key": "00000000-0000-0000-0000-000000000000",
    "ГруппаОС": "",
    "СпособОтраженияРасходовПоАмортизации_Key": "00000000-0000-0000-0000-000000000000",
    "ОбъектыПредназначеныДляСдачиВАренду": false,
    "Оборудование": [],
    "ОбъектыСтроительства": [],
    "Товары": [],
    "Услуги": [
    {
    "Ref_Key": "c5a0384c-e9e4-11e5-4487-000c295e30eb",
    "LineNumber": "1",
    "Номенклатура_Key": "f1c74c20-e9c7-11e5-4487-000c295e30eb",
    "Содержание": "Аренда нежилого помещения",
    "Количество": 1,
    "Цена": 20000,
    "Сумма": 20000,
    "СтавкаНДС": "БезНДС",
    "СуммаНДС": 0,
    "СчетЗатрат_Key": "bb0224f1-8857-11e5-a85a-3085a93ddca2",
    "ПодразделениеЗатрат_Key": "00000000-0000-0000-0000-000000000000",
    "Субконто1": "fefc5ab4-8857-11e5-a85a-3085a93ddca2",
    "Субконто1_Type": "StandardODATA.Catalog_СтатьиЗатрат",
    "Субконто2": "",
    "Субконто2_Type": "StandardODATA.Undefined",
    "Субконто3": "",
    "Субконто3_Type": "StandardODATA.Undefined",
    "СчетЗатратНУ_Key": "bb0224f1-8857-11e5-a85a-3085a93ddca2",
    "СубконтоНУ1": "fefc5ab4-8857-11e5-a85a-3085a93ddca2",
    "СубконтоНУ1_Type": "StandardODATA.Catalog_СтатьиЗатрат",
    "СубконтоНУ2": "",
    "СубконтоНУ2_Type": "StandardODATA.Undefined",
    "СубконтоНУ3": "",
    "СубконтоНУ3_Type": "StandardODATA.Undefined",
    "СчетУчетаНДС_Key": "bb0224e4-8857-11e5-a85a-3085a93ddca2",
    "ОтражениеВУСН": "Принимаются",
    "СпособУчетаНДС": "ПринимаетсяКВычету"
    }
    ],
    "ВозвратнаяТара": [],
    "ЗачетАвансов": [],
    "АгентскиеУслуги": [],
    "ОсновныеСредства": [],
    "ШтрихкодыУпаковок": [],
    "Топливо": [],
    "Организация@navigationLinkUrl": "Document_ПоступлениеТоваровУслуг(guid'c5a0384c-e9e4-11e5-4487-000c295e30eb')/Организация",
    "Склад@navigationLinkUrl": "Document_ПоступлениеТоваровУслуг(guid'c5a0384c-e9e4-11e5-4487-000c295e30eb')/Склад",
    "Контрагент@navigationLinkUrl": "Document_ПоступлениеТоваровУслуг(guid'c5a0384c-e9e4-11e5-4487-000c295e30eb')/Контрагент",
    "ДоговорКонтрагента@navigationLinkUrl": "Document_ПоступлениеТоваровУслуг(guid'c5a0384c-e9e4-11e5-4487-000c295e30eb')/ДоговорКонтрагента",
    "СчетУчетаРасчетовСКонтрагентом@navigationLinkUrl": "Document_ПоступлениеТоваровУслуг(guid'c5a0384c-e9e4-11e5-4487-000c295e30eb')/СчетУчетаРасчетовСКонтрагентом",
    "СчетУчетаРасчетовПоАвансам@navigationLinkUrl": "Document_ПоступлениеТоваровУслуг(guid'c5a0384c-e9e4-11e5-4487-000c295e30eb')/СчетУчетаРасчетовПоАвансам",
    "ВалютаДокумента@navigationLinkUrl": "Document_ПоступлениеТоваровУслуг(guid'c5a0384c-e9e4-11e5-4487-000c295e30eb')/ВалютаДокумента",
    "Ответственный@navigationLinkUrl": "Document_ПоступлениеТоваровУслуг(guid'c5a0384c-e9e4-11e5-4487-000c295e30eb')/Ответственный"
    }


 */

    public function __construct()
    {
        parent::__construct();

        $this->name_1c('Document_ПоступлениеТоваровУслуг');
        $this->table('sklad_receives');
        $this->table_type('document');
        $this->sub_tables([
            ["table" => "sklad_receive_items", "name_1c" => "Товары", "class" => "SkladReceive", "method" => "items", "title" => "Позиции накладной", "item_class" => "App\SkladReceiveItem", "belongs_method" => "sklad_receive"],
            // ["name"=>"sklad_receive_items", "name_1c"=>"Товары", "class"=>"SkladReceive","method"=>"tovary","title"=>"Товары","item_class"=>"App\SkladReceiveItem", "belongs_method"=>"sklad_receive"],
            // ["name"=>"sklad_receive_items", "name_1c"=>"Услуги", "class"=>"SkladReceive","method"=>"uslugi","title"=>"Услуги","item_class"=>"App\SkladReceiveItem", "belongs_method"=>"sklad_receive"],
            ["name_1c" => "Оборудование"],
            ["name_1c" => "ОсновныеСредства"],
            ["name_1c" => "ОбъектыСтроительства"],
            ["name_1c" => "ВозвратнаяТара"],
            ["name_1c" => "ЗачетАвансов"],
            ["name_1c" => "АгентскиеУслуги"],
            ["name_1c" => "ОсновныеСредства"],
            ["name_1c" => "ШтрихкодыУпаковок"],
            ["name_1c" => "Топливо"],
        ]);

        $this->model([
            ["name" => "firm_id", "name_1c" => "Организация_Key", "type" => "select", "table" => "firms", "table_class" => "Firm", "title" => "Организация", "require" => true, "default" => 1, "index" => "index", "show_in_table" => true, "out_index" => 4],
            ["name" => "sklad_id", "type" => "select", "table" => "sklads", "table_class" => "Sklad", "title" => "Склад", "require" => true, "default" => 1, "index" => "index", "show_in_table" => true, "out_index" => 8],
            ["name" => "kontragent_id", "name_1c" => "Контрагент_Key", "type" => "select", "table" => "kontragents", "table_class" => "Kontragent", "title" => "Поставщик", "require" => true, "default" => 1, "index" => "index", "show_in_table" => true, "out_index" => 5],
            ["name" => "dogovor_id", "type" => "select", "table" => "dogovors", "table_class" => "Dogovor", "title" => "Договор", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false, "ignore_filters" => true],
            ["name" => "valuta_id", "name_1c" => "ВалютаДокумента_Key", "type" => "select", "table" => "valuta", "table_class" => "Valuta", "title" => "Валюта документа", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "ignore_filters" => true],
            ["name" => "in_doc_num", "name_1c" => "НомерВходящегоДокумента", "type" => "string", "title" => "№ входящего документа", "require" => true, "default" => "", "index" => "index", "show_in_table" => true, "out_index" => 6],
            ["name" => "in_doc_date", "name_1c" => "ДатаВходящегоДокумента", "type" => "date", "title" => "Дата входящего документа", "require" => true, "default" => date("Y-m-d"), "index" => "index", "show_in_table" => true, "ignore_filters" => true, "out_index" => 7],
            ["name" => "kontragent_otpravitel_id", "name_1c" => "Грузоотправитель_Key", "type" => "select", "table" => "kontragents", "table_class" => "Kontragent", "title" => "Грузоотправитель", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false, "ignore_filters" => true],
            ["name" => "firm_poluchatel_id", "name_1c" => "Грузополучатель_Key", "type" => "select", "table" => "firms", "table_class" => "Firm", "title" => "Грузополучатель", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false, "ignore_filters" => true],
            ["name" => "price_include_nds", "name_1c" => "НДСВключенВСтоимость", "type" => "boolean", "title" => "НДС включен в стоимость", "require" => true, 'default' => false, "index" => "index", "show_in_table" => false, "show_in_form" => false, "ignore_filters" => true],
            ["name" => "sum_include_nds", "name_1c" => "СуммаВключаетНДС", "type" => "boolean", "title" => "Сумма включает НДС", "require" => true, 'default' => false, "index" => "index", "show_in_table" => false, "show_in_form" => false, "ignore_filters" => true],
            ["name" => "summa", "name_1c" => "СуммаДокумента", "type" => "money", "virtual" => true, "title" => "Сумма документа", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],

            // фильтры
            ["name" => "items.nomenklatura_.groups", "type" => "groups", "table" => "nomenklatura", "filter" => true, "virtual" => true, "title" => "Контрагент", "show_in_table" => false, "show_in_form" => false],
            ["name" => "items.nomenklatura_id", "type" => "select", "table" => "nomenklatura", "filter" => true, "virtual" => true, "title" => "Номенклатура", "show_in_table" => false, "show_in_form" => false],

        ]);
        // добавляем читателей
        $this->appends = array_merge($this->appends, ['dogovor', 'sklad', 'firm', 'kontragent', 'summa']);
    }

    // связи
    // позиции накладной
    public function items()
    {
        return $this->hasMany('App\SkladReceiveItem');
    }
    // фирма
    public function firm_()
    {
        return $this->belongsTo('App\Firm', 'firm_id');
    }
    // склад
    public function sklad_()
    {
        return $this->belongsTo('App\Sklad', 'sklad_id');
    }
    // контрагент
    public function kontragent_()
    {
        return $this->belongsTo('App\Kontragent', 'kontragent_id');
    }
    // валюта
    public function valuta_()
    {
        return $this->belongsTo('App\Valuta', 'valuta_id');
    }
    // договор
    public function dogovor_()
    {
        return $this->belongsTo('App\Dogovor', 'dogovor_id');
    }




    // public function tovary() {
    //     return $this->hasMany('App\SkladReceiveItem', 'sklad_receive_id','id');
    //     // ->whereHas('nomenklatura' , function($query) {
    //     //     $query->where('is_usluga',0);
    //     // });
    //     // $res = $this->items()->where('id','>',1)->whereHas('nomenklatura' , function($query){
    //     //     $query->where('is_usluga',0);
    //     // });
    //     // return $res;
    // }

    // public function uslugi() {
    //     return $this->hasMany('App\SkladReceiveItem', 'sklad_receive_id','id');
    //     // $res = $this->items()->where('id','>',1)->whereHas('nomenklatura' , function($query){
    //     //     $query->where('is_usluga',1);
    //     // });
    //     // return $res;
    // }

    // читатели
    // для select-ов
    public function getSelectListTitleAttribute()
    {
        $doc_date = Carbon::createFromFormat('Y-m-d', $this->doc_date);
        return 'Поступление №' . $this->doc_num . ' от ' . $doc_date->format('d.m.Y');
    }
    // выдаем договор
    public function getDogovorAttribute()
    {
        $d = $this->dogovor_()->first();
        return is_null($d) ? '' : $d->getSelectListTitleAttribute();
    }

    // выдаем склад
    public function getSkladAttribute()
    {
        $s = $this->sklad_()->first();
        return is_null($s) ? '' : $s->getSelectListTitleAttribute();
    }

    // выдаем организацию
    public function getFirmAttribute()
    {
        $f = $this->firm_()->first();
        return is_null($f) ? '' : $f->getSelectListTitleAttribute();
    }

    // выдаем контрагента
    public function getKontragentAttribute()
    {
        $k = $this->kontragent_()->first();
        return is_null($k) ? '' : $k->getSelectListTitleAttribute();
    }

    // выдаем сумму документа
    public function getSummaAttribute()
    {
        return $this->items()->sum('summa');
    }
}