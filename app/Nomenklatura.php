<?php

namespace App;

use App\ABPTable;
use App\Manufacturer;
use App\EdIsm;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Common\ABPCache;
use App\SkladRegister;
use Illuminate\Support\Facades\DB;



class Nomenklatura extends ABPTable
{
    // экспорт в 1С
    use Traits\Trait1C;

    // "odata.metadata": "http://10.0.1.22/1c/odata/standard.odata/$metadata#Catalog_Номенклатура",
    // "value": [
    //     {
    //         "Ref_Key": "f02ce7b3-e3b3-11eb-b4ad-000c29b45b09",
    //         "DataVersion": "AAAAAQAAAAA=",
    //         "DeletionMark": false,
    //         "Parent_Key": "00000000-0000-0000-0000-000000000000",
    //         "IsFolder": false,
    //         "Code": "00-00000001",
    //         "Description": "Мойка",
    //         "Артикул": "",
    //         "ВидНоменклатуры_Key": "f40235be-d84c-11ea-8133-0050569f62a1",
    //         "ЕдиницаИзмерения_Key": "f40235d5-d84c-11ea-8133-0050569f62a1",
    //         "НаименованиеПолное": "Мойка",
    //         "Комментарий": "",
    //         "Услуга": false,
    //         "НоменклатурнаяГруппа_Key": "f40235d6-d84c-11ea-8133-0050569f62a1",
    //         "СтранаПроисхождения_Key": "00000000-0000-0000-0000-000000000000",
    //         "НомерГТД_Key": "00000000-0000-0000-0000-000000000000",
    //         "СтатьяЗатрат_Key": "00000000-0000-0000-0000-000000000000",
    //         "ОсновнаяСпецификацияНоменклатуры_Key": "00000000-0000-0000-0000-000000000000",
    //         "Производитель_Key": "00000000-0000-0000-0000-000000000000",
    //         "Импортер_Key": "00000000-0000-0000-0000-000000000000",
    //         "КодТНВЭД_Key": "00000000-0000-0000-0000-000000000000",
    //         "КодОКВЭД2_Key": "00000000-0000-0000-0000-000000000000",
    //         "КодОКВЭД_Key": "00000000-0000-0000-0000-000000000000",
    //         "КодОКП_Key": "00000000-0000-0000-0000-000000000000",
    //         "КодОКПД2_Key": "00000000-0000-0000-0000-000000000000",
    //         "УдалитьСтавкаНДС": "",
    //         "ПродукцияМаркируемаяДляГИСМ": false,
    //         "ПериодичностьУслуги": "",
    //         "КодРаздел7ДекларацииНДС_Key": "00000000-0000-0000-0000-000000000000",
    //         "ПодконтрольнаяПродукцияВЕТИС": false,
    //         "ВидСтавкиНДС": "Общая",
    //         "ТабачнаяПродукция": false,
    //         "ОбувнаяПродукция": false,
    //         "ЛегкаяПромышленность": false,
    //         "МолочнаяПродукция": false,
    //         "Шины": false,
    //         "Духи": false,
    //         "Велосипеды": false,
    //         "КреслаКоляски": false,
    //         "Фотоаппараты": false,
    //         "СредствоИндивидуальнойЗащиты": false,
    //         "КодНоменклатурнойКлассификацииККТ_Key": "00000000-0000-0000-0000-000000000000",
    //         "ДополнительныеРеквизиты": [],
    //         "Predefined": false,
    //         "PredefinedDataName": "",
    //         "ВидНоменклатуры@navigationLinkUrl": "Catalog_Номенклатура(guid'f02ce7b3-e3b3-11eb-b4ad-000c29b45b09')/ВидНоменклатуры",
    //         "ЕдиницаИзмерения@navigationLinkUrl": "Catalog_Номенклатура(guid'f02ce7b3-e3b3-11eb-b4ad-000c29b45b09')/ЕдиницаИзмерения",
    //         "НоменклатурнаяГруппа@navigationLinkUrl": "Catalog_Номенклатура(guid'f02ce7b3-e3b3-11eb-b4ad-000c29b45b09')/НоменклатурнаяГруппа"
    //     }

    // количество поставок при расчете средней цены
    protected $avg_limit_receives = 3;

    public function __construct()
    {
        parent::__construct();

        $this->name_1c('Catalog_Номенклатура');
        $this->table('nomenklatura');
        $this->has_folders_1c(true);
        $this->has_files(true);
        $this->has_images(true);
        $this->has_file_list(true);
        $this->has_groups(true);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['short_title', 'doc_title', 'manufacturer', 'ed_ism', 'okei', 'doc_type', 'ostatok', 'avg_price', 'price_with_nds']);
        // преобразователи типов
        $this->casts = array_merge([
            $this->casts, [
                'is_usluga' => 'boolean',
                'part_num' => 'string',
                'artikul' => 'string',
                'description' => 'string',
                'ostatok' => 'float',
                'avg_price' => 'float'
            ]
        ]);

