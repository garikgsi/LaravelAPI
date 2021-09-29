<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Common\ABPResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

class PermissionsController extends Controller
{

    private $response;

    // конструктор
    public function __construct() {
        $this->response = new ABPResponse();
    }




// РОЛИ

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function roles_index($id = null)
    {
        if (isset($id)) {
            $user = User::find($id);
        } else {
            $user = Auth::user();
        }

        if ($user->can('viewAny', Role::class)) {
            $data = Role::all();
            return $this->response->set_data($data, $data->count(), 200)->response();
        } else {
            return $this->response->set_err('Нет прав на просмотр ролей', 403)->response();
        }
    }

    // выдаем роли пользователя
    public function user_roles($id = null)
    {
        if ($id!=null) {
            $user = User::find($id);
        } else {
            $user = Auth::user();
        }

        $data = $user->roles;
        return $this->response->set_data($data, $data->count(), 200)->response();
        // dd(compact($user->roles()));
        // if ($user->can('view_user_roles', Role::class)) {
        //     $data = $user->roles;
        //     return $this->response->set_data($data, $data->count(), 200)->response();
        // } else {
        //     return $this->response->set_err('Нет прав на просмотр ролей', 403)->response();
        // }
    }

    // устанавливаем роли пользователя
    public function set_user_roles(Request $request, $id = null)
    {
        $user = Auth::user();
        if ($user->can('set_user_roles')) {
            $manage_user = User::find($id);
            if ($manage_user) {
                $new_permissions = json_decode($request->input('data'));
                if (!isset($new_permissions)) $new_permissions = [];
                // заменяем роли пользователя
                $roles = Role::find($new_permissions);
                $manage_user->syncRoles($roles);
                // сам себе запретить убирать роль супер администратора
                if ($user->id==$id) {
                    $super_admin_role = Role::where('name','super admin')->first();
                    $user->assignRole($super_admin_role);
                }
                $data = $manage_user->roles;
            } else {
                $data = [];
                $this->response->set_err('Пользователь не найден');
            }
            return $this->response->set_data($data, $data->count(), 200)->response();
        } else {
            return $this->response->set_err('Нет прав на изменение ролей', 403)->response();
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($user_id = null)
    {
        //
        if (isset($user_id)) {
            $user = User::find($user_id);
        } else {
            $user = Auth::user();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
