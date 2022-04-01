<?php

namespace App\Common;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Client\RequestException;

class API1C
{
    private $URL;
    private $Login;
    private $Password;
    private $RetryTimes;
    private $RetryTimeout;
    private $resCode;
    private $resData;
    private $isError = false;
    private $errText = "";
    private $count = 0;


    public function __construct($URL = "http://10.0.1.22/1c", $Login = "odata", $Password = "", $RetryTimes = 3, $RetryTimeout = 3000)
    {
        $this->URL = config('abp.1c_odata_url', $URL);
        $this->Login = config('abp.1c_odata_login', $Login);
        $this->Password = config('abp.1c_odata_password', $Password);
        $this->RetryTimes = $RetryTimes;
        $this->RetryTimeout = $RetryTimeout;
    }

    private function response()
    {
        return [
            "is_error" => $this->isError,
            "err_text" => $this->errText,
            "code" => $this->resCode,
            "data" => $this->resData,
        ];
    }

    private function setError($err)
    {
        $this->isError = true;
        $this->errText = $err;
        return $this;
    }

    public function getError()
    {
        return $this->errText;
    }

    private function setCode($code)
    {
        $this->resCode = $code;
        return $this;
    }

    public function getCode()
    {
        return $this->resCode;
    }

    private function setData($data)
    {
        $this->resData = $data;
        return $this;
    }

    public function getData()
    {
        return $this->resData;
    }

    public function count()
    {
        return $this->count;
    }

    // отправка запроса и обработка исключений
    public function send($URL, $method, $data = null)
    {
        // echo json_encode($data);
        // dd($data);
        // // если данные в массиве - преобразуем в JSON
        // if (is_array($data)) {
        //     $data = json_encode($data);
        // }
        // исполняем запрос
        try {
            $response = Http::withBasicAuth($this->Login, $this->Password)
                // ->withOptions([
                //     'debug' => true
                // ])
                ->retry($this->RetryTimes, $this->RetryTimeout)
                ->$method($URL, $data);
        } catch (RequestException $exception) { 
            $response = $exception->response;
            $retData = json_decode((string) $response);
            if ($response->serverError() || $response->clientError()) {
                $retData = json_decode((string) $response->body());
                $err_code_1c = $retData->{"odata.error"}->code;
                if ($err_code_1c > -1) $err_code_1c = "#" . $err_code_1c;
                else $err_code_1c = "";
                $err_text_1c = $retData->{"odata.error"}->message->value;
                $err = "Сервер 1С ответил ошибкой:" . $err_code_1c . " [" . $err_text_1c . "]";
                $code = $response->getStatusCode();
                $this->setCode($code);
                switch ($code) {
                    case "401": {
                            $err = "Неправильные логин и(или) пароль. " . $err;
                        }
                        break;
                    case "400": {
                            return $err = "Неправильный запрос. " . $err;
                        }
                        break;
                }
                return $this->setError($err)->response();
            } else {
                return $this->setError("unknown error")->response();
            }
        }
        if ($response->successful()) {
            $retData = json_decode((string) $response->getBody());
            $this->setCode($response->getStatusCode());
            return $this->setData($retData)->response();
        } else {
            return $this->setError("unsuccessful")->response();
        }
    }

    // есть ли запись в 1с с таким uuid
    public function is_exists($table, $uuid)
    {
        $res = null;
        if ($uuid != "00000000-0000-0000-0000-000000000000") {
            $myParams = "?$" . "format=application/json;odata=nometadata";
            $URL = $this->URL . "/odata/standard.odata/" . urlencode($table) . "(guid'" . $uuid . "')" . $myParams;
            try {
                $response = Http::withBasicAuth($this->Login, $this->Password)->retry($this->RetryTimes, $this->RetryTimeout)->get($URL);
                if ($response->successful()) {
                    // $retData = json_decode((string) $response);
                    $res = json_decode((string) $response->body());
                }
            } catch (\Throwable $th) {
                return null;
            }
        }
        return $res;
    }

    public function get($table, $params = "")
    {
        // $myParams = "$"."format=application/json;odata=nometadata&$"."inlinecount=allpages";
        $myParams = "$" . "format=application/json;odata=nometadata";
        if ($params != "") $p = $params . "&" . $myParams;
        else $p = $myParams;
        $URL = $this->URL . "/odata/standard.odata/" . urlencode($table) . "?" . $p;
        return $this->send($URL, "get");
    }

    public function post($table, $data)
    {
        $URL = $this->URL . "/odata/standard.odata/" . urlencode($table) . "?$" . "format=json";
        return $this->send($URL, "post", $data);
    }

    public function update($table, $uuid, $data, $method = 'patch')
    {
        $URL = $this->URL . "/odata/standard.odata/" . urlencode($table) . "(guid'" . $uuid . "')" . "?$" . "format=json";
        return $this->send($URL, $method, $data);
    }
}
