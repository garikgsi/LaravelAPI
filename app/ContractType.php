<?php

namespace App;

use App\ABPTable;

class ContractType extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('contract_types');
        $this->has_files(true);
        $this->has_images(false);
        $this->has_groups(false);

        $this->model([
            ["name" => "periodic", "type" => "string", "title" => "Периодичность", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => false, "default" => 1],
        ]);
    }
}