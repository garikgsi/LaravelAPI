<?php

namespace App;

use App\ABPTable;


class FileType extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('file_types');

    }

}
