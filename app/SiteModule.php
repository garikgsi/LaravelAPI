<?php

namespace App;

// use Illuminate\Database\Eloquent\Model;
use App\ABPTable;

class SiteModule extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('site_modules');
        $this->has_files(false);
        // $this->fillable(['title','template']);

        $this->model([
            ["name"=>"title","type"=>"string","title"=>"Название","require"=>true,"index"=>"index"],
            ["name"=>"template","type"=>"string","title"=>"Шаблон","require"=>false],
        ]);
    }

}
