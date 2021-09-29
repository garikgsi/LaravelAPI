<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SkladReceive;
use App\ABPTable;

class TableController extends Controller
{
    public function tlist($table)
    {
        $schema = new ABPTable();
        $table = $schema->new_table($table);
        $data = $table->where('id','>',1)->whereNull('deleted_at')->take(10)->get();
        $fields = $table->model();
        return view('table_list')->with(["title"=>$table->title(),"table"=>$table->table(),"rows"=>$data, "fields"=>$fields]);
    }

    public function tedit($table,$id) {
        $schema = new ABPTable();
        $tables = $schema->get_tables();
        $table = $schema->new_table($table);
        $table_type = $table->class_name();
        $data = $table->findOrFail($id);
        $fields = $table->model();
        $model = [];
        foreach ($fields as $field) {
            switch ($field["type"]) {
                case "select": {
                    if (isset($field["table"]) && isset($tables[$field["table"]])) {
                        $select_table = $schema->new_table($field["table"]);
                        if ($select_table) {
                            $field["data"] = $select_table->get_select_data();
                        }
                    }

                } break;
            }
            $model[] = $field;
        }

        if (method_exists($data,"files")) {
            $files = $data->files();
        } else {
            $files = [];
        }
        $sub_tables = $table->sub_tables();
        $items_data = [];
        foreach ($sub_tables as $sub_table) {
            if (isset($sub_table["name"]) && isset($sub_table["method"])) {
                $items_table = $schema->new_table($sub_table["name"]);
                $items_fields = $items_table->model();
                $items_table_title = $sub_table["title"];
                $items_table_name = $sub_table["name"];

                $items = $data->{$sub_table["method"]}()->where('id','>',1)->whereNull('deleted_at')->get();
                $items_data[] = ["fields" => $items_fields, "data"=>$items, "title"=>$items_table_title, "name"=>$items_table_name];
            }
        }
// dd($files);


        return view('table_edit')->with(["title"=>$table->title(),"table"=>$table->table(),"table_type"=>$table_type,"data"=>$data, "fields"=>$model, "sub_tables"=>$items_data, "mode"=>"edit","files"=>$files]);
    }

    public function tadd($table_name) {
        $schema = new ABPTable();
        $tables = $schema->get_tables();
        $table = $schema->new_table($table_name);
        $table_type = $table->class_name();
        $data = $table->get_default_values();
        $fields = $table->model();
        $model = [];
        foreach ($fields as $field) {
            switch ($field["type"]) {
                case "select": {
                    if (isset($field["table"]) && isset($tables[$field["table"]])) {
                        $select_table = $schema->new_table($field["table"]);
                        if ($select_table) {
                            $field["data"] = $select_table->get_select_data();
                        }
                    }

                } break;
            }
            $model[] = $field;
        }

        return view('table_edit')->with(["title"=>$table->title(),"table"=>$table->table(),"table_type"=>$table_type,"data"=>$data, "fields"=>$model, "mode"=>"add"]);
    }

    public function tsave($table,$id=0, Request $request) {
        $schema = new ABPTable();
        $tables = $schema->get_tables();
        $table_schema = $schema->new_table($table);
        if ($id>0) {
            $row = $table_schema->findOrFail($id);
        } else {
            $row = $table_schema;
        }
        $fields = $table_schema->model();
        $reached_data = $request->all();
        $new_data = [];
        foreach ($fields as $field) {
            if (isset($reached_data[$field["name"]])) {
                switch ($field["type"]) {
                    case "boolean": {
                        if ($reached_data[$field["name"]]=="on") $new_data[$field["name"]] = true; else $new_data[$field["name"]] = false;
                    } break;
                    default: {
                        $new_data[$field["name"]] = $reached_data[$field["name"]];
                    }
                }

            }
        }
        $row->fill($new_data);
        $row->save();
        if ($row->is_sub_table()) {
            $parent_table = $row->get_key_table();
            if ($parent_table) {
                return redirect('/db/'.$parent_table["table"]."/".$row->{$parent_table["name"]});
            }
        } else {
            return redirect('/db/'.$table.'/')->withInput();
        }
    }

    public function tdel($table,$id, Request $request) {
        $schema = new ABPTable();
        $tables = $schema->get_tables();
        $table_schema = $schema->new_table($table);
        $data = $table_schema->findOrFail($id);
        $res = $table_schema->destroy($id);
        // удалить все подчиненные таблицы
        if ($res) {
            $sub_tables = $table_schema->sub_tables();
            foreach ($sub_tables as $sub_table) {
                if (isset($sub_table["name"]) && isset($sub_table["method"])) {
                    $items_data = $data->{$sub_table["method"]}()->cursor();
                    foreach($items_data as $row){
                        $row->destroy($row->id);
                    };
                }
            }
        }
        // редирект
        if ($table_schema->is_sub_table()) {
            $parent_table = $row->get_key_table();
            if ($parent_table) {
                return redirect('/db/'.$parent_table["table"]."/".$row->{$parent_table["name"]});
            }
        } else {
            return redirect('/db/'.$table.'/')->withInput();
        }
    }
}
