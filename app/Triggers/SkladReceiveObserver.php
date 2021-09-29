<?php

namespace App\Triggers;

use App\SkladReceive;
use Illuminate\Support\Facades\Auth;


class SkladReceiveObserver
{

    protected $sr = NULL;
    protected $items;
    protected $sklad;
    protected $sklad_title;
    protected $sotrudnik;
    // пользователь == кладовщик
    protected $is_keeper;
    // пользователь = администратор
    protected $is_admin;
    // старые значения
    protected $old;
    // новые значения
    protected $new;


    public function creating(SkladReceive $sr)
    {
        // присвоим номер документа
        $new_doc_num = 1;
        $max_doc_num = SkladReceive::whereYear('doc_date', date('Y'))->latest()->first()->doc_num;
        if ($max_doc_num) {
            $res = preg_match("/(\d+)/", $max_doc_num, $matches);
            if ($res) {
                $n = $matches[1];
                $new_doc_num = $n + 1;
            }
        }
        $sr->doc_num = $new_doc_num;
        // грузоотправитель - равен контрагенту
        $sr->kontragent_otpravitel_id = $sr->kontragent_id;
        // грузополучатель - равен фирме
        $sr->firm_poluchatel_id = $sr->firm_id;
    }

    // проверки перед сохранением
    public function saving(SkladReceive $sr)
    {
        // инициализация служебныех переменных
        $this->set_vars($sr);

        // приходовать может только кладовщик склада
        if ($this->if_change('is_active') && !$this->is_keeper && !$this->is_admin) {
            abort(422, '#SRO. Приходовать накладные может только кладовщик или администратор');
            return false;
        }

        // проверка остатков
        // если распроводим накладную - надо проверить остатки
        if ($this->if_set('is_active', 0)) {
            $check = $this->check_unactive($sr);
            if ($check !== true) {
                abort(422, $check);
                return false;
            }
        }
    }

    public function saved(SkladReceive $sr)
    {
        // обновим позиции накладной (логика отрабатывает по сохранению позиций накладной)
        $items = $sr->items;
        foreach ($items as $item) {
            $res = $item->touch();
            if (!$res) {
                abort(422, '#SRO. Запись накладной ' . $item->id . ' не удалось сохранить');
                return false;
            }
        }
    }

    public function deleting(SkladReceive $sr)
    {
        // инициализация служебныех переменных
        $this->set_vars($sr);

        // проведенная накладная должна быть распроведена
        $sr->is_active = 0;
        // проверим остатки после распроведения
        $check = $this->check_unactive($sr);
        if ($check !== true) {
            abort(422, $check);
            return false;
        }
    }

    public function deleted(SkladReceive $sr)
    {
        // удаляем итемсы
        $items = $sr->items()->withTrashed()->get();
        foreach ($items as $item) {
            if (is_null($item->deleted)) {
                $res = $item->delete();
            } else {
                $res = $item->forceDelete();
            }
        }
    }

    public function restored(SkladReceive $sr)
    {
        // восстанавливаем итемсы
        $items = $sr->items()->withTrashed()->get();
        foreach ($items as $item) {
            $res = $item->restore();
            return false;
        }
    }




    // служебные функции
    // проверка возможности распровести / удалить накладную
    protected function check_unactive(SkladReceive $sr)
    {
        // массив того, что не хватает
        $ostatok_err = [];
        // проверим остатки
        foreach ($sr->items as $item) {
            // можно ли удалить регистры
            $reg_check = $item->check_del_register();
            if ($reg_check < 0) {
                $ostatok_err[] = $item->nomenklatura . " в количестве " . abs($reg_check) . " " . $item->ed_ism;
            }
        }
        // если есть ошибки остатков (чего-то где-то не хватает)
        if (count($ostatok_err)) {
            return "#SRO. При распроведении на складе " . $this->sklad_title . " будет нехватать: " . implode(", ", $ostatok_err);
        } else {
            return true;
        }
    }

    // заносим основные переменные класса
    protected function set_vars(SkladReceive $sr)
    {
        $this->sr = $sr;
        $this->items = $sr->items;
        $this->sklad = $sr->sklad_;
        $this->sklad_title = $sr->sklad;
        // пользователь
        $user = Auth::user();
        $user_info = $user->info;
        // сотрудник
        $this->sotrudnik = $user_info->sotrudnik();
        // пользователь == кладовщик
        $this->is_keeper = $this->sotrudnik->is_keeper($sr->getOriginal('sklad_out_id'));
        // пользователь = администратор
        $this->is_admin = $user_info->is_admin();
        // старые значения
        $this->old = $sr->getOriginal();
        // новые значения
        $this->new = $sr;
    }

    // проверяем изменилось значение поля $field на $val
    protected function if_set($field, $val)
    {
        if ($this->sr) {
            if (isset($this->old[$field]) && isset($this->new[$field]) && $this->old[$field] != $this->new[$field] && $this->new[$field] == $val) return true;
        } else {
            abort(422, '#SRO. Чтобы использовать if_set нужно сначала инициализировать переменные');
            return false;
        }
        return false;
    }
    // проверяем изменилось значение поля
    protected function if_change($field)
    {
        if ($this->sr) {
            if (isset($this->old[$field]) && isset($this->new[$field]) &&  $this->old[$field] != $this->new[$field]) return true;
        } else {
            abort(422, '#SRO. Чтобы использовать if_change нужно сначала инициализировать переменные');
            return false;
        }
        return false;
    }
}