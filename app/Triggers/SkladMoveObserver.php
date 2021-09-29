<?php

namespace App\Triggers;

use App\SkladMove;
use App\SkladRegister;
use App\Nomenklatura;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Events\SkladMoveIsOut;
use App\SkladMoveItem;


class SkladMoveObserver
{
    protected $sm = NULL;
    protected $items;
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

    public function creating(SkladMove $sm)
    {
        // проверки
        // склады должны быть разными
        if ($sm->sklad_out_id == $sm->sklad_in_id) {
            abort(422, 'Склад отправления равен складу назначения');
            return false;
        }

        // начальные значения проведения документа
        $sm->is_active = 0;
        $sm->is_in = 0;

        // присвоим номер документа
        $new_doc_num = 1;
        $max_doc_num = SkladMove::whereYear('doc_date', date('Y'))->latest()->first();
        if ($max_doc_num) {
            $max = $max_doc_num->doc_num;
            $res = preg_match("/(\d+)/", $max, $matches);
            if ($res) {
                $n = $matches[1];
                $new_doc_num = $n + 1;
            }
        }
        $sm->doc_num = $new_doc_num;
    }

    // проверки перед сохранением
    public function saving(SkladMove $sm)
    {
        // инициализация служебныех переменных
        $this->set_vars($sm);

        // склады должны быть разными
        if ($sm->sklad_out_id == $sm->sklad_in_id) {
            abort(422, 'Склад отправления равен складу назначения');
            return false;
        }
        // если распроводим или проводим - снимаем/ставим обе галочки
        if ($this->if_change('is_active')) {
            if ($this->is_admin) {
                $sm->is_in = $sm->is_active;
                $sm->is_out = $sm->is_active;
            } else {
                abort(422, 'Проводить и распроводить перемещение целиком может только администратор');
                return false;
            }
        }

        // отгружать может только кладовщик склада отправления
        if ($this->if_change('is_out') && !$this->is_out_keeper && !$this->is_admin) {
            abort(422, 'Изменять проведенные накладные может только кладовщик склада отправления или администратор');
            return false;
        }

        // получать может только кладовщик склада отправления
        if ($this->if_change('is_in') && !$this->is_in_keeper && !$this->is_admin) {
            abort(422, 'Изменять проведенные накладные может только кладовщик склада получения или администратор');
            return false;
        }

        // проверка остатков
        $ostatok_err = [];
        // если отправляем - проверяем остатки
        if ($this->if_set('is_out', 1)) {
            // проверим остатки
            foreach ($this->items as $item) {
                $kolvo = floatVal($item->kolvo);
                $delta = $item->check_ostatok($sm->sklad_out_id, $item->nomenklatura_id, $kolvo);
                if ($delta < 0) {
                    $ostatok_err[] = $item->nomenklatura . " на складе " . $this->sklad_out_title . " в количестве " . (is_null($delta) ? $kolvo : abs($delta)) . " " . $item->ed_ism;
                }
            }
        }
        // если отменяем приход - проверяем остатки
        if ($this->if_set('is_in', 0)) {
            // проверим остатки
            foreach ($this->items as $item) {
                // если есть регистр - будем проверять остатки
                if ($item->has_register(1)) {
                    $kolvo = floatVal($item->kolvo);
                    $delta = $item->check_ostatok($sm->sklad_in_id, $item->nomenklatura_id, $kolvo);
                    // dd($delta);
                    if ($delta < 0) {
                        $ostatok_err[] = $item->nomenklatura . " на складе " . $this->sklad_in_title . " в количестве " . (is_null($delta) ? $kolvo : abs($delta)) . " " . $item->ed_ism;
                    }
                }
            }
        }
        // если есть ошибки остатков (чего-то где-то не хватает)
        if (count($ostatok_err)) {
            $err = "(SM)Недостаточно: " . implode(", ", $ostatok_err);
            abort(422, $err);
            return false;
        }

        // если проведено на складе отправления и на складе назначения - делаем общую пометку проведения
        // при этом приоритетнее галочки поступления / отправки
        if ($sm->is_out == 1 && $sm->is_in == 1) {
            $sm->is_active = 1;
        } else {
            $sm->is_active = 0;
        }
    }

    // сохранение
    public function saved(SkladMove $sm)
    {
        // если документ отгружен - добавим событие
        if ($sm->getOriginal('is_out') == 0 && $sm->is_out == 1) {
            event(new SkladMoveIsOut($sm));
        }
        // обновим позиции накладной
        $items = $sm->items;
        foreach ($items as $item) {
            $item->touch();
        }
    }

    public function deleting(SkladMove $sm)
    {
        $sm->is_active = 0;
        $sm->is_in = 0;
        $sm->is_out = 0;
        // ПРОВЕРКА ОСТАТКОВ
        $items = $sm->items;
        foreach ($items as $item) {
            $ostatok_err = [];
            for ($i = 0; $i <= 1; $i++) {
                $res = $item->mod_register($i, 'check_for_delete');
                if ($res["is_error"]) $ostatok_err[] = $res["err"];
            }
        }
        if (count($ostatok_err) > 0) {
            abort(422, implode(", ", $ostatok_err));
            return false;
        }
    }

    public function deleted(SkladMove $sm)
    {
        // удаляем итемсы
        $items = $sm->items()->withTrashed()->get();
        foreach ($items as $item) {
            if (is_null($item->deleted)) {
                $res = $item->delete();
            } else {
                $res = $item->forceDelete();
            }
        }
    }

    public function restored(SkladMove $sm)
    {
        // восстанавливаем итемсы
        $items = $sm->items()->withTrashed()->get();
        foreach ($items as $item) {
            $res = $item->restore();
        }
    }


    // служебные функции
    // заносим основные переменные класса
    protected function set_vars(SkladMove $sm)
    {
        $this->sm = $sm;
        $this->items = $sm->items;
        $this->sklad_out = $sm->sklad_out_;
        $this->sklad_out_title = $sm->sklad_out;
        $this->sklad_in = $sm->sklad_in_;
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

    // проверяем изменилось значение поля $field на $val
    protected function if_set($field, $val)
    {
        if ($this->sm) {
            if (isset($this->old[$field]) && isset($this->new[$field]) && $this->old[$field] != $this->new[$field] && $this->new[$field] == $val) return true;
        } else {
            abort(422, 'Чтобы использовать if_set нужно сначала инициализировать set_vars.sm');
            return false;
        }
        return false;
    }
    // проверяем изменилось значение поля
    protected function if_change($field)
    {
        if ($this->sm) {
            if (isset($this->old[$field]) && isset($this->new[$field]) && $this->old[$field] != $this->new[$field]) return true;
        } else {
            abort(422, 'Чтобы использовать if_change нужно сначала инициализировать set_vars.sm');
            return false;
        }
        return false;
    }
}