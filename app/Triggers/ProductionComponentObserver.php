<?php

namespace App\Triggers;

use App\Production;
use App\ProductionItem;
use App\ProductionComponent;
use App\SkladRegister;
use App\Nomenklatura;
use Illuminate\Support\Facades\Auth;


class ProductionComponentObserver
{

    // производство
    private $p = NULL;
    // готовые изделия
    private $pi;
    // компонент
    private  $pc = NULL;
    // номенклатура изделия
    private $nomenklatura;
    // тайтл номенклатуры изделия
    private $nomenklatura_title;
    // тайтл единицы измерения изделия
    private $ed_ism_title;
    // тайтл склада
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


    public function saving(ProductionComponent $pc)
    {
        // инициализация переменных класса
        $this->set_vars($pc);

        if ($this->p) {
            // изменять проведенное могут только кладовщик или администратор
            if (($this->p->is_active == 1 || $this->pi->is_producted == 1) && !$this->is_keeper && !$this->is_admin) {
                abort(422, '#PCO.Изменять проведенную позицию можно только кладовщику или администратору');
                return false;
            }

            // если изделие приходуем на склад - проверим, чтобы документ был проведен
            if ($this->if_set('is_producted', 1)) {
                if ($this->p->is_active != 1) {
                    abort(422, '#PCO.Невозможно оприходовать на склад готовое изделие при непроведенной накладной');
                    return false;
                }
            }

            // проверим регистры накопления
            $res = $pc->mod_register(0);
            if ($res["is_error"]) {
                abort(422, "#PCO." . $res["err"]);
                return false;
            }
        }
    }

    public function saved(ProductionComponent $pc)
    {
        // инициализация переменных класса
        $this->set_vars($pc);

        if ($this->p) {
            // обновим регистры накопления
            $res = $pc->mod_register(0, 'update_only');
            if ($res["is_error"]) {
                abort(422, "#PCO." . $res["err"]);
                return false;
            }

            // обновим серийные номера
            $sn_res = $pc->update_sn_register();
            if (!$sn_res) {
                abort(422, '#PCO.Не удалось обновить базу данных серийных номеров');
                return false;
            }
        }
    }

    // проверки перед удалением
    public function deleting(ProductionComponent $pc)
    {
        // инициализация переменных класса
        $this->set_vars($pc);
        if ($this->p) {
            // удалять могут только кладовщик или администратор
            if (($this->p->is_active == 1 || $this->pi->is_producted == 1) && !$this->is_keeper && !$this->is_admin) {
                abort(422, '#PCO.Удалять из проведенной позиции можно только кладовщику или администратору');
                return false;
            }

            // проверим регистры накопления
            $res = $pc->mod_register(0, 'check_for_delete');
            if ($res["is_error"]) {
                abort(422, "#PCO." . $res["err"]);
                return false;
            }
        }
    }

    // удаление
    public function deleted(ProductionComponent $pc)
    {
        // инициализация переменных класса
        $this->set_vars($pc);

        if ($this->p) {
            // удалим регистр
            $res = $pc->mod_register(0, 'delete_only');
            if ($res["is_error"]) {
                abort(422, "#PCO." . $res["err"]);
                return false;
            }
            // удалим серийные номера
            $sn_res = $pc->delete_sn_register();
            if (!$sn_res) {
                abort(422, '#PCO.Не удалось очистить базу данных серийных номеров для записи');
                return false;
            }
        }
    }




    // заносим основные переменные класса
    protected function set_vars(ProductionComponent $pc)
    {
        $this->pc = $pc;
        $this->pi = $pc->production_item;
        $this->nomenklatura_title = $pc->nomenklatura;
        $this->nomenklatura = $pc->component;
        $this->ed_ism_title = $pc->ed_ism;
        // пользователь
        $user = Auth::user();
        $user_info = $user->info;
        // сотрудник
        $this->sotrudnik = $user_info->sotrudnik();
        // пользователь = администратор
        $this->is_admin = $user_info->is_admin();
        // старые значения
        $this->old = $pc->getOriginal();
        // новые значения
        $this->new = $pc;
        if ($this->pi) {
            $this->p = $this->pi->production;
            $p = $this->p;

            if ($p) {
                $this->sklad = $p->sklads;
                $this->sklad_title = $p->sklad;
                // пользователь == кладовщик
                $this->is_keeper = $this->sotrudnik->is_keeper($this->sklad->id);
            }
        }
    }

    // проверяем изменилось значение поля $field на $val
    protected function if_set($field, $val)
    {
        if ($this->pi) {
            if (isset($this->old[$field]) && isset($this->new[$field]) && $this->old[$field] != $this->new[$field] && $this->new[$field] == $val) return true;
        } else {
            abort(422, 'Чтобы использовать if_set нужно сначала инициализировать set_vars.pc');
            return false;
        }
        return false;
    }
    // проверяем изменилось значение поля
    protected function if_change($field)
    {
        if ($this->pi) {
            if (isset($this->old[$field]) && isset($this->new[$field]) && $this->old[$field] != $this->new[$field]) return true;
        } else {
            abort(422, 'Чтобы использовать if_change нужно сначала инициализировать set_vars.pc');
            return false;
        }
        return false;
    }
}