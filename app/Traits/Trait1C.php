<?php

// импорт / экспорт в 1С

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Common\API1C;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

use App\Nomenklatura;
use App\EdIsm;


trait Trait1C
{

    // id склада в 1С (в 1с нет учета по складам)
    protected $default_sklad_uuid = 'f40235d3-d84c-11ea-8133-0050569f62a1';

    //

    // // получить uuid, если он есть
    // public function get_uuid()
    // {
    //     return $this->uuid ? $this->uuid : null;
    // }

    // геттер id по uuid
    public function id_by_uuid($uuid)
    {
        $res_data = $this->where("uuid", $uuid)->first();
        return $res_data ? $res_data->id : null;
    }

    // выдаем имя таблицы БД по имени в 1С
    public function get_table_name_by_1c_name($table_1c_name)
    {
        foreach ($this->tables() as $table => $table_details) {
            if (isset($table_details["Name1C"]) && $table_details["Name1C"] == $table_1c_name) return $table;
        }
        return null;
    }

    // выдаем поля описанные в 1с для модели
    public function get_1c_fields()
    {
        // все таблицы
        $tables = $this->tables();
        // результат
        $res = [];
        // для справочника
        switch ($this->table_type()) {
            case "catalog": {
                    foreach ($this->model() as $field) {
                        if (isset($field["name_1c"])) {
                            $res_field = ["name" => $field["name"], "name_1c" => $field["name_1c"], "type" => $field["type"]];
                            if (isset($field["table"])) {
                                $res_field["table"] = $field["table"];
                                if ($field["table"] != "polymorph") {
                                    if (isset($ables[$field["table"]]["Class"])) $res_field["table_class"] = $tables[$field["table"]]["Class"];
                                    if (isset($tables[$field["table"]]["Name1C"])) $res_field["table_name_1c"] = $tables[$field["table"]]["Name1C"];
                                    $res_field["table_title"] = $tables[$field["table"]]["Title"];
                                }
                            }
                            $res[] = $res_field;
                        }
                    }
                }
                break;

            case "document": { // настройки заполнения полей для документов
                    // поля - исключения
                    $except_fields = ["name", "comment", "is_protected"];
                    foreach ($this->model() as $field) {
                        if (!in_array($field["name"], $except_fields) && isset($field["name_1c"])) {
                            $res_field = ["name" => $field["name"], "name_1c" => $field["name_1c"], "type" => $field["type"]];
                            if (isset($field["table"])) {
                                if (isset($tables[$field["table"]]["Class"])) $res_field["table_class"] = $tables[$field["table"]]["Class"];
                                if (isset($tables[$field["table"]]["Name1C"])) $res_field["table_name_1c"] = $tables[$field["table"]]["Name1C"];
                                $res_field["table_title"] = $tables[$field["table"]]["Title"];
                                $res_field["table"] = $field["table"];
                            }
                            $res[] = $res_field;
                        }
                    }
                }
                break;
            case "sub_table": { // настройки заполнения полей для подчиненных таблиц
                    // поля - исключения
                    $except_fields = ["id", "name", "comment", "is_protected"];
                    foreach ($this->model() as $field) {
                        if (!in_array($field["name"], $except_fields) && isset($field["name_1c"])) {
                            $res_field = ["name" => $field["name"], "name_1c" => $field["name_1c"], "type" => $field["type"]];
                            if (isset($field["table"])) {
                                if (isset($tables[$field["table"]]["Class"])) $res_field["table_class"] = $tables[$field["table"]]["Class"];
                                if (isset($tables[$field["table"]]["Name1C"])) $res_field["table_name_1c"] = $tables[$field["table"]]["Name1C"];
                                $res_field["table_title"] = $tables[$field["table"]]["Title"];
                                $res_field["table"] = $field["table"];
                            }
                            $res[] = $res_field;
                        }
                    }
                }
                break;
        }
        return $res;
    }

