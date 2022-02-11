<?php

// серийные номера для таблиц поступления товаров на склад
namespace App\Traits;

use App\SerialNum;
use App\SerialNumRegister;
use App\SerialNumMove;
use Carbon\Carbon;


trait SerialNumbersTrait
{

    // использовать оперативный учет
    protected $use_ou_for_serial_numbers = true;
    // поля для выборки
    protected $sn_fields_array = ["number", "end_guarantee"];

    // СВЯЗИ ТАБЛИЦ
    // связь с базой данных серийных номеров
    public function series()
    {
        return $this->morphMany('App\SerialNum', 'seriable');
    }
    // перемещение серийных номеров
    public function sn_movable()
    {
        return $this->morphMany('App\SerialNumMove', 'sn_movable');
    }

    // ЧИТАТЕЛИ
    // читатель серийных номеров
    public function getSerialsAttribute()
    {
        return $this->series()->get()->toArray();
    }

    // МЕТОДЫ

    // getter кол-во серийных номеров для строки накладной
    public function get_sn_count()
    {
        return $this->sn_movable ? intval($this->sn_movable->count()) : 0;
    }

    // getter список серийных номеров для строки накладной
    public function get_serial_numbers()
    {
        // обработаем (выведем только id с серийными №)
        $res = $this->sn_movable()->get()->map(function ($item) {
            $sn = ["id" => $item->serial_num_id];
            foreach ($this->sn_fields_array as $field) {
                $sn[$field] = $item[$field];
            }
            return $sn;
        });
        // форматированный результат
        return $res->all();
    }
    // getter возможные серийники для записи расходной накладной
    public function get_available_sn()
    {
        // типы документов, которые могут порадить списание серийных номеров
        $SkladMoveClass = 'App\SkladMoveItem';
        $ProductionClass = 'App\ProductionComponent';
        $ActClass = 'App\ActItem';
        //  в зависимости от типа документа будут взяты разные поля
        // перемещение
        if ($this instanceof $SkladMoveClass) {
            $sm = $this->sklad_move;
            $sklad_id = $sm->sklad_out_id;
            $doc_date = $sm->doc_date;
            $nomenklatura_id = $this->nomenklatura_id;
        }
        // производство
        if ($this instanceof $ProductionClass) {
            $p = $this->production;
            $sklad_id = $p->sklad_id;
            $doc_date = $p->doc_date;
            $nomenklatura_id = $this->nomenklatura_id;
        }
        // продажа
        if ($this instanceof $ActClass) {
            $a = $this->act;
            $sklad_id = $a->sklad_id;
            $doc_date = $a->doc_date;
            $nomenklatura_id = $this->nomenklatura_id;
        }
        // если документ идентифицирован
        if (isset($nomenklatura_id) && isset($sklad_id)) {
            $sn_register_model = new SerialNumRegister;
            $sn = $sn_register_model->select(['serial_id'])->selectRaw('SUM(kolvo) as kolvo')
                ->whereDate('doc_date', '<=', $this->use_ou_for_serial_numbers ? date("Y-m-d") : $doc_date)
                ->where('nomenklatura_id', $nomenklatura_id)->where('sklad_id', $sklad_id)
                ->groupBy('serial_id');
            $res = $sn->get()->filter(function ($item) {
                return $item->kolvo > 0;
            })->map(function ($item) {
                $sn = ["id" => $item->serial_id];
                foreach ($this->sn_fields_array as $field) {
                    $sn[$field] = $item[$field];
                }
                return $sn;
            });
            // для расходных документов также необходимо добавить в результат уже сохраненные записи
            // dd($res->toArray());
            $res_sn = collect($res)->merge(collect($this->get_serial_numbers()))->values()->all();
            // форматированный результат
            return $res_sn;
        }
        return [];
    }

