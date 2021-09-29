<?php

namespace App;

use App\ABPTable;

class SerialNumMove extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('serial_num_moves');
        $this->table_type('table');

        $this->model([
            ["name" => "serial_num_id", "type" => "select", "table" => "serial_nums", "title" => "Серийный №", "out_index" => 1],
            ["name" => "sn_movable", "title" => "Документ", "type" => "morph"],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['number', 'end_guarantee', 'document', 'nomenklatura']);
    }

    // связи
    // полиморфная связь с документом
    public function sn_movable()
    {
        return $this->morphTo();
    }

    // серийный №
    public function serial()
    {
        return $this->belongsTo('App\SerialNum', 'serial_num_id', 'id');
    }

    // регистр
    public function sn_register()
    {
        return $this->hasMany('App\SerialNumRegister');
    }

    // читатели
    // номенклатура
    public function getNomenklaturaAttribute()
    {
        $doc = $this->sn_movable;
        if ($doc) {
            $doc_attrs = $doc->toArray();
            if (isset($doc_attrs['nomenklatura'])) return $doc_attrs['nomenklatura'];
        }
        return 'номенклатура';
    }

    // серийный номер
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
        $ReceiveClass = 'App\SkladReceiveItem';
        $ProductionItemClass = 'App\ProductionItem';
        $ProductionComponentClass = 'App\ProductionComponent';
        $MoveClass = 'App\SkladMoveItem';
        // поступление
        if ($this->sn_movable instanceof $ReceiveClass) {
            return $this->sn_movable->sklad_receive->select_list_title;
        }
        // производство - приход
        if ($this->sn_movable instanceof $ProductionItemClass) {
            return $this->sn_movable->production->select_list_title;
        }
        // производство - расход
        if ($this->sn_movable instanceof $ProductionComponentClass) {
            return $this->sn_movable->production->select_list_title;
        }
        // перемещение
        if ($this->sn_movable instanceof $MoveClass) {
            return $this->sn_movable->sklad_move->select_list_title;
        }
        return '';
    }
}