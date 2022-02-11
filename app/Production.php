<?php

namespace App;

use App\ABPTable;
use Carbon\Carbon;
use PhpParser\Node\Stmt\TryCatch;

class Production extends ABPTable
{
    // экспорт в 1С
    use Traits\Trait1C;


    // "odata.metadata": "http://10.0.1.22/1c/odata/standard.odata/$metadata#Document_ОтчетПроизводстваЗаСмену",
    // "value": [
    //     {
    //         "Ref_Key": "f02ce7b7-e3b3-11eb-b4ad-000c29b45b09",
    //         "DataVersion": "AAAAAQAAAAA=",
    //         "DeletionMark": false,
    //         "Number": "0000-000001",
    //         "Date": "2021-07-13T11:30:00",
    //         "Posted": false,
    //         "Организация_Key": "87fa6cc2-e901-11ea-8591-000c29b45b09",
    //         "Склад_Key": "f40235d3-d84c-11ea-8133-0050569f62a1",
    //         "ПодразделениеОрганизации_Key": "00000000-0000-0000-0000-000000000000",
    //         "СчетЗатрат_Key": "86eff6b6-d84c-11ea-8133-0050569f62a1",
    //         "ПодразделениеЗатрат_Key": "87fa6cc3-e901-11ea-8591-000c29b45b09",
    //         "НДСвСтоимостиТоваров": "НеИзменять",
    //         "ДляСписанияНДСиспользоватьСчетИАналитикуУчетаЗатрат": true,
    //         "СчетСписанияНДС_Key": "00000000-0000-0000-0000-000000000000",
    //         "СубконтоСписанияНДС1": "",
    //         "СубконтоСписанияНДС1_Type": "StandardODATA.Undefined",
    //         "СубконтоСписанияНДС2": "",
    //         "СубконтоСписанияНДС2_Type": "StandardODATA.Undefined",
    //         "СубконтоСписанияНДС3": "",
    //         "СубконтоСписанияНДС3_Type": "StandardODATA.Undefined",
    //         "РучнаяКорректировка": false,
    //         "Ответственный_Key": "10f78d15-dfe4-11eb-b4ad-000c29b45b09",
    //         "Комментарий": "",
    //         "Продукция": [
    //             {
    //                 "Ref_Key": "f02ce7b7-e3b3-11eb-b4ad-000c29b45b09",
    //                 "LineNumber": "1",
    //                 "Номенклатура_Key": "f02ce7b3-e3b3-11eb-b4ad-000c29b45b09",
    //                 "КоличествоМест": 0,
    //                 "ЕдиницаИзмерения_Key": "f40235d5-d84c-11ea-8133-0050569f62a1",
    //                 "Коэффициент": 1,
    //                 "Количество": 1,
    //                 "ПлановаяСтоимость": 0,
    //                 "СуммаПлановая": 0,
    //                 "Спецификация_Key": "00000000-0000-0000-0000-000000000000",
    //                 "Счет_Key": "86eff6ca-d84c-11ea-8133-0050569f62a1",
    //                 "НоменклатурнаяГруппа_Key": "f40235d6-d84c-11ea-8133-0050569f62a1"
    //             }
    //         ],
    //         "Услуги": [
    //             {
    //                 "Ref_Key": "f02ce7b7-e3b3-11eb-b4ad-000c29b45b09",
    //                 "LineNumber": "1",
    //                 "Номенклатура_Key": "f02ce7b6-e3b3-11eb-b4ad-000c29b45b09",
    //                 "Количество": 1,
    //                 "СуммаПлановая": 0,
    //                 "Счет_Key": "86eff769-d84c-11ea-8133-0050569f62a1",
    //                 "ПодразделениеЗатрат_Key": "87fa6cc3-e901-11ea-8591-000c29b45b09",
    //                 "Субконто1": "00000000-0000-0000-0000-000000000000",
    //                 "Субконто1_Type": "StandardODATA.Catalog_Контрагенты",
    //                 "Субконто2": "00000000-0000-0000-0000-000000000000",
    //                 "Субконто2_Type": "StandardODATA.Catalog_ДоговорыКонтрагентов",
    //                 "Субконто3": "",
    //                 "Субконто3_Type": "StandardODATA.Undefined",
    //                 "Спецификация_Key": "00000000-0000-0000-0000-000000000000",
    //                 "НоменклатурнаяГруппа_Key": "f40235d6-d84c-11ea-8133-0050569f62a1",
    //                 "ПлановаяСтоимость": 0
    //             }
    //         ],
    //         "ВозвратныеОтходы": [],
    //         "Материалы": [
    //             {
    //                 "Ref_Key": "f02ce7b7-e3b3-11eb-b4ad-000c29b45b09",
    //                 "LineNumber": "1",
    //                 "Номенклатура_Key": "f02ce7b4-e3b3-11eb-b4ad-000c29b45b09",
    //                 "Счет_Key": "86eff64f-d84c-11ea-8133-0050569f62a1",
    //                 "КоличествоМест": 0,
    //                 "ЕдиницаИзмерения_Key": "f40235d5-d84c-11ea-8133-0050569f62a1",
    //                 "Коэффициент": 1,
    //                 "Количество": 1,
    //                 "ОтражениеВУСН": "Принимаются",
    //                 "ДокументОприходования": "",
    //                 "ДокументОприходования_Type": "StandardODATA.Undefined",
    //                 "Себестоимость": 0,
    //                 "НоменклатурнаяГруппа_Key": "f40235d6-d84c-11ea-8133-0050569f62a1",
    //                 "СтатьяЗатрат_Key": "f4023598-d84c-11ea-8133-0050569f62a1",
    //                 "СпособУчетаНДС": "",
    //                 "Продукция_Key": "00000000-0000-0000-0000-000000000000"
    //             },
    //             {
    //                 "Ref_Key": "f02ce7b7-e3b3-11eb-b4ad-000c29b45b09",
    //                 "LineNumber": "2",
    //                 "Номенклатура_Key": "f02ce7b5-e3b3-11eb-b4ad-000c29b45b09",
    //                 "Счет_Key": "86eff64f-d84c-11ea-8133-0050569f62a1",
    //                 "КоличествоМест": 0,
    //                 "ЕдиницаИзмерения_Key": "f40235d4-d84c-11ea-8133-0050569f62a1",
    //                 "Коэффициент": 1,
    //                 "Количество": 300,
    //                 "ОтражениеВУСН": "Принимаются",
    //                 "ДокументОприходования": "",
    //                 "ДокументОприходования_Type": "StandardODATA.Undefined",
    //                 "Себестоимость": 0,
    //                 "НоменклатурнаяГруппа_Key": "f40235d6-d84c-11ea-8133-0050569f62a1",
    //                 "СтатьяЗатрат_Key": "f4023598-d84c-11ea-8133-0050569f62a1",
    //                 "СпособУчетаНДС": "",
    //                 "Продукция_Key": "00000000-0000-0000-0000-000000000000"
    //             }
    //         ],
    //         "Организация@navigationLinkUrl": "Document_ОтчетПроизводстваЗаСмену(guid'f02ce7b7-e3b3-11eb-b4ad-000c29b45b09')/Организация",
    //         "Склад@navigationLinkUrl": "Document_ОтчетПроизводстваЗаСмену(guid'f02ce7b7-e3b3-11eb-b4ad-000c29b45b09')/Склад",
    //         "СчетЗатрат@navigationLinkUrl": "Document_ОтчетПроизводстваЗаСмену(guid'f02ce7b7-e3b3-11eb-b4ad-000c29b45b09')/СчетЗатрат",
    //         "ПодразделениеЗатрат@navigationLinkUrl": "Document_ОтчетПроизводстваЗаСмену(guid'f02ce7b7-e3b3-11eb-b4ad-000c29b45b09')/ПодразделениеЗатрат",
    //         "Ответственный@navigationLinkUrl": "Document_ОтчетПроизводстваЗаСмену(guid'f02ce7b7-e3b3-11eb-b4ad-000c29b45b09')/Ответственный"
    //     }

