<?php

namespace App\Triggers;

use App\SkladReceiveItem;


class SkladReceiveItemObserver
{

    // проверки при обновлении
    public function saving(SkladReceiveItem $sri)
    {
        // наименование номенклатуры
        $sri->nomenklatura_name = $sri->nomenklatura_()->first()->doc_title;
        // ставка НДС
        $sri->stavka_nds = $sri->nds_->stavka;
        // проверим регистры накопления
        $res = $sri->mod_register(1);
        if ($res["is_error"]) abort(421, $res["err"]);

        // кол-во серийников
        $sn_kolvo = $sri->get_sn_count();
        if ($sn_kolvo > $sri->kolvo) {
            abort(421, "#SRIO.Для " . $sri->nomenklatura . " указано слишком много серийных номеров (" . $sn_kolvo . " из максимально возможных " . $sri->kolvo . ")");
            return false;
        }
    }

    public function saved(SkladReceiveItem $sri)
    {
        // обновим регистры накопления
        $res = $sri->mod_register(1, 'update_only');
        if ($res["is_error"]) abort(421, $res["err"]);

        // обновим серийные номера
        $res_sn = $sri->mod_sn_register('update_only');
        // dd($res_sn);
        if ($res_sn["is_error"]) {
            abort(421, "#SRIO." . $res_sn["err"]);
            return false;
        }
        // // обновим серийные номера
        // $sn_res = $sri->update_sn_register();
        // if (!$sn_res) abort(421, '#SRIO. Не удалось обновить базу данных серийных номеров');

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