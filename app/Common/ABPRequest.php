<?php

namespace App\Common;

use Illuminate\Http\Request;


class ABPRequest extends Request {

    public function __construct()
    {
        parent::__construct();
    }

    public function filter_data() {

    }

}