    // получим данные по uid из 1С и формализуем их в формат вставки в бд
    public function get_formalize_data_from_1c($uuid)
    {
        // данные для вставки в бд
        $db_data = [];
        // отложенные вставки (данные ссылающиеся на эту таблицу)
        $delayed_inserts = [];
        // получим строку данных
        $http = new API1C();
        // получаем данные из 1с, если есть
        $data_1c = $http->is_exists($this->name_1c(), $uuid);
        // если данные получены из 1С
        if ($data_1c) {
            // выбираем только те, что есть в массиве полей 1С
            $fields = $this->get_1c_fields();
            // dd($fields);
            // обработаем все поля
            foreach ($fields as $field) {
                $field_name_1c = $field['name_1c'];
                $field_name_db = $field['name'];
                $field_type = $field['type'];
                // если значение есть в массиве данных 1С
                if ((is_string($field_name_1c) && property_exists($data_1c, $field_name_1c) || $field_type == 'morph')) {
                    // значение в зависимости от типа поля
                    switch ($field_type) {
                        case 'morph': {
                                $pf_table = null;
                                // проверим, переданы ли свойства полиморфа
                                for ($i = 0; $i < 2; $i++) {
                                    $pf_field_name = $field_name_1c[$i];
                                    if (property_exists($data_1c, $pf_field_name)) {
                                        if ($i == 0) {
                                            $pf_type = mb_substr($data_1c->$pf_field_name, 14, NULL, 'UTF-8');
                                            $pf_table = $this->get_table_name_by_1c_name($pf_type);
                                        } else {
                                            $pf_id = $data_1c->$pf_field_name;
                                        }
                                    } else {
                                        // не переданы полифилы
                                    }
                                }
                                // если корректно распознана таблица
                                if ($pf_table) {
                                    $foreign_table = $this->new_table($pf_table);
                                    $db_data[$field_name_db . "_type"] = get_class($foreign_table);
                                    if ($pf_id == "00000000-0000-0000-0000-000000000000") {
                                        $db_data[$field_name_db . "_id"] = 1;
                                    } else {
                                        if (method_exists($foreign_table, 'id_by_uuid')) {
                                            $foreign_id = $foreign_table->id_by_uuid($pf_id);
                                            if ($foreign_id) {
                                                $db_data[$field_name_db . "_id"] = $foreign_id;
                                            } else {
                                                // такого id не найдено - вставим запись после добавления текущей
                                                //  и потом изменим столбец target_field
                                                //      target_field - значение столбца, в который нужно будет занести id вставленной записи
                                                //      data_table - db-имя таблицы в которую необходимо вставить запись
                                                //      data_uuid - uuid 1С, который нужно вставить в БД и получнный id внести в target_field
                                                $delayed_inserts[] = [
                                                    "target_field" => $field_name_db . "_id",
                                                    "data_table" => $pf_table,
                                                    "data_uuid" => $pf_id
                                                ];
                                                $db_data[$field_name_db . "_id"] = 1;
                                            }
                                        } else {
                                            $db_data[$field_name_db . "_id"] = 1;
                                        }
                                    }
                                }
                                // dd($db_data);
                            }
                            break;
                        case 'select': {
                                if ($data_1c->$field_name_1c == "00000000-0000-0000-0000-000000000000") {
                                    $db_data[$field_name_db] = 1;
                                    // echo "<p>" . $field_name_1c . " is 00000000-0000-0000-0000-000000000000</p>";
                                } else {
                                    if (isset($field["table"])) {
                                        $foreign_table = $this->new_table($field["table"]);
                                        if (method_exists($foreign_table, 'id_by_uuid')) {
                                            $foreign_id = $foreign_table->id_by_uuid($data_1c->$field_name_1c);
                                            if ($foreign_id) {
                                                $db_data[$field_name_db] = $foreign_id;
                                                // echo "<p>в таблице " . $field["table"] . " найден id=" . $foreign_id . " по uuid= " . $data_1c->$field_name_1c . "</p>";
                                            } else {
                                                // такого id не найдено - вставим запись после добавления текущей
                                                //  и потом изменим столбец target_field
                                                $delayed_inserts[] = [
                                                    "target_field" => $field_name_db,
                                                    "data_table" => $field["table"],
                                                    "data_uuid" => $data_1c->$field_name_1c
                                                ];
                                                $db_data[$field_name_db] = 1;

                                                // echo "<p>в таблице " . $field["table"] . " не найден id соответствующий uuid= " . $data_1c->$field_name_1c . "</p>";
                                                // print_r(end($delayed_inserts));
                                                // echo "</p>";

                                            }
                                        } else {
                                            // ошибка импорта
                                            // таблица не поддерживаем импорт в 1С
                                            $db_data[$field_name_db] = 1;
                                            // echo "<p>таблица не поддерживает импорт 1С " . $field["table"] . "</p>";
                                        }
                                    } else {
                                        // таблица не передана в описании пеля модели
                                        // не понятно откуда извлекать данные
                                        $db_data[$field_name_db] = 1;
                                    }
                                }
                            }
                            break;
                        default: {
                                $db_data[$field_name_db] = $data_1c->$field_name_1c;
                            }
                    }
                }
            }
        }
        // echo "<br>----";
        // echo "<p>";
        // print_r($db_data);
        // echo "</p>";
        return [
            "data" => $db_data,
            "sub_tables" => null,
            "delayed_inserts" => $delayed_inserts
        ];
    }

