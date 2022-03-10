<?php

namespace App\Triggers;

use App\SerialNum;
use App\SerialNumMove;

class SerialNumObserver
{

    public function saved(SerialNum $sn)
    {
        // строка документа к которому привязан серийник
        $doc = $sn->seriable()->first();
        // если метод поддерживается (используется трейт)
        if (method_exists($doc, 'sn_movable')) {
            $res = $doc->sn_movable()->updateOrCreate(['serial_num_id' => $sn->id], ['serial_num_id' => $sn->id]);
            if (!$res) {
                abort(421, 'Перемещения по серийному № ' . $sn->number . ' не получилось создать #' . $sn->id);
                return false;
            }
        }
    }

    public function deleting(SerialNum $sn)
    {
        // серийный номер
        $serial_number = $sn->number;
        // проверим не указан ли этот серийник в других перемещениях
        // перемещения этого серийника
        $moved = $sn->serial_move;
        // форматированная ошибка
        $err = 'Серийный номер ' . $serial_number . ' невозможно удалить, т.к. он указан в следующих документах: ';
        $err_list = [];
        // если перемещений больше 1 (производство/поступление МПЗ)
        if ($moved->count() > 1) {
            // выдадим список документов, в которых этот серийник фигурирует
            foreach ($moved as $snm) {
                // типы расходных документов
                // $ReceiveClass = 'App\SkladReceiveItem';
                // $ProductionItemClass = 'App\ProductionItem';
                $ProductionComponentClass = 'App\ProductionComponent';
                $MoveClass = 'App\SkladMoveItem';
                // строка документа
                $doc_item = $snm->sn_movable;
                // получим экземпляр модели документа
                // if ($doc_item instanceof $ReceiveClass) {
                //     $doc = $doc_item->sklad_receive;
                // }
                // if ($doc_item instanceof $ProductionItemClass) {
                //     $doc = $doc_item->production;
                // }
                if ($doc_item instanceof $ProductionComponentClass) {
                    $doc = $doc_item->production;
                }
                if ($doc_item instanceof $MoveClass) {
                    $doc = $doc_item->sklad_move;
                }
                if (isset($doc) && $doc) {
                    // название документа
                    $err_list[] = $doc->select_list_title;
                }
            }
        }
        // если есть расходные документы - ошибка
        if (count($err_list) > 0) {
            // добавляем список документов в вывод ошибки
            $err .= implode(", ", $err_list);
            abort(421, $err);
            return false;
        }
    }

    public function deleted(SerialNum $sn)
    {
        // серийный номер
        $serial_number = $sn->number;
        // удаляем сопуствующие перемещения серийных номеров
        $registers = $sn->serial_move;
        foreach ($registers as $register) {
            $reg_item = $register->delete();
            if (!$reg_item) {
                abort(421, 'Перемещения по серийному№ ' . $serial_number . ' не получилось удалить');
                return false;
            }
        }
    }
}