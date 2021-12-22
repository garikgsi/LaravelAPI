<?php

namespace App;

use App\ABPTable;
use Carbon\Carbon;

class Act extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('acts');
        $this->has_files(true);
        $this->has_images(false);
        $this->has_groups(false);
        $this->table_type('document');
        $this->icon('mdi-human-dolly');


        // модель
        $this->model([
            ["name" => "order_id", "type" => "key", "table" => "orders", "table_class" => "Order", "title" => "Заказ", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "sklad_id", "type" => "select", "table" => "sklads", "table_class" => "Sklad", "title" => "Склад", "require" => true, "default" => 1, "index" => "index", "show_in_table" => true, "out_index" => 3],
            ["name" => "period_start_date", "type" => "date", "title" => "Дата начала периода", "require" => false, "index" => "index", "default" => date("Y-m-d"), "show_in_table" => false, "show_in_form" => false],
            ["name" => "period_end_date", "type" => "date", "title" => "Дата окончания периода", "require" => false, "index" => "index", "default" => date("Y-12-31"), "show_in_table" => false, "show_in_form" => false],
            ["name" => "summa", "type" => "money", "title" => "Сумма", "require" => false, "default" => 0, "index" => "index", "show_in_table" => false, "show_in_form" => false, "readonly" => true],
            ["name" => "summa_nds", "type" => "money", "title" => "Сумма НДС", "require" => false, 'default' => 0, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            // виртуальные столбцы
            ["name" => "kontragent", "type" => "string", "virtual" => true, "title" => "Контрагент", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "firm", "type" => "string", "virtual" => true, "title" => "Организация", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "contract_type", "type" => "string", "virtual" => true, "title" => "Вид договора", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "order", "type" => "string", "virtual" => true, "title" => "Заказ", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "sum", "type" => "money", "virtual" => true, "title" => "Сумма", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['order', 'kontragent', 'firm', 'contract_type', 'sklad', 'sum', 'ddate']);

        // подчиненные таблицы
        $this->sub_tables([
            ["table" => "act_items", "class" => "ActItem", "method" => "items", "title" => "Позиции накладной", "item_class" => "App\ActItem", "belongs_method" => "act", "keys" => ["foreign" => "act_id", "references" => "id", "foreign_table" => "act_items", "reference_table" => "acts"]],
        ]);
    }

    // связи
    // позиции накладной
    public function items()
    {
        return $this->hasMany('App\ActItem');
    }
    // заказ
    public function order_()
    {
        return $this->belongsTo('App\Order', 'order_id');
    }
    // склад
    public function sklad_()
    {
        return $this->belongsTo('App\Sklad', 'sklad_id');
    }

    // читатели
    // форматированная дата документа
    public function getDdateAttribute()
    {
        $doc_date = Carbon::createFromFormat('Y-m-d', $this->doc_date);
        return $doc_date ? $doc_date->format('d.m.Y') : $this->doc_date;
    }
    // заказ
    public function getOrderAttribute()
    {
        if (isset($this->order_id)) {
            $order = $this->order_()->first();
            return $order ? $order->getSelectListTitleAttribute() : '';
        }
        return '';
    }
    // склад
    public function getSkladAttribute()
    {
        if (isset($this->sklad_id)) {
            $sklad = $this->sklad_()->first();
            // dd($sklad->getSelectListTitleAttribute());
            return $sklad ? $sklad->getSelectListTitleAttribute() : '';
        }
        return '';
    }
    // организация
    public function getFirmAttribute()
    {
        $order = $this->order_()->first();
        if ($order) {
            return $order->firm;
        }
        return '';
    }
    // контрагент
    public function getKontragentAttribute()
    {
        $order = $this->order_()->first();
        if ($order) {
            return $order->kontragent;
        }
        return '';
    }
    // вид договора
    public function getContractTypeAttribute()
    {
        $order = $this->order_()->first();
        if ($order) {
            return $order->contract_type;
        }
        return '';
    }
    // сумма реализации
    public function getSumAttribute()
    {
        $items = collect($this->items()->get());
        // dd($items->toArray());
        return $items->sum('summa');
    }

    // печатные формы
    public function pf_data()
    {
        // табличная часть
        $table_items = $this->items;
        // дата документа
        $doc_date = Carbon::createFromFormat('Y-m-d', $this->doc_date);
        // начальные данные
        $table_data_arr = [
            "status" => 1, //_N0
            "doc_num" => $this->doc_num, //_N1
            "doc_date" => $doc_date->format('d.m.Y'), //_N2
            "saler" => '', //_N4
            // пустые по умолчанию
            'saler_addr' => '',
            'saler_inn_kpp' => '',
            'saler_go_addr' => '',
            'saler_addr' => '',
            'saler_addr' => '',
            'buyer_gp_addr' => '',
            'buyer_pp_num' => '',
            'buyer_pp_date' => '',
            'buyer' => '',
            'buyer_addr' => '',
            'buyer_inn_kpp' => '',
            'valuta' => '',
            'podpis_ceo' => '',
            'podpis_account' => '',
            'ip_fio' => '',
            'ip_ogrnip' => '',
            'doverennost' => '',
            'manager_firm_position' => '',
            'manager_fio' => '',
            'keeper_fio' => '',
            'keeper_firm_position' => '',
            'out_date_date' => '',
            'out_date_month' => '',
            'out_date_year' => '',
        ];
        $order = $this->order_()->first();
        if ($order) {
            $contract = $order->contract_()->first();
            if ($contract) {
                // организация
                $firm = $contract->firm_()->first();
                if ($firm) {
                    $table_data_arr["saler"] = $firm->short_name; // _N4
                    $table_data_arr["saler_addr"] = ''; // _N5
                    $table_data_arr["saler_inn_kpp"] = $firm->inn . ' / ' . $firm->kpp; // _N6
                    $table_data_arr["saler_go_addr"] = $firm->short_name . ' '; // _N7
                    $table_data_arr["saler_addr"] = ''; // _N5
                }
                // контрагент
                $kontragent = $contract->contractable()->first();
                if ($kontragent) {
                    $table_data_arr["buyer_gp_addr"] = $kontragent->full_name . ' ' . $kontragent->address; //_N8
                    $table_data_arr["buyer_pp_num"] = ''; //_N9
                    $table_data_arr["buyer_pp_date"] = ''; //_N10
                    $table_data_arr["buyer"] = $kontragent->full_name; //_N11
                    $table_data_arr["buyer_addr"] = $kontragent->address; //_N12
                    $table_data_arr["buyer_inn_kpp"] = $kontragent->inn . ' / ' . $kontragent->kpp; //_N12
                }
            }
            $table_data_arr["valuta"] = 'руб.'; // _N14
        }
        // кол-во листов
        $table_data_arr["pages_count"] = ''; //_N29
        // подписанты
        // руководитель
        $table_data_arr["podpis_ceo"] = ''; //_N30
        $table_data_arr["podpis_account"] = ''; //_N31
        $table_data_arr["ip_fio"] = ''; //_N32
        $table_data_arr["ip_ogrnip"] = ''; //_N33
        $table_data_arr["doverennost"] = ''; //_N34
        // ответственный
        $table_data_arr["manager_firm_position"] = ''; //_N40
        $table_data_arr["manager_fio"] = ''; //_N41

        // складарь
        $sklad = $this->sklad_()->first();
        if ($sklad) {
            $keeper = $sklad->keeper()->first();
            if ($keeper) {
                $table_data_arr["keeper_fio"] = $keeper->short_fio; //_N36
                $table_data_arr["keeper_firm_position"] = $keeper->firm_position; //_N35
            }
        }
        // дата отгрузки
        $table_data_arr["out_date_date"] = $doc_date->format('d'); //_N37
        $table_data_arr["out_date_month"] = $doc_date->translatedFormat('F'); //_N37
        $table_data_arr["out_date_year"] = $doc_date->translatedFormat('y'); //_N39

        // табличная часть
        $items = [];
        // итоговая часть
        $itogs = [
            "summa" => 0,
            "sum_nds" => 0,
            "sum_sum" => 0
        ];

        // № п/п
        $npp = 1;
        foreach ($table_items as $item) {
            $row = [
                "npp" => $npp, //_N14
            ];
            $nomenklatura = $item->nomenklatura_()->first();
            if ($nomenklatura) {
                $row["code"] = $nomenklatura->artikul; //_N15
                $row["name"] = $nomenklatura->name; //_N16
                $row["code_tov"] = ''; //_N17
                // единица измерения
                $ed_ism = $nomenklatura->ed_ism_()->first();
                if ($ed_ism) {
                    $row["ed_ism_code"] = $ed_ism->okei; //_N18
                    $row["ed_ism"] = $nomenklatura->ed_ism; //_N19
                }
            }
            $row["kolvo"] = $item->kolvo; //_N20
            $row["price"] = $item->price; //_N21
            $row["summa"] = $item->summa; //_N22
            // НДС
            $nds = $item->nds_()->first();
            if ($nds) {
                $row["stavka_nds"] = $nds->comment; //_N23
            }
            $row["sum_nds"] = $item->summa_nds; //_N24
            $row["sum"] = $item->summa_nds + $item->summa; //_N25
            // суммы
            $itogs["summa"] += $row["summa"]; //_N26
            $itogs["sum_nds"] += $row["sum_nds"]; //_N27
            $itogs["sum_sum"] += $row["sum"]; //_N28
            // добавляем строку в табличную часть
            $items[] = (object)$row;
            // инкремент #п/п
            $npp++;
        }
        // формируем результат
        $res = [
            "doc" => $table_data_arr,
            "table" => $items,
            "itogs" => $itogs
        ];
        // dd($res);
        return $res;
    }
}