    public function __construct()
    {
        parent::__construct();

        $this->table('productions');
        $this->name_1c('Document_ОтчетПроизводстваЗаСмену');
        $this->table_type('document');
        $this->sub_tables([
            ["table" => "production_items", "class" => "Production", "method" => "items", "title" => "Изделия", "item_class" => "App\ProductionItem", "belongs_method" => "production"],
            ["table" => "production_replaces", "class" => "Production", "method" => "replaces", "title" => "Замены", "item_class" => "App\ProductionReplace", "belongs_method" => "production"],
        ]);

        $this->model([
            ["name" => "sklad_id", "type" => "select", "table" => "sklads", "table_class" => "Sklad", "title" => "Склад", "default" => 1, "index" => "index", "require" => true, "show_in_table" => true, "show_in_form" => true, "out_index" => 4],
            ["name" => "firm_id", "name_1c" => "Организация_Key", "type" => "select", "table" => "firms", "table_class" => "Firm", "title" => "Организация", "default" => 1, "index" => "index", "require" => true, "show_in_table" => false, "show_in_form" => true, "out_index" => 7],
            [
                "name" => "recipe_id", "type" => "foreign_select", "structure" =>
                [
                    ["table" => "nomenklatura", "title" => "изделие"],
                    ["table" => "recipes", "title" => "рецептуру", "key" => "nomenklatura_id"]
                ],
                "title" => "Изделие (рецептура)", "default" => 1, "index" => "index", "readonly" => ["edit", "copy"], "require" => true, "show_in_table" => true, "show_in_form" => true, "out_index" => 5
            ],
            ["name" => "kolvo", "type" => "kolvo", "title" => "Количество", "readonly" => ["edit"], "require" => true, 'default' => 1, "index" => "index", "show_in_table" => true, "out_index" => 6, "show_in_form" => true],
            ["name" => "commission_member1", "type" => "select", "table" => "sotrudniks", "table_class" => "Firm", "title" => "Член комиссии №1", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "commission_member2", "type" => "select", "table" => "sotrudniks", "table_class" => "Firm", "title" => "Член комиссии №2", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "commission_chairman", "type" => "select", "table" => "sotrudniks", "table_class" => "Firm", "title" => "Председатель комиссии", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
        ]);

        // преобразователи типов
        $this->casts = array_merge([
            $this->casts, [
                'kolvo' => 'float'
            ]
        ]);

        // добавляем читателей
        $this->hidden = array_merge($this->hidden, ['zip_components']);
        $this->appends = array_merge($this->appends, ['doc_date_rus', 'recipe', 'sklad', 'replaces', 'nomenklatura_id', 'nomenklatura', 'ed_ism', 'commission', 'firm', 'zip_components']);
    }

    public function items()
    {
        return $this->hasMany('App\ProductionItem', 'production_id', 'id');
    }

    public function recipes()
    {
        return $this->belongsTo('App\Recipe', 'recipe_id', 'id');
    }
    public function sklads()
    {
        return $this->belongsTo('App\Sklad', 'sklad_id', 'id');
    }
    public function sklad_()
    {
        return $this->belongsTo('App\Sklad', 'sklad_id', 'id');
    }
    public function firm_()
    {
        return $this->belongsTo('App\Firm', 'firm_id', 'id');
    }
    public function commission_member1_()
    {
        return $this->belongsTo('App\Sotrudnik', 'commission_member1', 'id');
    }
    public function commission_member2_()
    {
        return $this->belongsTo('App\Sotrudnik', 'commission_member2', 'id');
    }
    public function commission_chairman_()
    {
        return $this->belongsTo('App\Sotrudnik', 'commission_chairman', 'id');
    }
    public function product()
    {
        // return $this->recipes()->first()->nomenklatura()->first();
        // dd($this->recipes());
        try {
            return $this->recipes() ? $this->recipes->nomenklatura : null;
        } catch (\Throwable $th) {
            dd($th);
        }
    }
    public function replaces()
    {
        return $this->hasMany('App\ProductionReplace');
    }

    // читатели
    // для select-ов
    public function getSelectListTitleAttribute()
    {
        return 'Производство №' . $this->doc_num . ' от ' . $this->doc_date_rus;
    }
    // дата документа в русском формате
    public function getDocDateRusAttribute()
    {
        $doc_date = Carbon::createFromFormat('Y-m-d', $this->doc_date);
        return $doc_date->format('d.m.Y');
    }
    // рецепт
    public function getRecipeAttribute()
    {
        return $this->product()->getSelectListTitleAttribute() . " (" . $this->recipes()->first()->getSelectListTitleAttribute() . ")";
    }
    // id номенклатуры готового изделия
    public function getNomenklaturaIdAttribute()
    {
        return $this->product()->id;
    }
    // номенклатура готового изделия
    public function getNomenklaturaAttribute()
    {
        return $this->product()->getSelectListTitleAttribute();
    }
    // ед.изм готового изделия
    public function getEdIsmAttribute()
    {
        return $this->product()->first()->edism;
    }
    // склад
    public function getSkladAttribute()
    {
        $s = $this->sklad_()->first();
        return is_null($s) ? '' : $s->getSelectListTitleAttribute();
        // return '';
    }
    // фирма
    public function getFirmAttribute()
    {
        $f = $this->firm_()->first();
        return is_null($f) ? '' : $f->getSelectListTitleAttribute();
        // return '';
    }
    // замены
    public function getReplacesAttribute()
    {
        return $this->replaces()->get();
    }
    // комиссия
    public function getCommissionAttribute()
    {
        return [
            "commission_member1" => [
                "position" => $this->commission_member1_ ? $this->commission_member1_->firm_position : '',
                "name" => $this->commission_member1_ ? $this->commission_member1_->short_fio : ''
            ],
            "commission_member2" => [
                "position" => $this->commission_member2_ ? $this->commission_member2_->firm_position : '',
                "name" => $this->commission_member2_ ? $this->commission_member2_->short_fio : ''
            ],
            "commission_chairman" => [
                "position" => $this->commission_chairman_ ? $this->commission_chairman_->firm_position : '',
                "name" => $this->commission_chairman_ ? $this->commission_chairman_->short_fio : ''
            ]
        ];
    }
    // компоненты всех произведенных изделий (свернутые данные)
    public function getZipComponentsAttribute()
    {
        $res = [];
        // $t = $this->replicate();
        // $items = $t->items;
        $items = $this->items;
        foreach ($items as $item) {
            $components = $item->components;
            foreach ($components as $component) {
                $n = $component->nomenklatura_id;
                $k = floatVal($component->kolvo);
                if (isset($res[$n])) {
                    $res[$n] += $k;
                } else {
                    $res[$n] = $k;
                }
            }
        }
        // dd($res);
        $data = [];
        foreach ($res as $nomenklatura_id => $kolvo) {
            $data[] = [
                "nomenklatura_id" => $nomenklatura_id,
                "kolvo" => $kolvo
            ];
        }
        return $data;
    }


    // печатные формы
    public function pf_data()
    {
        // табличная часть
        $items = $this->items;
        $table_data_arr = [];
        foreach ($items as $item) {
            $components = $item->components;
            foreach ($components as $component) {
                if (isset($table_data_arr[$component->nomenklatura_id])) {
                    $table_data_arr[$component->nomenklatura_id]["kolvo"] += floatval($component->kolvo);
                } else {
                    if ($component->kolvo > 0) {
                        $table_data_arr[$component->nomenklatura_id] = [
                            "nomenklatura_id" => $component->nomenklatura_id,
                            "nomenklatura" => $component->nomenklatura,
                            "artikul" => $component->component->artikul,
                            "okei" => $component->component->okei,
                            "ed_ism" => $component->ed_ism,
                            "kolvo" => floatval($component->kolvo),
                            "price" => 0,
                            "summa" => 0
                        ];
                    }
                }
            }
        }
        // итоговая часть
        $itogs = [
            "sum_production" => 0,
            "sum_components" => 0,
            "total" => 0
        ];
        $res = [
            "table" => collect($table_data_arr)->values(),
            "itogs" => $itogs
        ];
        return $res;
    }
}
