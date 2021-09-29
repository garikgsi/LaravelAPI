<?php

namespace App\Common;

// use Illuminate\Database\Eloquent\Model;
use App\ABPTable;

class ABPModel {

    public function __construct($table)
    {
        if ($table) {

            if (isset($this->tables[$table])) {
                $this->table = $table;
                $table_description = $this->tables[$table];
                $this->title($table_description["Title"]);
                $this->class_name($table_description["Class"]);
                if (isset($table_description["Name1C"])) $this->name_1c($table_description["Name1C"]);
                // new instance
                $class_name = $this->class_name();
                $table = new $class_name;
                $this->model = $table->model();
                if (isset($table->appends)) $this->appends = $table->appends;
                $this->t = $table;
                return $table;
            } else {
                return null;
            }
        }

    }


}
