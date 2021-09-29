<?php

namespace App;

use App\ABPTable;

class FirmPosition extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('firm_positions');
        $this->has_files(true);

    }
}
