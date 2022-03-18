<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sklad;
use App\SkladRegister;

class TestCcontroller extends Controller
{
    public function test()
    {
        $reg = new SkladRegister;
        dd($reg->ostatok(2970, 21213, 'now', true));
    }
}