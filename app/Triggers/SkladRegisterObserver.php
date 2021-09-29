<?php

namespace App\Triggers;

use App\SkladRegister;

class SkladRegisterObserver
{
    // использовать оперативный учет
    protected $use_ou = true;

    // проверки при сохранении
    public function saving(SkladRegister $sr)
    {
        // если кол-во отрицательное - проверим остаток
    }

    public function deleting(SkladRegister $sr)
    {
        // если кол-во положительное - проверим остаток
    }
}