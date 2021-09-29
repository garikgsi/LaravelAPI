<?php

namespace App\Triggers;

use App\Production;
use App\ProductionItem;
use App\ProductionComponent;
use App\SkladRegister;
use App\SerialNum;
use Illuminate\Support\Facades\Auth;



class ProductionItemObserver
{

    // производство
    private $p;
    // готовые изделия
    private $pi = NULL;
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


    public function creating(ProductionItem $pi)
    {
        // инициализация переменных класса
        $this->set_vars($pi);

        // присвоим серийный номер
        $new_serial = 1;
        $last_serial = ProductionItem::whereNotNull('serial')->latest()->first();
        // серийник - id номенклатуры + 10 последних цифр дополненных нулями
        if ($last_serial) {
            $new_serial = intVal(substr($last_serial->serial, -8)) + 1;
        }
        $pi->serial = 'M' . str_pad($new_serial, 8, "0", STR_PAD_LEFT);
        // if ($this->nomenklatura)
        //     $pi->serial = $this->nomenklatura->id . str_pad($new_serial, 10, "0", STR_PAD_LEFT);
    }

    public function created(ProductionItem $pi)
    {
        // добавляем серийный № в базу данных
        if ($pi->serial) {
            $res = $pi->syncSerials($pi->serial);
            if ($res["is_error"]) {
                abort(422, "#PIO." . implode(", ", $res["errors"]));
                return false;
            }
        }
    }

    public function saving(ProductionItem $pi)
    {
        // инициализация переменных класса
        $this->set_vars($pi);

        if ($this->p) {
            // изменять проведенное могут только кладовщик или администратор
            if (($this->p->is_active == 1 || $this->pi->is_producted == 1) && !$this->is_keeper && !$this->is_admin) {
                abort(422, '#PIO.Изменять проведенную позицию можно только кладовщику или администратору');
                return false;
            }

            // если изделие приходуем на склад - проверим, чтобы документ был проведен
            if ($this->if_set('is_producted', 1)) {
                if ($this->p->is_active != 1) {
                    abort(422, '#PIO.Невозможно оприходовать на склад готовое изделие при непроведенной накладной');
                    return false;
                }
            }

            // проверим регистры накопления
            $res = $pi->mod_register(1);
            if ($res["is_error"]) {
                abort(422, "#PIO." . $res["err"]);
                return false;
            }
        }
    }

    public function saved(ProductionItem $pi)
    {
        // инициализация переменных класса
        $this->set_vars($pi);

        if ($this->p) {
            // обновим регистры накопления
            $res = $pi->mod_register(1, 'update_only');
            if ($res["is_error"]) {
                abort(422, "#PIO." . $res["err"]);
                return false;
            }


            // обновим серийные номера
            $sn_res = $pi->update_sn_register();
            if (!$sn_res) {
                abort(422, '#PIO.Не удалось обновить базу данных серийных номеров');
                return false;
            }

            // обновим все компоненты изделия
            $components = $pi->components;
            foreach ($components as $component) {
                $component->touch();
            }
        }
    }

    // проверки перед удалением
    public function deleting(ProductionItem $pi)
    {
        // инициализация переменных класса
        $this->set_vars($pi);

        if ($this->p) {
            // проверим права на удаление
            // удалять могут только кладовщик или администратор
            if (($this->p->is_active == 1 || $this->pi->is_producted == 1) && !$this->is_keeper && !$this->is_admin) {
                abort(422, '#PIO. Удалять из проведенной позиции можно только кладовщику или администратору');
                return false;
            }

            // проверим регистры накопления
            $res = $pi->mod_register(1, 'check_for_delete');
            if ($res["is_error"]) {
                abort(422, "#PIO. " . $res["err"]);
                return false;
            }
        }
    }

    // удаление
    public function deleted(ProductionItem $pi)
    {
        // инициализация переменных класса
        $this->set_vars($pi);

        if ($this->p) {
            // удалим все компоненты изделия
            $components = $pi->components;
            foreach ($components as $component) {
                $component->delete();
            }

            // удалим регистр
            $res = $pi->mod_register(1, 'delete_only');
            if ($res["is_error"]) {
                abort(422, "#PIO." . $res["err"]);
                return false;
            }
            // удалим серийные номера
            $sn_res = $pi->delete_sn_register();
            if (!$sn_res) {
                abort(422, '#PIO.Не удалось очистить базу данных серийных номеров для записи');
                return false;
            }
        }
    }


    // заносим основные переменные класса
    protected function set_vars(ProductionItem $pi)
    {
        // пользователь
        $user = Auth::user();
        $user_info = $user->info;
        // сотрудник
        $this->sotrudnik = $user_info->sotrudnik();
        // пользователь = администратор
        $this->is_admin = $user_info->is_admin();
        // старые значения
        $this->old = $pi->getOriginal();
        // новые значения
        $this->new = $pi;
        // производство
        $this->pi = $pi;
        $this->p = $pi->production;
        $p = $this->p;
        if ($p) {
            $this->nomenklatura_title = $p->nomenklatura;
            $this->nomenklatura = $p->product();
            $this->ed_ism_title = $p->ed_ism;
            $this->sklad = $p->sklad_id;
            $this->sklad_title = $p->sklad;
            // пользователь == кладовщик
            $this->is_keeper = $this->sotrudnik->is_keeper($p->sklad_id);
        }
    }

    // проверяем изменилось значение поля $field на $val
    protected function if_set($field, $val)
    {
        if ($this->pi) {
            if (isset($this->old[$field]) && isset($this->new[$field]) && $this->old[$field] != $this->new[$field] && $this->new[$field] == $val) return true;
        } else {
            abort(422, '#PIO. Чтобы использовать if_set нужно сначала инициализировать переменные');
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
            abort(422, '#PIO. Чтобы использовать if_change нужно сначала инициализировать переменные');
            return false;
        }
        return false;
    }
}