    // setter синхронизация серийных номеров строки накладной с массивом переданных данных
    public function syncSerials($data)
    {
        // ошибки
        $errors = [];
        // типы документов, которые могут порадить создание серийных номеров
        $ReceiveClass = 'App\SkladReceiveItem';
        $ProductionItemClass = 'App\ProductionItem';
        // // типы документов, которые могут порадить списание серийных номеров
        // $ProductionComponentClass = 'App\ProductionComponent';
        // $MoveClass = 'App\SkladMoveItem';
        // если данные не массив - обернем
        if (!is_array($data)) $data = [$data];
        // проверим кол-во переданных значений количеству, указанному в документе
        $sn_kolvo = count($data);
        if ($sn_kolvo > $this->kolvo) {
            $errors[] = "Количество серийных номеров (" . $sn_kolvo . ") превышает количество учтенной номенклатуры (" . intVal($this->kolvo) . ")";
        } else {
            if ($this instanceof $ReceiveClass || $this instanceof $ProductionItemClass) {
                // если создание новых серийных номеров
                // неудаляемые id
                $existed_id = [];
                // обрабатываем данные построчно
                foreach ($data as $sn) {
                    $sn_model = new SerialNum;
                    $mod_type = 'add';
                    if (isset($sn["id"]) && !is_null($sn["id"])) {
                        $sn_model_item = $sn_model->find($sn["id"]);
                        if ($sn_model_item) {
                            $mod_type = 'edit';
                        }
                    }
                    // если передан не массив значений, а просто серийник (например в случае производства) - преобразуем его в массив
                    if (!is_array($sn)) {
                        $sn = [
                            "number" => $sn
                        ];
                    }
                    if (!isset($sn["name"])) $sn['name'] = $sn['number'];
                    // формализуем данные
                    $sn_data = $sn_model->formalize_data_from_request(null, $sn, $mod_type);
                    // если нет ошибок
                    if (!$sn_data["is_error"]) {
                        if (isset($sn_model_item)) {
                            $res = $sn_model_item->update($sn_data["data"]);
                        } else {
                            $sn_model_item = $this->series()->save($sn_model->fill($sn_data["data"]));
                        }
                        // dd($sn_model_item->toArray());
                        // не будем удалять эту запись
                        if (isset($sn_model_item->id)) {
                            $existed_id[] = $sn_model_item->id;
                        }
                    } else {
                        // dd($sn_data["errors"]["require"]);
                    }
                    // очищаем память
                    unset($sn_model);
                    unset($sn_model_item);
                }
                // удаляем все лишние строки
                $to_delete_id = $this->series->whereNotIn('id', $existed_id)->pluck('id');
                foreach ($to_delete_id as $del_id) {
                    $this->series()->find($del_id)->delete();
                }
            } else {
                // списание существующих серийных номеров
                // массив существующих серийников для записи
                $existed_id = $this->sn_movable()->pluck('serial_num_id')->all();
                // удаляем серийники не вошедшие в список
                $to_delete_id = array_diff($existed_id, $data);
                foreach ($to_delete_id as $del_id) {
                    $this->sn_movable()->where('serial_num_id', $del_id)->first()->delete();
                }
                // добавляем в список новые серийники
                $save_id = array_diff($data, $existed_id);
                if (count($save_id) > 0) {
                    foreach ($save_id as $sn) {
                        $sn_move_model = new SerialNumMove;
                        $this->sn_movable()->save($sn_move_model->fill(['serial_num_id' => $sn]));
                        unset($sn_move_model);
                    }
                }
            }
        }
        return [
            "errors" => $errors,
            "is_error" => count($errors) > 0
        ];
    }

    // проверяем и/или обновляем регистр серийных номеров
    public function mod_sn_register($mode = null)
    {
        $res = [
            "is_error" => false,
            "error" => null
        ];
        $sn_all = $this->sn_movable;
        foreach ($sn_all as $snm) {
            $res_snm = $this->mod_snm_registers($snm, $mode);
            if ($res_snm["is_error"]) {
                $res["is_error"] = true;
                $res["err"] = isset($res["err"]) ? $res["err"] . $res_snm["err"] : $res_snm["err"];
            }
        }
        return $res;
    }

