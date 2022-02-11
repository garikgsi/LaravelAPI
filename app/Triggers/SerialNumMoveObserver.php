<?php

namespace App\Triggers;

use App\SerialNum;
use App\SerialNumMove;
use App\SerialNumRegister;
use Carbon\Carbon;

class SerialNumMoveObserver
{

    public function saved(SerialNumMove $snm)
    {
        // print_r($snm->toArray());

        // обновляем регистры
        $this->update_registers($snm);
    }

    public function deleting(SerialNumMove $snm)
    {
        $check_delete = $this->can_delete_registers($snm);
        if (!$check_delete["can"]) {
            $err = "#2:Серийный.№ '.$snm->number.' невозможно удалить из регистра, т.к. он указан в следующих документах: ";
            $err .= implode(", ", $check_delete["err"]);
            abort(421, $err);
            return false;
        }
    }

    public function deleted(SerialNumMove $snm)
    {
        // удаляем серийник из регистра
        $this->delete_registers($snm);
    }


    // вспомогательные методы
    // выдаем кол-во на складе
    protected function sn_kolvo_exists($serial_id, $sklad_id)
    {
        $sn_register_model = new SerialNumRegister;
        $sn = $sn_register_model->select(['serial_id'])->selectRaw('SUM(kolvo) as kolvo')
            ->whereDate('doc_date', '<=', date("Y-m-d"))
            ->where('serial_id', $serial_id)->where('sklad_id', $sklad_id)
            ->groupBy('serial_id')->lockForUpdate()->first();
        if ($sn) {
            $res_array = $sn->toArray();
            return intval($res_array["kolvo"]);
        } else {
            return 0;
        }
    }

    // проверка того, что серийник уже принадлежит этой записи - не нужно проверять остаток
    protected function is_sn_out(SerialNumMove $snm)
    {
        $sn_count = $snm->sn_register->where('serial_id', $snm->serial_num_id)->where('in_out', 0)->count();
        return $sn_count > 0 ? true : false;
    }

    // проверка, можно ли удалить регистры
    protected function can_delete_registers(SerialNumMove $snm)
    {
        $res = [
            "can" => true,
            "err" => []
        ];
        $registers = $snm->sn_register;
        foreach ($registers as $register) {
            if ($register->in_out == 1) {
                $reg = new SerialNumRegister;
                $created_date = Carbon::createFromFormat('Y-m-d H:i:s', $register->created_at);
                $moves = $reg->where('created_at', '>', $created_date->format('Y-m-d H:i:s'))
                    ->where('serial_id', $register->serial_id)->where('in_out', 0);
                if ($moves->count() > 0) {
                    $res["can"] = false;
                    // выдадим документы, мешающие удалению
                    foreach ($moves->get() as $move) {
                        $res["err"][] = $move->document;
                    }
                }
                unset($reg);
            }
        }
        return $res;
    }

    // удаляем регистры перемещения серийника
    protected function delete_registers(SerialNumMove $snm)
    {
        $registers = $snm->sn_register;
        // print_r($registers->toArray());
        foreach ($registers as $register) {
            $reg_item = $register->forceDelete();
            if (!$reg_item) {
                abort(421, 'Серийный.№ ' . $snm->number . ' не удален из регистра');
                return false;
            }
        }
    }