    // загрузка данных строки из 1с по uuid
    public function load_from_1c_by_uuid($uuid)
    {
        $res = null;
        // определим текущий момент
        $now = DB::raw("NOW()");
        // данные для вставки
        $row_data = $this->get_formalize_data_from_1c($uuid);
        // если есть такой уид в таблице - редактируем, иначе добавляем
        if ($id = $this->id_by_uuid($uuid)) {
            // здесь, если нужно  - делаем проверку на необходимость изменений
            $mod_type = 'edit';
            $row_data['data']['id'] = $id;
        } else {
            $mod_type = 'add';
        }
        // добавим дату синхронизации с 1с
        $row_data['data']['sync_1c_at'] = $now;
        // результат сохранения

        // echo "<p>Row data is:";
        // print_r($row_data);
        // echo "</p>";

        $res = $this->save_recursive($r = new Request, $row_data, $mod_type);
        // обрабатываем отложенные вставки
        if (!$res["is_error"]) {
            // полученная модель
            $current_model = $res["res"];
            // если есть отложенные вставки
            if (isset($row_data["delayed_inserts"])) {
                foreach ($row_data["delayed_inserts"] as $delayed_insert) {
                    // создаем экземпляр таблицы
                    $foreign_table = $this->new_table($delayed_insert["data_table"]);
                    // импортируем по uuid
                    $delay_ins_res = $foreign_table->load_from_1c_by_uuid($delayed_insert["data_uuid"]);
                    if ($delay_ins_res) {
                        if (isset($delay_ins_res["data"]["id"])) {
                            $current_model->{$delayed_insert["target_field"]} = $delay_ins_res["data"]["id"];
                            $current_model->save();
                        }
                    }
                }
            }
            // данные
            if ($current_model) {
                $result_data = array_merge($current_model->toArray(), $res["data"]);
            } else {
                $result_data = null;
            }
            // результат
            return [
                "is_error" => $res["is_error"],
                "errors" => $res["errors"],
                "res" => $current_model,
                "data" => $result_data
            ];
        } else {
            return $res;
        }
    }

    // загрузка данных из 1с
    public function load_from_1c()
    {
        // результат
        $res = null;
        // создаем новое соединение с 1С
        $http = new API1C();
        // параметры запроса
        $request_params_1c = "$" . "select=Ref_Key";
        if ($this->has_folders_1c()) $request_params_1c .= "&$" . "filter=IsFolder eq false";

        // получаем список справочника из 1С
        $response_from_1c = $http->get($this->name_1c(), $request_params_1c);

        // print_r($response_from_1c);

        if ($response_from_1c["is_error"]) {
            $sync_res_errors[] = $response_from_1c["err_text"] . ", код [" . $response_from_1c["code"] . "]";
        } else {
            // проверяем код ответа сервера и если 200, то заносим данные в БД
            switch ($response_from_1c["code"]) {
                case 200: {
                        // определим текущий момент
                        $now = DB::raw("NOW()");

                        // добавим данные из 1С
                        // dd($response_from_1c["data"]->value);
                        foreach ($response_from_1c["data"]->value as $data_1c) {
                            // var_dump($data_1c);
                            if (isset($data_1c->Ref_Key)) {

                                // echo "<br/><p>-----------------------------</p>";
                                // echo "INSERT " . $this->table() . " record with UUID=" . $data_1c->Ref_Key;

                                $res = $this->load_from_1c_by_uuid($data_1c->Ref_Key);

                                // echo "<p>Result is:";
                                // print_r($res);
                                // echo "</p>";
                            }
                        }
                    }
            }
        }
        return $res;
    }

