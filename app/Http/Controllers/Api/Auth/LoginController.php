<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Common\ABPResponse;


class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $response = new ABPResponse;
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return $response->set_err('Неверные данные для входа', 401)->response();
        }

        $user = Auth::user();
        if (!$user->email_verified_at) {
            return $response->set_err('Учетная запись не верифицирована по email', 401)->response();
        } else {
            if (!is_null($user->api_token_lifetime)) {
                $now = Carbon::now();
                $current_token_lifetime = Carbon::parse($user->api_token_lifetime);
                if ($current_token_lifetime < $now) {
                    $user->token_create();
                }
            } else {
                $user->token_create();
            }

            return $response->set_data([
                'token_type' => 'Bearer',
                'token' => $user->api_token,
                'expires_at' => Carbon::parse($user->api_token_lifetime)->toDateTimeString(),
                'user' => $user,
                'roles' => $user->roles
            ])->response();
        }
    }
}