<?php

namespace App\Common;

class ABPField
{
    // нормализация значения для БД в соответствии с типом
    public static function norm_for_db($type, $val)
    {
    }

    // проверка заполненности поля
    public static function checkExist($type, $val)
    {
    }

    // проверка соответствия значения типу
    public static function checkType($type, $val)
    {
        switch ($type) {
            case 'string': {
                    return is_null($val) || $val == 'null' ? '' : $val;
                }
                break;
            case 'boolean': {
                    $filter_val = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if ($filter_val === NULL) {
                        return false;
                    } else {
                        if ($filter_val === true) {
                            return '1';
                        } else {
                            return '0';
                        }
                    }
                    // return $val;
                }
                break;
            case 'text': {
                    return $val;
                }
                break;
            case 'select': {
                    $pattern = "/^[0-9]+$/i";
                    if (preg_match($pattern, $val) === 1) {
                        return $val;
                    } else {
                        return false;
                    }
                }
                break;
            case 'integer':
            case 'int': {
                    return filter_var($val, FILTER_VALIDATE_INT);
                }
                break;
            case 'money': {
                    return filter_var($val, FILTER_VALIDATE_FLOAT);
                }
                break;
            case 'kolvo': {
                    return filter_var($val, FILTER_VALIDATE_FLOAT);
                }
                break;
            case 'phone': {
                    $pattern = "/^[0-9]{10}$/i";
                    if (preg_match($pattern, $val) === 1) {
                        return $val;
                    } else {
                        return false;
                    }
                }
                break;
            case 'password': {
                    $pattern = "/^.{8,}$/";
                    if (preg_match($pattern, $val) === 1) {
                        return $val;
                    } else {
                        return false;
                    }
                }
                break;
            case 'email': {
                    return filter_var($val, FILTER_VALIDATE_EMAIL);
                }
                break;
            case 'date': {
                    $pattern = "/^\d{4}-\d{2}-\d{2}$/";
                    if (preg_match($pattern, $val) === 1) {
                        return $val;
                    } else {
                        return false;
                    }
                }
                break;
            case 'datetime': {
                    $pattern = "/^\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}:\d{2}$/";
                    if (preg_match($pattern, $val) === 1) {
                        return $val;
                    } else {
                        return false;
                    }
                }
                break;
            case 'ip': {
                    return filter_var($val, FILTER_VALIDATE_IP);
                }
                break;
            case 'textarea': {
                    return $val;
                }
                break;
            case 'month': {
                    $pattern = "/^\d{4}-\d{2}$/";
                    if (preg_match($pattern, $val) === 1) {
                        return $val;
                    } else {
                        return false;
                    }
                }
                break;
            case 'period': {
                    if (is_array($val) && count($val) == 2) {
                        if (self::checkType($val[0], "date") && self::checkType($val[1], "date")) {
                            return $val;
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
                break;
                // case 'pricequantityamount': {
                // } break;
            case 'radio': {
                    return filter_var($val, FILTER_VALIDATE_INT);
                }
                break;
            default: {
                    return $val;
                }
        }
    }
}