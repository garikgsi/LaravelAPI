<?php

namespace App;

use App\ABPTable;


class Tag extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('tags');
    }

    public function table_tags() {
        return $this->hasMany('App\Tag');
    }

}