    // выдаем uuid по id, если запись синхронизирована с 1С
    // или вставляем запись и выдаем uuid
    public function get_uuid($add_data = [])
    {
        if ($this->uuid) {
            return $this->uuid;
        } else {
            $res = $this->export_to_1c($add_data);
            if (!$res["is_error"]) {
                if (isset($res["res"]->uuid)) {
                    return $res["res"]->uuid;
                }
            }
            // ошибка добавления
            return '00000000-0000-0000-0000-000000000000';
        }
    }

    // формализуем данные для импорта в 1С
    public function get_formalize_data_for_1c($mod_type = 'add', $add_data = [])
    {

        // в табличных частях передавать надо ссылки, как в запросах POST, т.е. Nomenklatura_Key = "d5e37797-e894-11eb-b4ad-000c29b45b09"

        // пример формата данных для изменения
        //     "data" => array:7 [▼
        //     "Number" => "3/2"
        //     "Date" => "2021-07-08T00:00:00+00:00"
        //     "Организация@odata.bind" => "Catalog_Организации(guid'87fa6cc2-e901-11ea-8591-000c29b45b09')"
        //     "Склад@odata.bind" => "Catalog_Склады(guid'f40235d3-d84c-11ea-8133-0050569f62a1')"
        //     "СчетЗатрат_Key" => "86eff6b6-d84c-11ea-8133-0050569f62a1"
        //     "Продукция" => array:1 [▼
        //     0 => array:6 [▼
        //         "LineNumber" => "1"
        //         "Номенклатура_Key" => "d5e37797-e894-11eb-b4ad-000c29b45b09"
        //         "ЕдиницаИзмерения_Key" => "f40235d5-d84c-11ea-8133-0050569f62a1"
        //         "Коэффициент" => 1
        //         "Количество" => "1.000"
        //         "Счет_Key" => "86eff6ca-d84c-11ea-8133-0050569f62a1"
        //     ]
        //     ]
        //     "Материалы" => array:2 [▼
        //     0 => array:6 [▼
        //         "LineNumber" => 1
        //         "Номенклатура_Key" => "d5e37798-e894-11eb-b4ad-000c29b45b09"
        //         "Счет_Key" => "86eff64f-d84c-11ea-8133-0050569f62a1"
        //         "ЕдиницаИзмерения_Key" => "f40235d5-d84c-11ea-8133-0050569f62a1"
        //         "Коэффициент" => 1
        //         "Количество" => 1.0
        //     ]
        //     1 => array:6 [▼
        //         "LineNumber" => 2
        //         "Номенклатура_Key" => "d5e37799-e894-11eb-b4ad-000c29b45b09"
        //         "Счет_Key" => "86eff64f-d84c-11ea-8133-0050569f62a1"
        //         "ЕдиницаИзмерения_Key" => "f40235d5-d84c-11ea-8133-0050569f62a1"
        //         "Коэффициент" => 1
        //         "Количество" => 10.0
        //     ]
        //     ]
        // ]

        // выходной массив данных для 1С
        $data = [];
        // получим поля 1С для модели
        $fields_1c = $this->get_1c_fields();
        // перечень полей не подлежащих экспорту
        $ignore_fields = ['is_active', 'uuid'];
        // обработаем поля в соответствии с типом
        foreach ($fields_1c as $field) {
            $field_name_1c = $field['name_1c'];
            $field_name_db = $field['name'];
            $field_type = $field['type'];
            // кроме игнорируемых полей
            if (!in_array($field_name_db, $ignore_fields)) {
                // преобразования в соответствии с типом
                switch ($field_type) {
                    case 'select': {
                            // // название поля 1С без окончания _Key
                            $field_name_1c_wo_key = mb_substr($field_name_1c, 0, (mb_strlen($field_name_1c, 'UTF-8') - 4), 'UTF-8');
                            // если значение определено
                            if ($this->$field_name_db != 1) {
                                if (isset($field["table"])) {
                                    $foreign_table = $this->new_table($field["table"]);
                                    $foreign_data = $foreign_table->find($this->$field_name_db);
                                    if ($foreign_data) {
                                        // dd($foreign_data);
                                        $field_val = $foreign_data->get_uuid();
                                    } else {
                                        // dd('fuck');
                                        // нарушение ссылочной целосности бд
                                        // нет такого id в подчиненной таблице
                                    }
                                } else {
                                    // dd('no table');
                                    // таблица не описана в структуре поля
                                }
                            }
                            // неопределенное значение заменим нулями
                            if (!isset($field_val)) $field_val = '00000000-0000-0000-0000-000000000000';
                            // форматируем значение в зависимости от типа запроса
                            // edit: put || patch, add: post
                            if ($mod_type == 'edit') {
                                // правильный формат для изменения методом PATCH
                                // "Организация@odata.bind" => "Catalog_Организации(guid'87fa6cc2-e901-11ea-8591-000c29b45b09')"
                                // если таблица импортируется в 1С
                                if (method_exists($foreign_table, 'name_1c')) {
                                    $data[$field_name_1c_wo_key . "@odata.bind"] =  $foreign_table->name_1c() . "(guid'" . $field_val . "')";
                                } else {
                                    // аналога таблицы в 1С нет
                                }
                            } else {
                                // правильный формат для добавления методом POST
                                // "Организация_Key" => "87fa6cc2-e901-11ea-8591-000c29b45b09"
                                $data[$field_name_1c] =  $field_val;
                            }
                        }
                        break;
                    case 'boolean': {
                            $data[$field_name_1c] = boolval($this->$field_name_db);
                        }
                        break;
                    case 'date':
                    case 'datetime': {
                            $data[$field_name_1c] = date('c', strtotime($this->$field_name_db));
                        }
                        break;
                    default: {
                            $data[$field_name_1c] = $this->$field_name_db;
                        }
                }
            }
        }

        // преобразования для конкретных типов документов
        // типы документов
        $ReceiveClass = 'App\SkladReceive';
        $ProductionClass = 'App\Production';
        $NomenklaturaClass = 'App\Nomenklatura';
        // если это номенклатура
        if ($this instanceof $NomenklaturaClass) {
            $data["ВидНоменклатуры_Key"] = $this->nomenklatura_type_uuid('Материалы', 'f40235d8-d84c-11ea-8133-0050569f62a1');
        }
        // если это поступление
        if ($this instanceof $ReceiveClass) {
            // склад
            // заменяем данные специфичными значениями
            if ($mod_type == 'edit') {
                $data["Склад@odata.bind"] = "Catalog_Склады(guid'" . $this->sklad_uuid() . "')";
            } else {
                $data["Склад_Key"] = $this->sklad_uuid();
            }

            // пустые табличные части
            $data["Товары"] = [];
            $data["Услуги"] = [];
            // #строки
            $line_num = [
                "u" => 1,
                "t" => 1
            ];
            // обработаем табличную часть
            foreach ($this->items as $item) {
                // номенклатура
                $n = Nomenklatura::find($item->nomenklatura_id);

                $line_data = [
                    "Номенклатура_Key" => $n->get_uuid(["ВидНоменклатуры_Key" => $this->nomenklatura_type_uuid('Материалы', 'f40235d8-d84c-11ea-8133-0050569f62a1')]),
                    "Содержание" => $item->nomenklatura,
                    "Количество" => floatVal($item->kolvo),
                    "Цена" => floatVal($item->price),
                    "Сумма" => floatVal($item->summa),
                    "СтавкаНДС" => $item->stavka_nds,
                    "СуммаНДС" => floatVal($item->summa_nds),
                    "СчетУчета_Key" => $this->ac_uuid("10.01", "86eff64f-d84c-11ea-8133-0050569f62a1")
                ];
                // если услуга - в таблицу с услугами
                if ($item->is_usluga) {
                    $line_data["LineNumber"] = $line_num["u"];
                    $line_num["u"]++;
                    $data["Услуги"][] = $line_data;
                } else {
                    $line_data["LineNumber"] = $line_num["t"];
                    $line_num["t"]++;
                    $data["Товары"][] = $line_data;
                }
            }
        }

        // если это производство
        if ($this instanceof $ProductionClass) {
            // выпускаемая продукция
            $prod = Nomenklatura::find($this->nomenklatura_id);
            // единица выпускаемой продукции - всегда штуки
            $ed_ism = EdIsm::where('okei', 796)->first();
            // данные производства
            $data = array_merge($data, [
                "СчетЗатрат_Key" => $this->ac_uuid("20.01", "86eff6b6-d84c-11ea-8133-0050569f62a1")
            ]);
            // добавляем продукцию
            $data["Продукция"][] = [
                "LineNumber" => "1",
                "Номенклатура_Key" => $prod->get_uuid(["ВидНоменклатуры_Key" => $this->nomenklatura_type_uuid('Продукция', 'f40235be-d84c-11ea-8133-0050569f62a1')]),
                "ЕдиницаИзмерения_Key" => $ed_ism->get_uuid(),
                "Коэффициент" => 1,
                "Количество" => $this->kolvo,
                "Счет_Key" => $this->ac_uuid("43", "86eff6ca-d84c-11ea-8133-0050569f62a1")
            ];
            // заменяем данные специфичными значениями
            if ($mod_type == 'edit') {
                $data["Склад@odata.bind"] = "Catalog_Склады(guid'" . $this->sklad_uuid() . "')";
            } else {
                $data["Склад_Key"] = $this->sklad_uuid();
            }
            // добавим материалы
            $data["Материалы"] = [];
            $this->makeVisible(['zip_components']);
            $components = $this->zip_components;
            // #строки
            $line_num = 1;
            // пройдемся по компонентам
            foreach ($components as $component) {
                // номенклатура материала
                $material = Nomenklatura::find($component["nomenklatura_id"]);
                $ed_ism = $material->ed_ism_()->first();
                $data["Материалы"][] = [
                    "LineNumber" => $line_num,
                    "Номенклатура_Key" => $material->get_uuid(["ВидНоменклатуры_Key" => $this->nomenklatura_type_uuid('Материалы', 'f40235d8-d84c-11ea-8133-0050569f62a1')]),
                    "Счет_Key" => $this->ac_uuid("10.01", "86eff64f-d84c-11ea-8133-0050569f62a1"),
                    "ЕдиницаИзмерения_Key" => $ed_ism->get_uuid(),
                    "Коэффициент" => 1,
                    "Количество" => floatVal($component["kolvo"]),
                ];
                // инкремент строки
                $line_num++;
            }
        }
        // если есть данные, которые переданы для внесения при добавлении
        $data = array_merge($data, $add_data);

        // выдаем данные в формате 1С
        return $data;
    }

