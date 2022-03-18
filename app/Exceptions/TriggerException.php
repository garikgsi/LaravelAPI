<?php

namespace App\Exceptions;

use Exception;

class TriggerException extends Exception
{
    public function render($request, Exception $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'is_error' => true,
                'error' => $exception->getMessage()
            ], 421);
        }
        return false;
        // нет вывода, т.к. ответ не поразумевает вывод в браузере. только api = json
    }
}