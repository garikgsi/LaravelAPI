<?php

namespace App\Triggers;

use App\SkladReceiveItem;


class SkladReceiveItemObserver
{

    // проверки при обновлении
    public function saving(SkladReceiveItem $sri)
    {
        // проверим регистры накопления
        $res = $sri->mod_register(1);
        if ($res["is_error"]) abort(421, $res["err"]);
    }

    public function saved(SkladReceiveItem $sri)
    {
        // обновим регистры накопления
        $res = $sri->mod_register(1, 'update_only');
        if ($res["is_error"]) abort(421, $res["err"]);

        // обновим серийные номера
        $sn_res = $sri->update_sn_register();
        if (!$sn_res) abort(421, '#SRIO. Не удалось обновить базу данных серийных номеров');
    }

    // проверки перед удалением
    public function deleting(SkladReceiveItem $sri)
    {
        // проверим регистры накопления
        $res = $sri->mod_register(1, 'check_for_delete');
        if ($res["is_error"]) abort(421, $res["err"]);
    }

    public function deleted(SkladReceiveItem $sri)
    {
        // удалим регистр
        $res = $sri->mod_register(1, 'delete_only');
        if ($res["is_error"]) abort(421, $res["err"]);
        // удалим серийные номера
        $sn_res = $sri->delete_sn_register();
        if (!$sn_res) abort(421, '#SRIO. Не удалось очистить базу данных серийных номеров для записи');
    }
}
