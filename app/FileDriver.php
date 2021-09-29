<?php

namespace App;

use App\ABPTable;
use App\Common\ABPCache;


class FileDriver extends ABPTable
{
    public function __construct() {
        parent::__construct();

        $this->table('file_drivers');

    }
    // выдаем строку для селекта
    public function getSelectListTitleAttribute() {
        $id = $this->attributes["id"];
        $m = $this->find($id);
        $title = $m->comment;
        ABPCache::put_select_list($this->table,$this->attributes["id"],$title);
        return $title;
    }


}
