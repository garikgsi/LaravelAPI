<?php

namespace App;

use App\ABPTable;


class NDS extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('nds');
        $this->has_files(true);
        // $this->fillable(['stavka']);
        $this->model([
            ["name" => "stavka", "type" => "float", "title" => "Ставка налога", "require" => true, "index" => "index"]
        ]);
        // добавляем читателей
        // $this->appends = array_merge($this->appends,['stavka']);
    }

    public function nomenklatura()
    {
        return $this->hasMany('App\Nomenklatura');
    }

    // читатели
    // выдаем ставку
    // public function getStavkaAttribute()
    // {
    //     return $this->attributes['stavka'];
    // }
    // выдаем строку для селекта
    public function getSelectListTitleAttribute()
    {
        $id = $this->attributes["id"];
        $model = $this->find($id);
        return $model->comment;
    }
}