    // экспорт в 1С текущей записи
    public function export_to_1c($add_data = [])
    {
        // массив ошибок
        $errors = [];
        // логи
        $logs = [];
        // определяем тип запроса
        // по умолчанию add
        $mod_type = 'add';
        // надо синхронизировать
        $need_sync = true;
        // начало синхронизации
        $logs[] = "Начало синхронизации с 1С записи " . $this->table() . " " . $this->id;
        // если документ - проверим, чтобы он был проведен
        if ($this->table_type() == 'document') {
            if (boolval($this->is_active) === false) {
                $need_sync = false;
                $logs[] = "Документ не проведен. Пропускаем...";
            }
        }
        // если есть uuid
        if ($need_sync && $this->uuid && $this->uuid != '00000000-0000-0000-0000-000000000000') {
            $logs[] = "Найден UUID=" . $this->uuid;
            // обновляем
            $mod_type = 'edit';
            // var_dump($this->sync_1c_at);
            // dd(is_null($this->sync_1c_at), $this->sync_1c_at);
            if (!is_null($this->sync_1c_at)) {
                // проверим даты изменения документа и предыдущей синхронизации документа с 1С
                $sync_1c_at = new Carbon($this->sync_1c_at);
                $updated_at = new Carbon($this->updated_at);
                $created_at = new Carbon($this->created_at);
                // если дата синхронизации больше или равна даты создания и даты обновления - синхронизировать не надо
                if ($sync_1c_at->gte($created_at) && $sync_1c_at->gte($updated_at)) {
                    $need_sync = false;
                }
            }
        }
        // dd($need_sync);
        // если нужно синхронизировать
        if ($need_sync) {
            // данные для 1С
            $row_data = $this->get_formalize_data_for_1c($mod_type, $add_data);
            // dd($row_data);
            $logs[] = [
                "data" => $row_data,
                "method" => $mod_type
            ];
            // dd($row_data);
            // создаем новое соединение с 1С
            $http = new API1C();
            // отправляем запрос в 1С
            switch ($mod_type) {
                case 'add': {
                        $logs[] = "Начинаем создание записи в 1С...";
                        $response_from_1c = $http->post($this->name_1c(), $row_data);
                    }
                    break;
                case 'edit': {
                        $logs[] = "Начинаем обновление записи в 1С...";
                        $response_from_1c = $http->update($this->name_1c(), $this->uuid, $row_data);
                    }
                    break;
            }
            // var_dump($response_from_1c);
            // если не было ошибок
            if (isset($response_from_1c["is_error"]) && !$response_from_1c["is_error"]) {
                // если переданы данные
                if (isset($response_from_1c["data"])) {
                    // определим текущий момент
                    $now = DB::raw("NOW()");
                    // заменим uuid и sync_1c_at
                    $this->uuid = $response_from_1c["data"]->Ref_Key;
                    $this->sync_1c_at = $now;
                    $this->save();
                    $logs[] = "Документ успешно синхронизирован";
                }
            } else {
                if (isset($response_from_1c["err_text"])) {
                    $errors[] = $response_from_1c["err_text"];
                } else {
                    $errors[] = 'Обработка запроса 1С завершилась неизвестной ошибкой';
                }
                $logs[] = "Документ не синхронизирован. Ошибка!";
            }
        } else {
            $logs[] = "Документ уже был синхронизирован. Пропускаем...";
        }
        // dd($this->uuid, $mod_type, $row_data, $response_from_1c);
        // вызвращаем результат
        // dd($this->toArray());
        return [
            "is_error" => count($errors) > 0,
            "errors" => $errors,
            "logs" => $logs,
            // "data" => $this->toArray(),
            "res" => $this
        ];
    }


