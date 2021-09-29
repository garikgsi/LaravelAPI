<?php

namespace App;

use App\ABPTable;
use Carbon\Carbon;

class SerialNumRegister extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('serial_num_registers');
        $this->table_type('register');

        $this->model([
            ["name" => "number_id", "type" => "select", "table" => "serial_nums", "title" => "Серийный №", "out_index" => 1],
            ["name" => "nomenklatura_id", "type" => "select", "table" => "nomenklatura", "table_class" => "Nomenklatura", "title" => "Номенклатура", "show_in_table" => false],
            ["name" => "sklad_id", "type" => "select", "table" => "sklads", "table_class" => "Sklad", "title" => "Склад", "show_in_table" => true, "show_in_form" => true, "out_index" => 4],
            ["name" => "kolvo", "type" => "kolvo", "title" => "Количество", "show_in_table" => false],
            ["name" => "serial_num_move_id", "type" => "select", "table" => "serial_num_moves", "title" => "ID движения SN"],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['number', 'end_guarantee', 'document']);
    }
    // связи

    // движение серийника
    public function serial_move()
    {
        return $this->belongsTo('App\SerialNumMove', 'serial_num_move_id');
    }
    // серийник
    public function serial()
    {
        return $this->belongsTo('App\SerialNum');
    }
    public function nomenklatura_()
    {
        return $this->belongsTo('App\Nomenklatura', 'nomenklatura_id', 'id');
    }
    public function sklads()
    {
        return $this->belongsTo('App\Sklad', 'sklad_id', 'id');
    }

    // читатели
    // серийник
    public function getNumberAttribute()
    {
        return $this->serial ? $this->serial->number : '';
    }
    // дата окончания гарантии
    public function getEndGuaranteeAttribute()
    {
        return $this->serial ? $this->serial->end_guarantee : '';
    }
    // документ
    public function getDocumentAttribute()
    {
        return $this->serial_move ? $this->serial_move->document : '';
    }

    // вспомогательные методы
    // проверяем можно ли удалить этот регистр
    public function can_delete()
    {
        if ($this->in_out == 1) {
            $register = new SerialNumRegister;
            $created_date = Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at);
            // \DB::connection('db1')->enableQueryLog();
            // // $moves = $register->whereDate('ou_date','>=',$this->ou_date)
            $moves = $register->where('created_at', '>', $created_date->format('Y-m-d H:i:s'))
                ->where('serial_id', $this->serial_id)->where('in_out', 0)->count();
            // var_dump(\DB::connection('db1')->getQueryLog());

            if ($moves > 0) {
                return false;
            }
        }
        return true;
    }
}