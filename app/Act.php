<?php

namespace App;

use App\ABPTable;

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


        // модель
        $this->model([
            ["name" => "order_id", "type" => "key", "table" => "orders", "table_class" => "Order", "title" => "Заказ", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "sklad_id", "type" => "select", "table" => "sklads", "table_class" => "Sklad", "title" => "Склад", "require" => true, "default" => 1, "index" => "index", "show_in_table" => true],
            ["name" => "period_start_date", "type" => "date", "title" => "Дата начала периода", "require" => false, "index" => "index", "default" => date("Y-m-d"), "show_in_table" => false, "show_in_form" => false],
            ["name" => "period_end_date", "type" => "date", "title" => "Дата окончания периода", "require" => false, "index" => "index", "default" => date("Y-12-31"), "show_in_table" => false, "show_in_form" => false],
            ["name" => "summa", "type" => "money", "title" => "Сумма", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "summa_nds", "type" => "money", "title" => "Сумма НДС", "require" => false, 'default' => 0, "index" => "index", "show_in_table" => false, "show_in_form" => false],
        ]);

        // подчиненные таблицы
        $this->sub_tables([
            ["table" => "act_items", "class" => "ActItem", "method" => "items", "title" => "Позиции накладной", "item_class" => "App\ActItem", "belongs_method" => "act", "keys" => ["foreign" => "act_id", "references" => "id", "foreign_table" => "act_items", "reference_table" => "acts"]],
        ]);
    }
}