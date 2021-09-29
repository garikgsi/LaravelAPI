<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\ABPResponse;


class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        $result_revoke = $request->user()->token_revoke();
        $resp = new ABPResponse;
        if ($result_revoke) {
            return $resp->set_data(['message' => 'Вы вышли'])->response();
        } else {
            return $resp->set_err(['message' => 'Выход не осуществлен'], 422)->response();
        }
    }
}