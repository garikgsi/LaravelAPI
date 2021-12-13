<?php

namespace App\Common;

use Exception;
// формат ответа сервера API

class ABPResponse
{

    private $error = [];
    private $is_err = false;
    private $resp_code = 200;
    private $data = [];
    private $model = [];
    private $extensions = [];
    private $count = NULL;
    private $start_time;


    public function __construct()
    {
        $this->start_time = microtime(1);
    }

    public function response($send_model = false)
    {
        $res = [
            "is_error" => $this->is_err,
            "error" => $this->error,
            "data" => $this->data,
            "time_request" => (round(microtime(1) - $this->start_time, 3)) . " sec"
        ];

        if ($send_model) {
            // $res["model"] = $this->model;
            // $res["extensions"] = $this->extensions;
            $res["model"]["fields"] = $this->model;
            $res["model"]["extensions"] = $this->extensions;
        }
        if (!is_null($this->count)) $res["count"] = $this->count;

        return response(json_encode($res, JSON_UNESCAPED_UNICODE), $this->resp_code);
    }

    public function set_err($error, $code = 200)
    {
        $this->is_err = true;
        if (is_array($error)) {
            $this->error = $error;
        } else {
            $this->error[] = $error;
        }
        $this->resp_code = $code;
        return $this;
    }

    public function set_data($data, $count = NULL, $code = NULL)
    {
        $this->data = $data;
        if (!is_null($count)) $this->count = $count;
        if (!is_null($code)) $this->resp_code = $code;
        return $this;
    }

    public function set_model($model)
    {
        $this->model = $this->format_model($model);
        return $this;
    }

    public function set_extensions($extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }

    public function set_code($code)
    {
        $this->resp_code = $code;
    }

    public function exception(Exception $e)
    {
        $this->set_err($e->getMessage());
        if (method_exists($e, 'getStatusCode')) {
            $this->set_code($e->getStatusCode());
        } else {
            $this->set_code(421);
        }
        return $this;
    }

    // подготовка модели к выводу
    public function format_model($model)
    {
        // $res = [];
        // foreach ($model as $field) {
        //     $res[$field["name"]] = $field;
        // }
        // return $res;
        return $model;
    }
}