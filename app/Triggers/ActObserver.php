<?php

namespace App\Triggers;

use App\Act;
use Illuminate\Support\Facades\Auth;


class ActObserver
{
    public function creating(Act $a)
    {
        // начальные значения проведения документа
        $a->is_active = 0;

        // присвоим номер документа
        $new_doc_num = 1;
        $max_doc_num = Act::whereYear('doc_date', date('Y'))->latest()->first();
        if ($max_doc_num) {
            $max = $max_doc_num->doc_num;
            $res = preg_match("/(\d+)/", $max, $matches);
            if ($res) {
                $n = $matches[1];
                $new_doc_num = (intval($n)) + 1;
            }
        }
        $a->doc_num = $new_doc_num;
    }

    // проверки перед сохранением
    public function saving(Act $a)
    {

        // пользователь
        $user = Auth::user();
        $user_info = $user->info;
        // сотрудник
        $sotrudnik = $user_info->sotrudnik();
        // пользователь = администратор
        $is_admin = $user_info->is_admin();
        // пользователь = складарь
        $is_keeper = $sotrudnik ? $sotrudnik->is_keeper($a->sklad_id) : false;



        // если документ уже был проведен
        if ($a->getOriginal("is_active") == 1) {
            // запрещаем изменять склад
            if ($a->isDirty('sklad_id')) {
                abort(421, 'Чтобы изменить склад необходимо сначала распровести документ');
                return false;
            }
        }

        // проверки при проведении документа
        if ($a->isDirty('is_active') && $a->is_active == 1) {
            // проводить только кладовщик склада отправления
            if (!$is_keeper && !$is_admin) {
                abort(421, 'Проводить накладные может только кладовщик или администратор');
                return false;
            } else {
                // ошибки при проверке остатков
                $ostatok_err = [];
                // проверяем остатки
                foreach ($a->items as $item) {
                    $kolvo = floatVal($item->kolvo);
                    $delta = $item->check_ostatok($a->sklad_id, $item->nomenklatura_id, $kolvo);
                    if ($delta < 0) {
                        $ostatok_err[] = $item->nomenklatura . " на складе " . $a->sklad . " в количестве " . (is_null($delta) ? $kolvo : abs($delta)) . " " . $item->ed_ism;
                    }
                }
                // если есть ошибки остатков (чего-то где-то не хватает)
                if (count($ostatok_err)) {
                    $err = "(A)Недостаточно: " . implode(", ", $ostatok_err);
                    abort(421, $err);
                    return false;
                }
            }
        }

        // проверки при распроведении документа
        if ($a->isDirty('is_active') && $a->is_active == 0) {
            // проводить только кладовщик склада отправления
            if (!$is_keeper && !$is_admin) {
                abort(421, 'Распроводить накладные может только кладовщик или администратор');
                return false;
            }
        }
    }

    // сохранение
    public function saved(Act $a)
    {
        // обновим позиции накладной
        $items = $a->items;
        foreach ($items as $item) {
            $item->touch();
        }
    }

    // удаление
    public function deleted(Act $a)
    {
        // удаляем итемсы
        $items = $a->items()->withTrashed()->get();
        foreach ($items as $item) {
            if (is_null($item->deleted)) {
                $res = $item->delete();
            } else {
                $res = $item->forceDelete();
            }
        }
    }

    // восстановление
    public function restored(Act $a)
    {
        // восстанавливаем итемсы
        $items = $a->items()->withTrashed()->get();
        foreach ($items as $item) {
            $res = $item->restore();
        }
    }
}