    // обновление регистра
    protected function update_registers(SerialNumMove $snm)
    {
        // строка документа к которому привязан серийник
        $doc = $snm->sn_movable;
        // типы документов, которые могут порадить создание серийных номеров
        $ReceiveClass = 'App\SkladReceiveItem';
        $ProductionItemClass = 'App\ProductionItem';
        // типы документов, которые могут порадить списание серийных номеров
        $ProductionComponentClass = 'App\ProductionComponent';
        $MoveClass = 'App\SkladMoveItem';
        $ActClass = 'App\ActItem';
        // документ проведен
        $is_active = 0;
        //  в зависимости от типа документа будут взяты разные поля
        // поступление
        if ($doc instanceof $ReceiveClass) {
            $sr = $doc->sklad_receive;
            $doc_data = [[
                "doc_date" => $sr->doc_date,
                "nomenklatura_id" => $doc->nomenklatura_id,
                "sklad_id" => $sr->sklad_id,
                "in_out" => 1,
                "kolvo" => 1
            ]];
            $is_active = $sr->is_active;
        }
        // производство - приход
        if ($doc instanceof $ProductionItemClass) {
            $p = $doc->production;
            $doc_data = [[
                "doc_date" => $p->doc_date,
                "nomenklatura_id" => $p->product()->id,
                "sklad_id" => $p->sklad_id,
                "in_out" => 1,
                "kolvo" => 1
            ]];
            $is_active = $doc->is_producted;
        }
        // производство - расход
        if ($doc instanceof $ProductionComponentClass) {
            $p = $doc->production;
            $doc_data = [[
                "doc_date" => $p->doc_date,
                "nomenklatura_id" => $doc->nomenklatura_id,
                "sklad_id" => $p->sklad_id,
                "in_out" => 0,
                "kolvo" => -1
            ]];
            $is_active = $doc->production_item->is_producted;
        }
        // продажа
        if ($doc instanceof $ActClass) {
            $act = $doc->act;
            $doc_data = [[
                "doc_date" => $act->doc_date,
                "nomenklatura_id" => $doc->nomenklatura_id,
                "sklad_id" => $act->sklad_id,
                "in_out" => 0,
                "kolvo" => -1
            ]];
            $is_active = $act->is_active;
        }
        // перемещение
        if ($doc instanceof $MoveClass) {
            $sm = $doc->sklad_move;
            $doc_data = [];
            // если отгружен
            if ($sm->is_out == 1) {
                $doc_data[] = [
                    "doc_date" => $sm->doc_date,
                    "nomenklatura_id" => $doc->nomenklatura_id,
                    "sklad_id" => $sm->sklad_out_id,
                    "in_out" => 0,
                    "kolvo" => -1
                ];
                $is_active = 1;
            }
            // если отгружен
            if ($sm->is_in == 1) {
                $doc_data[] = [
                    "doc_date" => $sm->doc_date,
                    "nomenklatura_id" => $doc->nomenklatura_id,
                    "sklad_id" => $sm->sklad_in_id,
                    "in_out" => 1,
                    "kolvo" => 1
                ];
                $is_active = 1;
            }
        }
        // данные регистра не зависящие от типа документа
        $sn_data = [
            "ou_date" => date('Y-m-d'),
            "serial_id" => $snm->serial_num_id,
        ];
        // dd($doc_data);
        // если документ найден
        if (isset($doc_data)) {
            // если документ проведен
            if ($is_active == 1) {
                // добавляем в регистр
                foreach ($doc_data as $ddata) {
                    // print_r($ddata);
                    // если списание - проверим наличие этого серийника на складе
                    if ($ddata["in_out"] == 0) {
                        // если серийник уже списан - не будем проверять остатки
                        // dd($this->is_sn_out($snm));
                        if (!$this->is_sn_out($snm)) {
                            // получим кол-во
                            $existed_kolvo = $this->sn_kolvo_exists($snm->serial_num_id, $ddata['sklad_id']);
                            // если есть серийник
                            if ($existed_kolvo < abs($ddata['kolvo'])) {
                                abort(421, "#SNMO. " . $snm->nomenklatura . ' с серийным № ' . $snm->number . ' нет на складе');
                                return false;
                            }
                        }
                    }
                    // данные регистра
                    $register_data = array_merge($sn_data, $ddata);
                    // добавляем или обновляем регистр
                    $reg_item = $snm->sn_register()->where('in_out', '=', $ddata["in_out"])->first();
                    if ($reg_item) {
                        $reg = $reg_item->update($register_data);
                    } else {
                        $sn_register_model = new SerialNumRegister;
                        $sn_register_item = $sn_register_model->fill($register_data);
                        $reg = $snm->sn_register()->save($sn_register_item);
                    }
                    if (!$reg) {
                        abort(421, '#SNMO. Серийный.№ ' . $snm->number . ' не добавлен в регистр');
                        return false;
                    }
                }
            } else {
                // удаляем из регистра
                $check_delete = $this->can_delete_registers($snm);
                if ($check_delete["can"]) {
                    $this->delete_registers($snm);
                } else {
                    $err = "#SNMO. " . $snm->nomenklatura . " с серийным.№ " . $snm->number . " невозможно удалить из регистра, т.к. он указан в следующих документах: ";
                    $err .= implode(", ", $check_delete["err"]);
                    abort(421, $err);
                    return false;
                }
            }
        } else {
            abort(421, '#SNMO. Не удалось идентифицировать документ для внесения серийного.№ ' . $snm->number . ' в регистр');
            return false;
        }
    }
}