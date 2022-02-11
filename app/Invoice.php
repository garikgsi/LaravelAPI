<?php

namespace App;

use App\ABPTable;
use Carbon\Carbon;
use App\Common\PrintFormFormats;

class Invoice extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('invoices');
        $this->has_files(true);
        $this->has_images(false);
        $this->has_groups(false);
        $this->table_type('document');

        // модель
        $this->model([
            ["name" => "order_id", "type" => "key", "table" => "orders", "table_class" => "Order", "title" => "Заказ", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "period_start_date", "type" => "date", "title" => "Дата начала периода", "require" => false, "index" => "index", "default" => date("Y-m-d"), "show_in_table" => false, "show_in_form" => false],
            ["name" => "period_end_date", "type" => "date", "title" => "Дата окончания периода", "require" => false, "index" => "index", "default" => date("Y-12-31"), "show_in_table" => false, "show_in_form" => false],
            ["name" => "summa", "type" => "money", "title" => "Сумма", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "summa_nds", "type" => "money", "title" => "Сумма НДС", "require" => false, 'default' => 0, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            // виртуальные столбцы
            ["name" => "kontragent", "type" => "string", "virtual" => true, "title" => "Контрагент", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "firm", "type" => "string", "virtual" => true, "title" => "Организация", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "contract_type", "type" => "string", "virtual" => true, "title" => "Вид договора", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "order", "type" => "string", "virtual" => true, "title" => "Заказ", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "sum", "type" => "money", "virtual" => true, "title" => "Сумма", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            // фильтры
            ["name" => "order_.contract_.contractable", "type" => "morph", "tables" => [["table" => "kontragents", "title" => "Контрагенты", "type" => "App\\Kontragent"]], "filter" => true, "virtual" => true, "title" => "Контрагент", "show_in_table" => false, "show_in_form" => false],
            ["name" => "items.nomenklatura_.groups", "type" => "groups", "table" => "nomenklatura", "filter" => true, "virtual" => true, "title" => "Контрагент", "show_in_table" => false, "show_in_form" => false],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['order', 'kontragent', 'firm', 'contract_type', 'sklad', 'sum', 'ddate']);

        // подчиненные таблицы
        $this->sub_tables([
            ["table" => "invoice_items", "class" => "InvoiceItem", "method" => "items", "title" => "Позиции счета", "item_class" => "App\InvoiceItem", "belongs_method" => "invoice", "keys" => ["foreign" => "invoice_id", "references" => "id", "foreign_table" => "invoice_items", "reference_table" => "invoices"]],
        ]);
    }

    // связи
    // позиции счета
    public function items()
    {
        return $this->hasMany('App\InvoiceItem');
    }
    // заказ
    public function order_()
    {
        return $this->belongsTo('App\Order', 'order_id');
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
        $doc_date = Carbon::createFromFormat('Y-m-d', $this->doc_date)->locale('ru');
        // начальные данные
        $table_data_arr = [
            "doc_num" => $this->doc_num,
            "doc_date" => $doc_date->format('d ' . $doc_date->getTranslatedMonthName('Do MMMM') . ' Y г.'),
            // "doc_date" => $doc_date->format('d.m.Y'),
            // комментарий
            'comment' => $this->comment,
            // подписанты
            // руководитель
            'firm_ceo' => 'Мишуров Е. Е.',
            // бухгалтер
            'firm_account' => 'Меньшикова А.К.',
        ];
        $order = $this->order_()->first();
        if ($order) {
            $contract = $order->contract_()->first();
            if ($contract) {
                // расчетный счет
                $rs = $contract->rs_()->first();
                if ($rs) {
                    $table_data_arr += [
                        // реквизиты получателя
                        'firm_bank' => $rs->bank,
                        'firm_bik' => $rs->bik,
                        'firm_ks' => $rs->ks,
                        'firm_rs' => $rs->s_num,
                        'valuta' => $rs->valuta
                    ];
                }

                // организация
                $firm = $contract->firm_()->first();
                if ($firm) {
                    $table_data_arr += [
                        'firm_inn' => $firm->inn,
                        'firm_kpp' => $firm->kpp,
                        'firm_name' => $firm->short_name,
                        // основание
                        'osnovanie' => '',
                    ];
                }
                // поставщик 1 строкой
                $table_data_arr['firm_str'] = $table_data_arr['firm_name'] . ' ИНН ' . $table_data_arr['firm_inn'] . ', КПП ' . $table_data_arr['firm_kpp'] . ', 107370,Москва,Открытое шоссе,д.12,стр.3, пом.XIII';

                // контрагент
                $kontragent = $contract->contractable()->first();
                if ($kontragent) {
                    $table_data_arr += [
                        'kontragent_str' => $kontragent->full_name . ', ИНН ' . $kontragent->inn
                    ];
                }
            }
        }
        // табличная часть
        $items = [];
        // итоговая часть
        $itogs = [
            "summa" => 0,
            "sum_nds" => 0,
            "sum_sum" => 0,
            "kolvo" => 0
        ];

        // № п/п
        $npp = 1;
        foreach ($table_items as $item) {
            $row = [
                "npp" => $npp, //_N14
            ];
            $nomenklatura = $item->nomenklatura_()->first();
            if ($nomenklatura) {
                $row["name"] = $nomenklatura->name;
                // единица измерения
                $ed_ism = $nomenklatura->ed_ism_()->first();
                if ($ed_ism) {
                    $row["ed_ism"] = $nomenklatura->ed_ism;
                }
            }
            $row["kolvo"] = PrintFormFormats::format_kolvo($item->kolvo);
            $row["price"] = PrintFormFormats::format_money($item->price);
            $row["summa"] = PrintFormFormats::format_money($item->summa);
            // НДС
            $nds = $item->nds_()->first();
            if ($nds) {
                // суммы указаны с НДС - выделяем его
                $sum_nds = $item->summa / (1 + $nds->stavka) * $nds->stavka;
                $itogs["sum_nds"] += $sum_nds;
            }
            // суммы
            $itogs["summa"] += $item->summa;
            // общее кол-во
            $itogs['kolvo']++;
            // добавляем строку в табличную часть
            $items[] = (object)$row;
            // инкремент #п/п
            $npp++;
        }
        // преобразуем итоги
        foreach ($itogs as $key => $value) {
            switch ($key) {
                case 'summa': {
                        // итоговые суммы прописью
                        $itogs["summa_propis"] = PrintFormFormats::propis($itogs["summa"]);
                        $itogs[$key] = PrintFormFormats::format_money($value);
                    }
                    break;
                case 'kolvo': {
                        $itogs[$key] = PrintFormFormats::format_int($value);
                    }
                    break;
                default: {
                        $itogs[$key] = PrintFormFormats::format_money($value);
                    }
            }
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