    // обновим данные регистра серийных номеров
    public function update_sn_register()
    {
        $res = true;
        $sn_all = $this->sn_movable;
        foreach ($sn_all as $sn) {
            $res = $sn->touch();
        }
        return $res;
    }
    // удалим данные регистра серийных номеров
    public function delete_sn_register()
    {
        $res = true;
        $sn_all = $this->sn_movable;
        foreach ($sn_all as $sn) {
            $res = $sn->delete();
        }
        return $res;
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


    // проверка или обновление регистра
    // $mode может принимать значения : null - только проверка (по умолчанию), delete - удаление регистров,
    // update - обновление регистров, update_only - обновление без проверок, delete_only - удаление без проверок
    // check_for_delete - выполнить проверки для удаления регистров

    private function mod_snm_registers(SerialNumMove $snm, $mode = null)
    {
        // строка документа к которому привязан серийник
        $doc = $snm->sn_movable;
        // dd($doc->toArray());
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
            // кол-во номенклатуры в строке документа
            $kolvo = $doc->kolvo;
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
            // кол-во номенклатуры в строке документа
            $kolvo = $doc->kolvo;
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
            // кол-во номенклатуры в строке документа
            $kolvo = $doc->kolvo;
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
            // кол-во номенклатуры в строке документа
            $kolvo = $doc->kolvo;
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
            // кол-во номенклатуры в строке документа
            $kolvo = $doc->kolvo;
        }
        // данные регистра не зависящие от типа документа
        $sn_data = [
            "ou_date" => date('Y-m-d'),
            "serial_id" => $snm->serial_num_id,
        ];
        // если документ найден
        if (isset($doc_data)) {
            // моды при которых проверки не проводятся
            $uncheck_modes = ['update_only', 'delete_only'];
            // моды при которых производится проверка на удаление регистров
            $delete_checks_modes = ['check_for_delete'];
            $is_delete_check = in_array($mode, $delete_checks_modes);
            // если нужно проверять
            if (!in_array($mode, $uncheck_modes)) {
                // если это проверка удаления
                if ($is_delete_check) {
                    $check_delete = $this->can_delete_registers($snm);
                    if (!$check_delete["can"]) {
                        if (isset($error)) $error .= ", ";
                        $error = $snm->nomenklatura . " с серийным.№ " . $snm->number . " невозможно удалить из регистра, т.к. он указан в следующих документах: ";
                        $error .= implode(", ", $check_delete["err"]);
                    }
                } else {
                    // проверки остатков для всех записей в регистр
                    foreach ($doc_data as $ddata) {
                        // получим регистры
                        $reg_item = $snm->sn_register()->where('in_out', '=', $ddata["in_out"]);
                        $reg_exist = $reg_item->count() > 0 ? true : false;
                        $register = $reg_exist ? $reg_item->first() : null;
                        // поступление
                        if ($ddata["in_out"] == 1) {
                            // если регистр существует
                            if ($reg_exist && $register) {
                                // если поступление - проверим изменился ли склад
                                if ($register->sklad_id != $ddata["sklad_id"]) {
                                    // в остатках должен быть серийник на складе регистра
                                    // он не должен был перемещен в будущем (запись регистра можно удалить)
                                    $check_delete = $this->can_delete_registers($snm);
                                    if (!$check_delete["can"]) {
                                        $error = $snm->nomenklatura . " с серийным.№ " . $snm->number . " невозможно удалить из регистра, т.к. он указан в следующих документах: ";
                                        $error .= implode(", ", $check_delete["err"]);
                                    }
                                }
                            }
                        } else {
                            // расход
                            // если документ проведен - проверим остатки
                            if ($is_active == 1) {

                                $sn_count = $snm->sn_register->where('serial_id', $snm->serial_num_id)->where('in_out', 0)->count();
                                $is_this_reg = $sn_count > 0 ? true : false;

                                if (!$is_this_reg) {
                                    // получим кол-во
                                    $existed_kolvo = $this->sn_kolvo_exists($snm->serial_num_id, $ddata['sklad_id']);
                                    // если есть серийник
                                    if ($existed_kolvo < abs($ddata['kolvo'])) {
                                        $error = $snm->nomenklatura . ' с серийным № ' . $snm->number . ' нет на складе';
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // если нет ошибок при проверке - выполняем действия согласно мода
            if (!isset($error)) {
                switch ($mode) {
                    case 'update':
                    case 'update_only': {
                            foreach ($doc_data as $ddata) {
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
                                    $error = 'Для серийного.№ ' . $snm->number . ' регистр не актуален';
                                }
                            }
                        }
                        break;
                    case 'delete':
                    case 'delete_only': {
                            $registers = $snm->sn_register;
                            // print_r($registers->toArray());
                            foreach ($registers as $register) {
                                $reg_item = $register->forceDelete();
                                if (!$reg_item) {
                                    $error = 'Серийный.№ ' . $snm->number . ' не удален из регистра';
                                }
                            }
                        }
                        break;
                }
            }
        } else {
            $error = 'Не удалось идентифицировать документ для внесения серийного.№ ' . $snm->number . ' в регистр';
        }

        return [
            "is_error" => isset($error) ? true : false,
            "err" => isset($error) ? $error : null
        ];
    }
}