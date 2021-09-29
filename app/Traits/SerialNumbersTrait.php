<?php

// серийные номера для таблиц поступления товаров на склад
namespace App\Traits;

use App\SerialNum;
use App\SerialNumRegister;
use App\SerialNumMove;

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
}