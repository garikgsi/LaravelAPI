<?php

namespace App\Http\Controllers;

use App\Common\ABPResponse;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;


class UserController extends Controller
{
    private $response;
    private $user;

    // конструктор
    public function __construct() {
        $this->response = new ABPResponse();
        $this->user = Auth::user();
    }

    public function set_user() {
        $this->user = Auth::user();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->set_user();
        if ($this->user->can('viewAny', User::class)) {
            $data = User::all();
            return $this->response->set_data($data, $data->count(), 200)->response();
        } else {
            return $this->response->set_err('Нет прав на получение списка пользователей', 403)->response();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
        $this->set_user();
        if (isset($id)) {
            $user = User::find($id);
            if ($user) {
                if ($this->user->can('view', $user)) {
                    $data = $user;
                    return $this->response->set_data($data, $data->count(), 200)->response();
                } else {
                    return $this->response->set_err('Нет прав на просмотр пользователя', 403)->response();
                }
            } else {
                return $this->response->set_err('Пользователь не найден', 404)->response();
            }
        } else {
            return $this->response->set_err('Параметр не передан', 400)->response();
        }
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