        $this->model([
            ["name" => "doc_type_id", "name_1c" => "ВидНоменклатуры_Key", "type" => "select", "table" => "doc_types", "table_class" => "DocType", "title" => "Вид номенклатуры", "require" => true, "default" => 1, "index" => "index"],
            ["name" => "ed_ism_id", "name_1c" => "ЕдиницаИзмерения_Key", "type" => "select", "table" => "ed_ism", "table_class" => "EdIsm", "title" => "Единица измерения", "require" => true, "default" => 1, "index" => "index"],
            ["name" => "description", "name_1c" => "НаименованиеПолное", "type" => "string", "title" => "Описание", "require" => false],
            ["name" => "part_num", "type" => "string", "title" => "Part №", "require" => false, "index" => "index"],
            ["name" => "manufacturer_id", "type" => "select", "table" => "manufacturers", "table_class" => "Manufacturer", "title" => "Производитель", "require" => false, 'default' => 1, "index" => "index"],
            ["name" => "artikul", "name_1c" => "Артикул", "type" => "string", "title" => "Артикул", "require" => false, "index" => "index"],
            ["name" => "price", "type" => "money", "title" => "Цена без НДС", "require" => true, "index" => "index", "show_in_table" => false, "out_index" => 2, "show_in_form" => true],
            ["name" => "nds_id", "type" => "select", "table" => "nds", "table_class" => "NDS", "title" => "Ставка НДС", "require" => false, 'default' => 1, "index" => "index"],
            ["name" => "is_usluga", "name_1c" => "Услуга", "type" => "boolean", "title" => "Услуга", "require" => false, 'default' => false, "index" => "index"],
            ["name" => "ostatok", "type" => "kolvo", "title" => "Остаток", "virtual" => true, "show_in_table" => true, "show_in_form" => false],
            ["name" => "avg_price", "type" => "money", "title" => "Средняя цена", "virtual" => true, "show_in_table" => true, "show_in_form" => false],
            ["name" => "price_with_nds", "type" => "money", "title" => "Цена прайс с НДС", "virtual" => true, "show_in_table" => true, "show_in_form" => false],
        ]);

