<?php

namespace App\Triggers;

use App\ActItem;
use App\NDS;
use Illuminate\Support\Facades\Auth;


class ActItemObserver
{

    // акт
    private $a = NULL;
    // номенклатура
    private $nomenklatura;
    // тайтл номенклатуры
    private $nomenklatura_title;
    // тайтл единицы измерения
    private $ed_ism_title;
    // склад
    private $sklad;
    // тайтл склада
    private $sklad_title;
    // сотрудник
    private $sotrudnik;
    // сотрудник == кладовщик
    private $is_keeper = false;
    // сотрудник == администратор
    private $is_admin = false;
    // значения до изменения
    private $old = [];
    // измененные значения
    private $new = [];



    public function saving(ActItem $ai)
    {
        // обновим сумму НДС
        if ($ai->nds_id > 1) {
            $nds = NDS::find($ai->nds_id);
            if ($nds) {
                $ai->summa_nds = $nds->stavka * $ai->summa;
            }
        }
        // обновим наименование
        $nomenklatura = $ai->nomenklatura_()->first();
        if ($nomenklatura) {
            $ai->nomenklatura_name = $nomenklatura->doc_title;
        }
        // инициализация переменных класса
        $this->set_vars($ai);

        if ($this->a) {
            // проводить могут только кладовщик или администратор
            if (($this->a->is_active == 1) && !$this->is_keeper && !$this->is_admin) {
                abort(421, '#AIO.Изменять проведенную позицию можно только кладовщику или администратору');
                return false;
            }
            // проверим регистры накопления
            $res = $ai->mod_register(0);
            if ($res["is_error"]) {
                abort(421, "#AIO." . $res["err"]);
                return false;
            }

            // кол-во серийников
            $sn_kolvo = $ai->get_sn_count();
            if ($sn_kolvo > $ai->kolvo) {
                abort(421, "#AIO.Для " . $ai->nomenklatura . " указано слишком много серийных номеров (" . $sn_kolvo . " из максимально возможных " . $ai->kolvo . ")");
                return false;
            }
            // проверим серийные номера
            $res_sn = $ai->mod_sn_register();
            // dd($res_sn);
            if ($res_sn["is_error"]) {
                abort(421, "#AIO." . $res_sn["err"]);
                return false;
            }
        }
    }

    public function saved(ActItem $ai)
    {
        // инициализация переменных класса
        $this->set_vars($ai);

        if ($this->a) {
            // обновим регистры накопления
            $res = $ai->mod_register(0, 'update_only');
            if ($res["is_error"]) {
                abort(421, "#AIO." . $res["err"]);
                return false;
            }

            // обновим серийные номера
            $res_sn = $ai->mod_sn_register('update_only');
            // dd($res_sn);
            if ($res_sn["is_error"]) {
                abort(421, "#AIO." . $res_sn["err"]);
                return false;
            }
            // // обновим серийные номера
            // $sn_res = $ai->update_sn_register();
            // if (!$sn_res) {
            //     abort(421, '#AIO.Не удалось обновить базу данных серийных номеров');
            //     return false;
            // }

            // изменим сумму накладной
            $this->up_sum_act($ai);
        }
    }

    // проверки перед удалением
    public function deleting(ActItem $ai)
    {
        // инициализация переменных класса
        $this->set_vars($ai);
        if ($this->a) {
            // удалять могут только кладовщик или администратор
            if (($this->a->is_active == 1) && !$this->is_keeper && !$this->is_admin) {
                abort(421, '#AIO.Удалять из проведенной накладной позиции можно только кладовщику или администратору');
                return false;
            }

            // проверим регистры накопления
            $res = $ai->mod_register(0, 'check_for_delete');
            if ($res["is_error"]) {
                abort(421, "#AIO." . $res["err"]);
                return false;
            }

            // проверим серийные номера
            $res_sn = $ai->mod_sn_register('check_for_delete');
            // dd($res_sn);
            if ($res_sn["is_error"]) {
                abort(421, "#AIO." . $res_sn["err"]);
                return false;
            }
        }
    }


    public function deleted(ActItem $ai)
    {
        // инициализация переменных класса
        $this->set_vars($ai);
        if ($this->a) {
            // удалим регистр
            $res = $ai->mod_register(0, 'delete_only');
            if ($res["is_error"]) {
                abort(421, "#AIO." . $res["err"]);
                return false;
            }
            // // удалим серийные номера
            // $sn_res = $ai->delete_sn_register();
            // if (!$sn_res) {
            //     abort(421, '#AIO.Не удалось очистить базу данных серийных номеров для записи');
            //     return false;
            // }
            // удалим серийные номера
            $res_sn = $ai->mod_sn_register('delete_only');
            // dd($res_sn);
            if ($res_sn["is_error"]) {
                abort(421, "#AIO." . $res_sn["err"]);
                return false;
            }


            // изменим сумму накладной
            $this->up_sum_act($ai);
        }
    }

    // изменим сумму накладной
    private function up_sum_act(ActItem $ai)
    {
        try {
            $a = $ai->act;
            $items = $a->items()->get();
            if ($items) {
                $a->summa = $items->sum('summa');
                $a->summa_nds = $items->sum('summa_nds');
                $a->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    // заносим основные переменные класса
    protected function set_vars(ActItem $ai)
    {
        $this->a = $ai->act;
        $this->nomenklatura_title = $ai->nomenklatura;
        $this->ed_ism_title = $ai->nomenklatura_()->first()->ed_ism;
        // пользователь
        $user = Auth::user();
        $user_info = $user->info;
        // сотрудник
        $this->sotrudnik = $user_info->sotrudnik();
        // пользователь = администратор
        $this->is_admin = $user_info->is_admin();
        // старые значения
        $this->old = $ai->getOriginal();
        // новые значения
        $this->new = $ai;
        if ($this->a) {
            $this->sklad = $this->a->sklad_;
            $this->sklad_title = $this->a->sklad;
            // пользователь == кладовщик
            $this->is_keeper = $this->sotrudnik ? $this->sotrudnik->is_keeper($this->sklad->id) : false;
        }
    }
}