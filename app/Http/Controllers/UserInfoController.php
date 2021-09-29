<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Common\ABPResponse;
use App\User;
use Illuminate\Support\Facades\Gate;


class UserInfoController extends Controller
{
    private $response;

    public function __construct() {
        $this->response = new ABPResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id=null)
    {
        if (isset($id) && $id!==null) {
            $user = User::find($id);
        } else {
            $user = Auth::user();
        }

        if (Gate::allows('edit-user', $user)) {
            if (isset($user) && $user !==null) {
                $r = $request->all();

            } else {
                return $this->response->set_err('Пользователь не определен', 403)->response();
            }
        } else {
            return $this->response->set_err('Запрещено политикой безопасности', 403)->response();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id=null)
    {
        if (isset($id) && $id!==null) {
            $user = User::find($id);
        } else {
            $user = Auth::user();
        }

        if (Gate::allows('show-user', $user)) {
            if (isset($user) && $user !==null) {
                $data = [
                    "user"          =>  $user->toArray(),
                    "info"          =>  $user->info !==null ? $user->info->toArray(): NULL,
                    "ui"            =>  $user->ui !==null ? $user->ui->toArray(): NULL,
                    "permissions"   => $user->getAllPermissions(),
                    "roles"         => $user->getRoleNames(),
                ];
                return $this->response->set_data($data, 200)->response();
            } else {
                return $this->response->set_err('Пользователь не определен', 403)->response();
            }
        } else {
            return $this->response->set_err('Запрещено политикой безопасности', 403)->response();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id=null)
    {
        if (isset($id) && $id!==null) {
            $user = User::find($id);
        } else {
            $user = Auth::user();
        }

        if (Gate::allows('edit-user', $user)) {
            if (isset($user) && $user !==null) {

            } else {
                return $this->response->set_err('Пользователь не определен', 403)->response();
            }
        } else {
            return $this->response->set_err('Запрещено политикой безопасности', 403)->response();
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
