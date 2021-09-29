<?php

namespace App\Triggers;

use App\SkladMove;
use App\SkladMoveItem;
use App\SkladRegister;
use App\Nomenklatura;
use Illuminate\Support\Facades\Auth;


class SkladMoveItemObserver
{

    protected $sm = NULL;
    protected $smi;
    protected $sklad_out;
    protected $sklad_out_title;
    protected $sklad_in;
    protected $sklad_in_title;
    protected $sotrudnik;
    // пользователь == кладовщик
    protected $is_out_keeper;
    protected $is_in_keeper;
    // пользователь = администратор
    protected $is_admin;
    // старые значения
    protected $old;
    // новые значения
    protected $new;

    // проверки при создании
    public function creating(SkladMoveItem $smi)
    {
        // инициализация служебныех переменных
        $this->set_vars($smi);

        if ($this->sm) {
            if ($this->sm->is_active == 1) {
                if (!$this->is_admin) {
                    abort(422, 'В проведенный документ вносить изменения может только администратор');
                    return false;
                }
            } else {
                // если уже отправлено
                if ($this->sm->is_out == 1) {
                    if ($this->is_out_keeper) {
                        // проверяем остатки на складе отправления
                        $check = $smi->mod_register(0);
                        if ($check["is_error"]) abort(422, $check["err"]);
                    } else {
                        abort(422, 'Отправлять со склада может только кладовщик или администратор');
                        return false;
                    }
                }
                // если уже оприходовано
                if ($this->sm->is_in == 1) {
                    if (!$this->is_in_keeper) {
                        abort(422, 'Приходовать на склад может только кладовщик или администратор');
                        return false;
                    }
                }
            }
        }
    }
    // создание
    public function created(SkladMoveItem $smi)
    {
        // инициализация служебных переменных
        $this->set_vars($smi);

        if ($this->sm) {
            // обновляем регистр
            for ($i = 0; $i <= 1; $i++) {
                $res = $smi->mod_register($i, 'update_only');
                if ($res["is_error"]) {
                    abort(422, $res["err"]);
                    return false;
                }
            }
        }
    }

    // проверки при обновлении
    public function updating(SkladMoveItem $smi)
    {
        // var_dump($smi->toArray());
        // инициализация служебных переменных
        $this->set_vars($smi);

        if ($this->sm) {
            // проверим остатки
            for ($i = 0; $i <= 1; $i++) {
                // var_dump($i);
                $res = $smi->mod_register($i);
                // print_r($res);
                if ($res["is_error"]) abort(422, $res["err"]);
            }
        }
    }

    // обновление
    public function updated(SkladMoveItem $smi)
    {
        // инициализация служебныех переменных
        $this->set_vars($smi);
        // если связи проинициализированы (мб такое, что модель еще не сопоставлена родителю)
        if ($this->sm) {
            // обновляем регистр
            for ($i = 0; $i <= 1; $i++) {
                // var_dump("updated " . $i);
                $res = $smi->mod_register($i, 'update_only');
                // var_dump($i, $res);
                if ($res["is_error"]) abort(422, $res["err"]);
            }
        }
    }


    public function saved(SkladMoveItem $smi)
    {
        // инициализация служебныех переменных
        $this->set_vars($smi);

        // если связи проинициализированы (мб такое, что модель еще не сопоставлена родителю)
        if ($this->sm) {
            // обновим серийные номера
            $sn_res = $smi->update_sn_register();
            if (!$sn_res) abort(422, 'Не удалось обновить базу данных серийных номеров');
        }
    }

    // проверки перед удалением
    public function deleting(SkladMoveItem $smi)
    {
        // инициализация служебныех переменных
        $this->set_vars($smi);

        // если связи проинициализированы (мб такое, что модель еще не сопоставлена родителю)
        if ($this->sm) {
            // проверяем остатки на складе получения, если приходован
            for ($i = 0; $i <= 1; $i++) {
                $res = $smi->mod_register($i, 'check_for_delete');
                if ($res["is_error"]) abort(422, $res["err"]);
            }
        }
    }

    public function deleted(SkladMoveItem $smi)
    {
        // инициализация служебныех переменных
        $this->set_vars($smi);

        // если связи проинициализированы (мб такое, что модель еще не сопоставлена родителю)
        if ($this->sm) {
            // удаляем регистр
            for ($i = 0; $i <= 1; $i++) {
                $res = $smi->mod_register($i, 'delete_only');
                if ($res["is_error"]) abort(422, $res["err"]);
            }
            // удалим серийные номера
            $sn_res = $smi->delete_sn_register();
            if (!$sn_res) abort(422, 'Не удалось очистить базу данных серийных номеров для записи');
        }
    }

    public function restored(SkladMoveItem $smi)
    {
        // восстановление не реализовано!
    }

    // служебные методы
    // заносим основные переменные класса
    protected function set_vars(SkladMoveItem $smi)
    {
        $this->smi = $smi;
        $this->sm = $smi->sklad_move;
        $sm = $this->sm;
        if ($sm) {
            $this->sklad_out = $sm->sklad_out_()->first();
            $this->sklad_out_title = $sm->sklad_out;
            $this->sklad_in = $sm->sklad_in_()->first();
            $this->sklad_in_title = $sm->sklad_in;
            // пользователь
            $user = Auth::user();
            $user_info = $user->info;
            // сотрудник
            $this->sotrudnik = $user_info->sotrudnik();
            // пользователь == кладовщик
            $this->is_out_keeper = $this->sotrudnik->is_keeper($sm->getOriginal('sklad_out_id'));
            $this->is_in_keeper = $this->sotrudnik->is_keeper($sm->getOriginal('sklad_in_id'));
            // пользователь = администратор
            $this->is_admin = $user_info->is_admin();
            // старые значения
            $this->old = $sm->getOriginal();
            // новые значения
            $this->new = $sm;
        }
    }



    // проверяем изменилось значение поля
    protected function if_change($field)
    {
        if ($this->smi) {
            if (isset($this->old[$field]) && isset($this->new[$field]) && $this->old[$field] != $this->new[$field]) return true;
        } else {
            abort(422, '#SMIO.Чтобы использовать if_change нужно сначала инициализировать переменные');
            return false;
        }
        return false;
    }
}