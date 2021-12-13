<?php

namespace App;

use App\ABPTable;

class Order extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('orders');
        $this->has_files(true);
        $this->has_images(false);
        $this->has_groups(false);
        $this->table_type('document');
        $this->icon('mdi-cart-minus');

        // модель
        $this->model([
            ["name" => "contract_id", "type" => "key", "table" => "contracts", "table_class" => "Contract", "title" => "Договор", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "start_date", "type" => "date", "title" => "Дата начала действия", "require" => false, "index" => "index", "default" => date("Y-m-d"), "show_in_table" => true, "show_in_form" => true],
            ["name" => "end_date", "type" => "date", "title" => "Дата окончания действия", "require" => false, "index" => "index", "default" => date("Y-12-31"), "show_in_table" => true, "show_in_form" => true],
            ["name" => "manager_id", "type" => "select", "table" => "sotrudniks", "table_class" => "Sotrudnik", "title" => "Ответственный", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "executer_id", "type" => "select", "table" => "sotrudniks", "table_class" => "Sotrudnik", "title" => "Исполнитель", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "is_written", "type" => "boolean", "title" => "Подписан", "default" => "0", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "write_date", "type" => "date", "title" => "Дата подписания", "require" => false, "readonly" => true, "index" => "index", "show_in_table" => false, "show_in_form" => false],
        ]);

        // заменим стандартное поведение столбцов модели
        $this->modModel([
            ["name" => "comment", "out_index" => 1, "show_in_table" => true]
        ]);

        // подчиненные таблицы
        $this->sub_tables([
            ["table" => "order_items", "class" => "OrderItem", "method" => "items", "title" => "Позиции заказа", "item_class" => "App\OrderItem", "belongs_method" => "order", "keys" => ["foreign" => "order_id", "references" => "id", "foreign_table" => "order_items", "reference_table" => "orders"]],
            ["table" => "invoices", "icon" => "mdi-receipt", "class" => "Invoice", "method" => "invoices", "title" => "Счета", "item_class" => "App\Invoice", "belongs_method" => "order", "keys" => ["foreign" => "order_id", "references" => "id", "foreign_table" => "invoices", "reference_table" => "orders"]],
            ["table" => "acts", "icon" => "mdi-view-compact-outline", "class" => "Act", "method" => "acts", "title" => "Реализации", "item_class" => "App\Act", "belongs_method" => "order", "keys" => ["foreign" => "order_id", "references" => "id", "foreign_table" => "acts", "reference_table" => "orders"]],
        ]);
    }

    // связи
    // контрагент договора
    public function contract()
    {
        return $this->belongsTo('App\Contract', 'contract_id');
    }
    // позиции заказа
    public function items()
    {
        return $this->hasMany('App\OrderItem');
    }
    // счета
    public function invoices()
    {
        return $this->hasMany('App\Invoice');
    }
    // акты
    public function acts()
    {
        return $this->hasMany('App\Act');
    }
}