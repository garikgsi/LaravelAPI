<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Common\API1C;
use App\DocType;
use App\Nomenklatura;
use App\SkladReceive;
use App\Jobs\LoadDataFromOldDB;
use App\Jobs\Sync1c;
use App\Notifications\Sync1C as Sync1CNotification;
use App\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class StartController extends Controller
{
    public function move_remains()
    {
        dispatch(new LoadDataFromOldDB());
        return back();
    }

    // синхронизация с 1С
    public function sync1C()
    {
        Sync1c::dispatch(Auth::user());
        // dispatch(new Sync1c($user));
        // $sync = new Sync1c();
        // $sync->handle();
        return back();
    }
}
