<?php

namespace App;

use App\ABPTable;


class FileList extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('file_lists');

        $this->model([
            ["name" => "file_id", "type" => "select", "table" => "files", "table_class" => "File", "title" => "Файл", "require" => true, "default" => 1, "index" => "index"],
            ["name" => "table_id", "type" => "select", "table" => "polymorph", "title" => "ID владельца", "require" => true, "default" => 1, "index" => "index"],
            ["name" => "table_type", "type" => "polymorph_table", "title" => "таблица владельца", "require" => true, "index" => "index"],
        ]);
    }

    public function file()
    {
        return $this->belongsTo('App\File');
    }
}