        $this->sub_tables([
            ["table" => "recipes", "icon" => "mdi-file-table-box-outline", "class" => "Nomenklatura", "method" => "recipes", "title" => "Рецептуры", "item_class" => "App\Recipe", "belongs_method" => "nomenklatura", "keys" => ["foreign" => "nomenklatura_id", "references" => "id", "foreign_table" => "recipes", "reference_table" => "nomenklatura"]],
        ]);
    }

    // читатели
    // выдаем остаток на всех складах
    public function getOstatokAttribute()
    {
        return floatval($this->sklad_register()->sum('kolvo'));
    }
    // выдаем среднюю цену за последние $avg_limit_receives поставок
    public function getAvgPriceAttribute()
    {
        return floatval($this->sklad_register()->where('price', '>', 0)->orderBy('doc_date', 'desc')->take($this->avg_limit_receives)->avg('price'));
    }
    // цена прайсовая
    public function getPriceWithNdsAttribute()
    {
        // вычислим цену без ндс
        $this_price = floatval($this->price);
        if ($this_price) {
            $price_wo_nds = $this_price;
        } else {
            $price_wo_nds = $this->avg_price * 2;
        }
        // добавим ндс
        $nds = $this->nds;
        if ($nds) {
            $price_with_nds = $price_wo_nds * (1 + $nds->stavka);
        } else {
            $price_with_nds = $price_wo_nds;
        }
        return round($price_with_nds, 2);
    }

    // выдаем наименование производителя
    public function getManufacturerAttribute()
    {
        // if (isset($this->attributes['manufacturer_id'])) {
        //     $value = $this->attributes['manufacturer_id'];
        //     return ABPCache::get_select_list('manufacturers',$value);
        // }
        $m = $this->manufacturer_()->first();
        return $m ? $m->getSelectListTitleAttribute() : '';
    }
    // выдаем единицу измерения
    public function getEdIsmAttribute()
    {
        // if (isset($this->attributes['ed_ism_id'])) {
        //     $value = $this->attributes['ed_ism_id'];
        //     return ABPCache::get_select_list('ed_ism',$value);
        // }
        $ei = $this->ed_ism_()->first();
        return $ei ? $ei->getSelectListTitleAttribute() : '';
    }
    // выдаем код по ОКЕИ
    public function getOkeiAttribute()
    {
        // if (isset($this->attributes['ed_ism_id'])) {
        //     $value = $this->attributes['ed_ism_id'];
        //     return ABPCache::get_select_list('ed_ism',$value);
        // }
        $ei = $this->ed_ism_()->first();
        return $ei ? $ei->okei : '';
    }
    // выдаем тип документа
    public function getDocTypeAttribute()
    {
        // if (isset($this->attributes['doc_type_id'])) {
        //     $value = $this->attributes['doc_type_id'];
        //     return ABPCache::get_select_list('doc_types', $value);
        // }
        $dt = $this->doc_type_()->first();
        return $dt ? $dt->getSelectListTitleAttribute() : '';
    }
    // выдаем строку для селекта
    public function getSelectListTitleAttribute()
    {
        // создадим экземпляр, если в запросе не будут указаны нужные поля
        $n = Nomenklatura::find($this->id);
        $title = $n->doc_title;
        $title .= $n->ostatok > 0 ? ' (остаток ' . $n->ostatok . ' ' . $n->ed_ism . ')' : ' (нет в наличии)';
        ABPCache::put_select_list($this->table, $this->attributes["id"], $title);
        return $title;
    }
    // выдаем наименование для документов
    public function getDocTitleAttribute()
    {
        // создадим экземпляр, если в запросе не будут указаны нужные поля
        $n = Nomenklatura::withTrashed()->find($this->id);
        // $id = $this->attributes["id"];
        // $model = $this->withTrashed()->find($this->attributes["id"]);
        $title = "";
        $fields = ["name", "artikul", "description", "part_num"];
        // $m = $model;
        foreach ($fields as $field) {
            if (isset($n->$field) && $n->$field) $title .= $n->$field . " ";
        }
        $title = trim($title);
        return $title;
    }
    // краткое наименование для отображение в ошибках и т.п.
    public function getShortTitleAttribute()
    {
        // создадим экземпляр, если в запросе не будут указаны нужные поля
        $n = Nomenklatura::withTrashed()->find($this->id);
        $title = "";
        $fields = ["name", "part_num"];
        // $m = $model;
        foreach ($fields as $field) {
            if (isset($n->$field) && $n->$field) $title .= $n->$field . " ";
        }
        $title = trim($title);
        return $title;
    }

    public function manufacturer_()
    {
        return $this->belongsTo('App\Manufacturer');
    }
    public function ed_ism_()
    {
        return $this->belongsTo('App\EdIsm', 'ed_ism_id');
    }
    public function doc_type_()
    {
        return $this->belongsTo('App\DocType');
    }
    public function nds()
    {
        return $this->belongsTo('App\NDS');
    }
    public function recipes()
    {
        return $this->hasMany('App\Recipe');
    }

    // регистры накопления по номенклатуре
    public function sklad_register()
    {
        return $this->hasMany('App\SkladRegister');
    }

    // остатки по конкретному складу
    public function scopeStock_balance($query, $sklad_id, $d = null)
    {
        if (is_null($d)) $date = date("Y-m-d");
        else $date = $d;
        return $query->whereHas('sklad_register', function ($query) use ($sklad_id, $date) {
            $query->where('sklad_id', $sklad_id)
                ->whereDate('ou_date', '<=', $date);
        })->withCount(['sklad_register as stock_balance' => function ($query) use ($sklad_id, $date) {
            // в 8-й ларе уже есть withSum('sklad_register','kolvo') - здесь пока такой костыль
            $query->select(DB::raw('SUM(kolvo)'))
                ->where('sklad_id', $sklad_id)
                ->whereDate('ou_date', '<=', $date);
        }]);
    }
    // public function scopeStock_balance($query, $sklad_id, $d=null) {
    //     if (is_null($d)) $date = date("Y-m-d"); else $date = $d;
    //     return $query->whereHas('sklad_register',function($query) use($sklad_id, $date){
    //         $query->where('sklad_id',$sklad_id)
    //             ->where('kolvo','>',0)
    //             ->whereDate('doc_date','<=', $date);
    //         })->withCount(['sklad_register as stock_balance'=>function($query) use($sklad_id,$date){
    //             // в 8-й ларе уже есть withSum('sklad_register','kolvo') - здесь пока такой костыль
    //             $query->select(\DB::raw('SUM(kolvo)'))
    //                 ->where('sklad_id',$sklad_id)
    //                 ->where('kolvo','>',0)
    //                 ->whereDate('doc_date','<=', $date);
    //         }]);
    // }


}
