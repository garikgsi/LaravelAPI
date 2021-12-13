<?php

namespace App;

use App\ABPTable;


class Contract extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('contracts');
        $this->has_files(true);
        $this->has_images(false);
        $this->has_groups(false);

        $this->model([
            ["name" => "contract_num", "type" => "string", "max" => 32, "title" => "№ договора", "require" => true, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 1],
            ["name" => "contract_date", "type" => "date", "title" => "Дата договора", "require" => true, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 2],
            ["name" => "firm_id", "type" => "select", "table" => "firms", "table_class" => "Firm", "title" => "Организация", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false, "out_index" => 4],
            ["name" => "contractable", "title" => "Контрагент", "type" => "morph", "tables" => [["table" => "kontragents", "title" => "Контрагенты", "type" => "App\\Kontragent"]], "require" => true, "props" => ["edit" => ["tableSelectorReadonly" => true]], "show_in_table" => false],
            ["name" => "contract_type_id", "type" => "select", "table" => "contract_types", "table_class" => "ContractType", "title" => "Вид договора", "require" => true, "default" => 1, "index" => "index", "show_in_table" => true],
            [
                "name" => "rs_id", "type" => "foreign_select", "structure" =>
                [
                    ["table" => "firms", "title" => "организацию"],
                    ["table" => "rs", "title" => "р/сч", "key" => ["rs_table" => ["rs_table_type" => "App\Firm", "rs_table_id" => "rs_table_id"]]]
                ],
                "title" => "Расчетный счет", "default" => 1, "index" => "index", "readonly" => ["edit", "copy"], "require" => true, "show_in_table" => false, "show_in_form" => true, "out_index" => 5
            ],
            ["name" => "is_written", "type" => "boolean", "title" => "Подписан", "default" => "0", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => false, "out_index" => 7],
            ["name" => "end_date", "type" => "date", "title" => "Действует до", "require" => false, "index" => "index", "default" => date("Y-12-31"), "show_in_table" => false, "show_in_form" => true],
            ["name" => "write_date", "type" => "date", "title" => "Дата подписания", "require" => false, "readonly" => true, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "summa", "type" => "money", "title" => "Сумма договора", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "kontragent", "type" => "string", "virtual" => true, "title" => "Контрагент", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['kontragent', 'contract_type']);

        // подчиненные таблицы
        $this->sub_tables([
            ["table" => "orders", "icon" => "mdi-cart-minus", "class" => "Order", "method" => "orders", "title" => "Заказы", "item_class" => "App\Order", "belongs_method" => "contract", "keys" => ["foreign" => "contract_id", "references" => "id", "foreign_table" => "orders", "reference_table" => "contracts"]],
        ]);
    }

    // связи
    // контрагент договора
    public function contractable()
    {
        return $this->morphTo();
    }
    // вид договора
    public function contract_type_()
    {
        return $this->belongsTo('App\ContractType', 'contract_type_id', 'id');
    }
    // заказы
    public function orders()
    {
        return $this->hasMany('App\Order');
    }


    // читатели
    // контрагент
    public function getKontragentAttribute()
    {
        return $this->contractable ? $this->contractable->getSelectListTitleAttribute() : '';
    }
    // вид договора
    public function getContractTypeAttribute()
    {
        $contract_type = $this->contract_type_()->first();
        return $contract_type ? $contract_type->getSelectListTitleAttribute() : '';
    }
}