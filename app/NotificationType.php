<?php

namespace App;

use App\ABPTable;

class NotificationType extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('notification_types');

        $this->model([
            ["name"=>"color","type"=>"string","title"=>"Цвет","require"=>true, "default"=>"success","index"=>"index","show_in_table"=>false,"show_in_form"=>true],
            ["name"=>"icon","type"=>"string","title"=>"Иконка","require"=>false,"index"=>"index","show_in_table"=>false,"show_in_form"=>true],
        ]);

    }
}