    // загрузка базовых справочников из 1С
    public function load_catalogs_from_1c()
    {
        $catalogs = [
            "rs",
            "ed_ism",
            "firms",
            "banks",
            "doc_types",
            "valuta"
        ];

        foreach ($catalogs as $table) {
            $t = $this->new_table($table);
            $t->load_from_1c();
        }
    }

    // получим uuid склада из 1С (не используем учет по складам)
    public function sklad_uuid()
    {
        // Cache::flush();
        $value = Cache::rememberForever('sklad_uuid', function () {
            // создаем новое соединение с 1С
            $http = new API1C();
            $response_1c = $http->get('Catalog_Склады', "\$select=Ref_Key&\$filter=Description eq 'Основной склад'");
            if (isset($response_1c["data"]->value[0]->Ref_Key)) {
                return $response_1c["data"]->value[0]->Ref_Key;
            } else {
                return $this->default_sklad_uuid;
            }
        });
        return $value;
    }

    // получим uuid счета из плана счетов
    public function ac_uuid($ac, $default)
    {
        $value = Cache::rememberForever('ac_' . $ac . '_uuid', function () use ($default, $ac) {
            // создаем новое соединение с 1С
            $http = new API1C();
            $response_1c = $http->get('ChartOfAccounts_Хозрасчетный', "\$select=Ref_Key&\$filter=Code eq '" . $ac . "'");
            if (isset($response_1c["data"]->value[0]->Ref_Key)) {
                return $response_1c["data"]->value[0]->Ref_Key;
            } else {
                return $default;
            }
        });
        return $value;
    }
    // получим uuid типа номенкладтуры
    public function nomenklatura_type_uuid($nomenklatura_type, $default)
    {
        $value = Cache::rememberForever('nt_' . $nomenklatura_type . '_uuid', function () use ($default, $nomenklatura_type) {
            // создаем новое соединение с 1С
            $http = new API1C();
            $response_1c = $http->get('Catalog_ВидыНоменклатуры', "\$select=Ref_Key&\$filter=Description eq '" . $nomenklatura_type . "'");
            if (isset($response_1c["data"]->value[0]->Ref_Key)) {
                return $response_1c["data"]->value[0]->Ref_Key;
            } else {
                return $default;
            }
        });
        return $value;
    }
}