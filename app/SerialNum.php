<?php

namespace App;

use App\ABPTable;

class SerialNum extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('serial_nums');
        $this->table_type('table');

        $this->model([
            ["name" => "number", "type" => "string", "max" => 64, "title" => "Серийный №", "out_index" => 1],
            ["name" => "end_guarantee", "type" => "date", "title" => "Дата окончания гарантии", "require" => false, "show_in_table" => true, "out_index" => 2],
            ["name" => "seriable", "title" => "Документ", "type" => "morph"],
        ]);

        // добавляем читателей
        // $this->appends = array_merge($this->appends,['nomenklatura', 'sklad']);
    }

    // связи
    // полиморфная связь с документом
    public function seriable()
    {
        return $this->morphTo();
    }

    // движение серийных номеров
    public function serial_move()
    {
        return $this->hasMany('App\SerialNumMove', 'serial_num_id', 'id');
    }
}