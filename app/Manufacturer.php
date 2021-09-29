<?php

namespace App;

use App\ABPTable;


class Manufacturer extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('manufacturers');
        $this->has_files(true);
    }

}
