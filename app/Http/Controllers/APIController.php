<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use App\ABPTable;
use App\Common\ABPField;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Common\ABPResponse;
use App\Common\ABPStorage;
use Exception;
use Illuminate\Support\Facades\Log;

use App\File;
use App\FileDriver;
use App\FileType;
use App\TableTag;
use App\Tag;
use App\FileList;
use App\SerialNum;
use App\Nomenklatura;

use App\SkladReceiveItem;

set_time_limit(0);

// ЯЗЫК ЗАПРОСОВ К API
//
// GET /api/v1/table_name - вывод 1-й страницы данных (по умолчанию сортируется по столбцу name, 10 строк на странице)
// GET /api/vi/table_name/N - вывод всех полуй записи таблицы table_name с id=N
// GET /api/v1/table_name?odata=[full|data|model|list|count] - формат вывода данных data - только данные, model - только модель столбцов таблицы,
//                                                      full - данные и модель, list - список в виде массива ['id'=>'N', 'title'=>'template']
//                                                      (формат вывода list определяется методом listFormat класса ABPTable, в случае неверно
//                                                      указанного шаблона будем передавать таблицу целиком), count - только посчитать записи
//     Параметры фильтрации в запросе GET:
//         &fields=fieldName1,fieldName2,...,fieldNameN - вывод только перечисленных столбцов таблицы
//         &order=id,[desc|asc] - сортировка выдачи: поле,порядок сортировки
//         &filter=fieldName1[lt|gt|eq|ne|ge|le|like]filterValue1 [or|and] fieldName2[lt|gt|eq|ne|ge|le|like]filterValue1 -
//             доступные операнды:
//                 lt => меньше
//                 gt => больше
//                 eq => равно
//                 ne => не равно
//                 ge => больше или равно
//                 le => меньше или равно
//                 like => like
//                 in => входит в массив (IN)
//                 ni => не входит в массив (NOT IN)
//             !! к операнду like значение обрамляется %% с обеих сторон
//             доступные условия:
//                 or => ИЛИ
//                 and => И
//             !!невозможно указывать условия, обрамленные в скобки
//         &search=text - поиск по всем возможным полям
//         &tags=id1,id2,...,idN - дополнительный фильтр по тегам (выбор должен содержать строки имеющий хотя бы 1 тег)
//         &extensions=ext1,ext2,...,extN - добавить в ответ расширения для записи из возможных [files,images,groups,file_list,main_image,select_list_title]
//         &scope=stock_balance.9, - добавить в запрос scope. Параметры передаются через точки, скопы разделяются запятыми
//         &offset - смещение относительно 0-го элемента выдачи, отсортированного согласно правилам сортировки (только совместно с limit)
//         &limit - количество выдаваемых значений выдачи (-1 для отсутствия лимитов)
//         &trashed=1 - выдавать помеченные на удаление записи
//
// POST /api/v1/table_name - добавление записи в таблицу table_name. Ответ при успехе - 201 и вставленная запись в объекте data, в случае ошибки - 500
// PUT|PATCH /api/v1/table_name/N - изменение записи с id=N в таблице table_name. В ответе count сервер вернет кол-во измененных записей
// PATCH /api/v1/table_name/N/post - проводим документ с id=N в таблице table_name. В запросе необходимо передать массив полей для проведения (в моделе должны быть отмечены признаком "post"=>true). В ответе count сервер вернет измененную запись
// DELETE /api/v1/table_name/N - удаление записи с id=N в таблице table_name. В ответе count сервер вернет кол-во удаленных записей или true

// Формат ответа сервера:
// {
//     "is_error": false,           /* булево поле наличия ошибки */
//     "error": "",                 /* текстовое описание ошибки */
//     "count": 4,                  /** количество записей в таблице соответствующих запросу */
//     "data": [],                  /** массив объектов данных */
//     "time_request": "0.596 sec", /** справочно - время выборки данных */
//     "model": []                  /** модель структуры таблицы, если передан параметр odata */
// }

// Коды ответов сервера:

// 200 OK - самый часто используемый код, свидетельствующий об успехе операции;
// 201 CREATED - используется, когда с помощью метода POST создается ресурс;
// 202 ACCEPTED - используется, чтобы сообщить, что ресурс принят на сервер (запись обновлена);
// 400 BAD REQUEST - используется, когда со стороны клиента допущена ошибка в вводе;
// 401 UNAUTHORIZED / 403 FORBIDDEN - используются, если для выполнения операции требуется аутентификация пользователя или системы;
// 404 NOT FOUND - используется, если в системе отсутствуют искомые ресурсы;
// 422 - переданы неверные данные для внесения изменений в БД или неверная логика (ошибка триггера и т.п.)
// 500 INTERNAL SERVER ERROR - это никогда не используется просто так - в таком случае произошла ошибка в системе;
// 502 BAD GATEWAY - используется, если сервер получил некорректный ответ от предыдущего сервера.



class APIController extends Controller
{
    // наличие ошибки на этапе конструкции модели
    private $is_err = false;
    // корень расположения файлов
    private $root_file = "";
    // ответ сервера
    private $response;
    // пользователь из-под которого выполняется запрос
    private $user;
    // модель ORM
    private $abp_table;


    public function __construct()
    {
        //         $this->middleware(function ($request, $next) {
        //             $this->user = Auth::user();
        // var_dump("!".$this->user->id);
        //             return $next($request);
        //         });
        // var_dump("->",$this->user);


        if (Route::current()) {
            // таблица - параметр маршрута
            $table = trim(urldecode(Route::current()->parameter('table')));
            // dd($table);
            // создаем экземпляр ответа сервера
            $this->response = new ABPResponse();
            // если передана таблица в машруте
            if (isset($table) && $table != null) {
                // проверим, не отчет ли это
                preg_match('/^report_(.+)$/', $table, $matches);
                if (count($matches) == 2) {
                    $report_table = $matches[1];
                    // создаем экземпляр таблицы АБП
                    $abp_table = new ABPTable($table);
                    if ($abp_table->class_name()) {
                        $orm_model = $abp_table->class_name();
                        $this->abp_table = new $orm_model;
                    } else {
                        return $this->set_err('Отчет [' . $report_table . '] не описан в базе данных', 404);
                    }
                } else {
                    // создаем экземпляр таблицы АБП
                    $abp_table = new ABPTable($table);
                    // если таблица существует
                    if ($abp_table->table_exists()) {
                        $orm_model = $abp_table->class_name();
                        $this->abp_table = new $orm_model;
                        return;
                    } else {
                        return $this->set_err('Таблицы [' . $table . '] не существует в базе данных', 404);
                    }
                }
            } else {
                return $this->set_err('Таблицы [' . $table . '] не существует в базе данных', 404);
            }
        } else {
            return $this->set_err('Ошибка в параметрах маршрута', 400);
        }
    }

    public function setUser()
    {
        if (!$this->user) $this->user = Auth::user();
        return $this->user;
    }

    public function has_err()
    {
        return boolval($this->is_err);
    }

    public function set_err($error, $code = 400)
    {
        $this->is_err = true;
        if ($this->response) $this->response->set_err($error, $code);
    }

    // Вывод данных отчета
    public function report(Request $request, $table)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            // создаем экземпляр модели
            $t = $this->abp_table;

            // устанавливаем текущего пользователя
            $user = $this->setUser();

            if ($user->can('viewAny')) {
                // \DB::connection('db1')->enableQueryLog();
                // начальные данные
                $data = $t;
                // фильтры (поддерживаются только условия AND между фильтрами)
                if (isset($request->filter)) {
                    // замены
                    $replaceSourceArray = ["lt", "gt", "eq", "ne", "ge", "le", "like"];
                    $replaceTargetArray = ["<", ">", "=", "<>", ">=", "<=", "like"];
                    // строка фильтрации
                    $filters_string = urldecode($request->filter);
                    if (preg_match_all("/(\w+)\s+(lt|gt|eq|ne|ge|le|like|in)\s+([\[\w\-\]\,\"]+)(\s+(or|and)\s+)?/iu", $filters_string, $filters, PREG_SET_ORDER)) {
                        foreach ($filters as $filter) {
                            // столбец фильтрации
                            $column = trim($filter[1]);
                            // операнд
                            $exp = str_replace($replaceSourceArray, $replaceTargetArray, strtolower($filter[2]));
                            // значение фильтра
                            $val = json_decode($filter[3]);
                            if (is_array($val)) {
                                if (count($val) == 1) $val = $val[0];
                            } else {
                                $val = $filter[3];
                            }
                            // выполним, если такой скоуп прописан
                            try {
                                $data = $data->$column($val);
                            } catch (Exception  $e) {
                                // nothing is here)
                            }
                        }
                    }
                }
                // $resCount = $data->count();
                // общий метод извлечения данных для отчетов - result
                $data = $data->result();
                // посчитаем общее кол-во записей перед лимитами
                $resCount = count($data);

                // смещение и лимиты
                $limit = 10;
                $offset = 0;
                $noLimit = false;
                if (isset($request->offset) || isset($request->limit)) {
                    if (isset($request->limit)) {
                        $limit = intval(urldecode($request->limit));
                        if ($limit != -1) {
                            $noLimit = false;
                        } else {
                            $noLimit = true;
                        }
                    }
                    // если есть лимиты - смотрим офсет
                    if (!$noLimit) {
                        // если передано смещение
                        if (isset($request->offset)) {
                            $offset = intval(urldecode($request->offset));
                        }
                    }
                }
                if (!$noLimit) {
                    $resData = collect($data)->skip($offset)->take($limit)->values()->all();
                } else {
                    $resData = $data;
                }
                // var_dump(\DB::connection('db1')->getQueryLog());
                return $this->response->set_data($resData, $resCount, 200)->response();;
            } else {
                return $this->response->set_err('Нет прав на просмотр записи', 403)->response();
            }
        }
    }

    // Вывод списка
    public function index(Request $request, $table)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            // создаем экземпляр модели
            $t = $this->abp_table;
            // устанавливаем текущего пользователя
            $user = $this->setUser();

            if ($user->can('viewAny')) {
                // параметры запроса
                $req = $request->input();
                // формат выводимых данных
                // необходимо получать данные
                $getData = true;
                // данные в виде списка (для селектов, например)
                $getList = false;
                // необходимо передавать модель
                $getModel = false;
                // выдать только кол-во данных
                $onlyCount = false;
                // если явно передан формат в запросе
                if (isset($req['odata'])) {
                    switch ($req['odata']) {
                        case 'full': {
                                $getData = true;
                                $getModel = true;
                            }
                            break;
                        case 'model': {
                                $getData = false;
                                $getModel = true;
                            }
                            break;
                        case 'list': {
                                $getData = false;
                                $getList = true;
                                $getModel = false;
                            }
                            break;
                        case 'count': {
                                $getData = false;
                                $getList = false;
                                $getModel = false;
                                $onlyCount = true;
                            }
                            break;
                        case 'data':
                        default: {
                                $getData = true;
                                $getModel = false;
                            }
                            break;
                    }
                }

                // надо получать данные
                if ($getData || $onlyCount) {
                    // получим коллекции на основании фильтров полученного запроса
                    $data = $t->get_table_data($request, $onlyCount);

                    // формируем результат
                    $this->response->set_data($data["data"],  $data["count"], 200);
                }
                // надо получать список для селектов
                if ($getList) {
                    $data = $t->get_list_data($request);
                    // формируем результат
                    $this->response->set_data($data["data"],  $data["count"], 200);
                }
                // надо передавать модель
                if ($getModel) {
                    if (!$getList && !$getData) $this->response->set_data([], $t->count());
                    $this->response->set_model($t->model())->set_extensions($t->get_extensions());
                }
                return $this->response->response($getModel);
            } else {
                return $this->response->set_err('Нет прав на просмотр записи', 403)->response();
            }
        }
    }

    // вывод 1 записи
    public function show(Request $request, $table, $id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id)) {
                // создаем экземпляр модели
                $t = $this->abp_table;
                $model = $t->find($id);
                if ($model) {
                    // устанавливаем текущего пользователя
                    $user = $this->setUser();
                    // права на просмотр списка таблицы
                    if ($user->can('view', $model)) {
                        // параметры запроса
                        $req = $request->input();
                        // формат выводимых данных
                        // необходимо получать данные
                        $getData = true;
                        // данные в виде списка (для селектов, например)
                        $getList = false;
                        // необходимо передавать модель
                        $getModel = false;
                        // если явно передан формат в запросе
                        if (isset($req['odata'])) {
                            switch ($req['odata']) {
                                case 'full': {
                                        $getData = true;
                                        $getModel = true;
                                    }
                                    break;
                                case 'model': {
                                        $getData = false;
                                        $getModel = true;
                                    }
                                    break;
                                case 'data':
                                default: {
                                        $getData = true;
                                        $getModel = false;
                                    }
                                    break;
                            }
                        }
                        // надо получать данные
                        if ($getData) {
                            // получим коллекции на основании фильтров полученного запроса
                            $data = $model->get_table_row($request, $id);
                            // формируем результат
                            $this->response->set_data($data["data"], $data["count"], 200);
                        }
                        // надо передавать модель
                        if ($getModel) {
                            $this->response->set_model($t->model())->set_extensions($t->get_extensions());
                        }
                        return $this->response->response($getModel);
                    } else {
                        return $this->response->set_err('Нет прав на просмотр записи', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не передан параметр id', 400)->response();
            }
        }
    }

    // добавление новой записи
    public function store(Request $request, $table)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            // устанавливаем текущего пользователя
            $user = $this->setUser();
            // создаем экземпляр модели
            $this_model = $this->abp_table;
            // modType
            $mod_type = $request->has('_mod_type') ? $request->_mod_type : 'add';
            if ($user->can('create', $this_model)) {
                if ($mod_type == 'copy') {
                    $copy_options = $request->has('_copy_options') ? json_decode($request->_copy_options, true) : [];
                    if ($request->has('id')) {
                        $this_model = $this_model->find($request->id);
                        try {
                            $res = DB::connection($this_model->connection())->transaction(function () use ($this_model, $request, $copy_options) {
                                // abort(500, json_encode($this_model->toArray()));
                                // $copy_result = $this_model->copy_recursive(false, $copy_options, $request);
                                $copy_result = $this_model->copy_recursive(false, $copy_options);
                                // abort(500, json_encode($copy_result));
                                return $copy_result;
                            });
                        } catch (Exception $e) {
                            return $this->response->exception($e)->response();
                        }
                    } else {
                        return $this->response->set_err('Не передан идентификатор копируемой записи', 400)->response();
                    }
                } else {
                    // формализуем значения для изменения записи
                    $data = $this_model->formalize_data_from_request($request, [], $mod_type);
                    // abort(500, json_encode($data) . json_encode($request->_copy_options));
                    // print_r($data);
                    // dd($data);
                    try {
                        $res = DB::connection($this_model->connection())->transaction(function () use ($this_model, $request, $data, $mod_type) {
                            $save_result = $this_model->save_recursive($request, $data, $mod_type);
                            return $save_result;
                        });
                    } catch (Exception $e) {
                        return $this->response->exception($e)->response();
                    }
                }

                if (isset($res) && !$res["is_error"]) {
                    $res_data = $res["data"];
                    return $this->response->set_data($res_data, 1, 200)->response();
                } else {
                    // ошибка создания записи в таблице
                    return $this->response->set_err("Не удалось создать запись. Ошибки:" . (isset($res) ? implode(",", $res["errors"]) : '#API.Ошибка сохранения данных'), 400)->response();
                }
            } else {
                return $this->response->set_err('Нет прав на создание записи', 403)->response();
            }
        }
    }

    // изменение записи
    public function update(Request $request, $table, $id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id) && isset($table)) {
                // устанавливаем текущего пользователя
                $user = $this->setUser();
                // создаем экземпляр модели
                $t = $this->abp_table;
                $this_model = $t->find($id);
                if ($this_model) {
                    // если есть права на изменение записи (добавление записи - это изменение записи)
                    if ($user->can('update', $this_model)) {
                        // формализуем значения для изменения записи
                        // dd($request->all());
                        $data = $this_model->formalize_data_from_request($request, ['id' => $id], 'edit');
                        try {
                            $res = DB::connection($t->connection())->transaction(function () use ($this_model, $request, $data) {
                                $save_result = $this_model->save_recursive($request, $data, 'edit');
                                return $save_result;
                                // if ($save_result["is_error"]) {
                                //     return false;
                                // } else {
                                //     return $save_result;
                                // }
                            });
                        } catch (Exception $e) {
                            return $this->response->exception($e)->response();
                        }
                        // return $this->response->set_err($res, 400)->response();
                        if (!$res["is_error"]) {
                            $res_data = $res["data"];
                            return $this->response->set_data($res_data, 1, 202)->response();
                        } else {
                            // ошибка создания записи в таблице
                            return $this->response->set_err(implode(",", $res["errors"]), 400)->response();
                        }
                    } else {
                        return $this->response->set_err('Нет прав на редактирование записи', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не переданы параметры', 400)->response();
            }
        }
    }

    // проведение документа
    public function post(Request $request, $table, $id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id) && isset($table)) {
                // устанавливаем текущего пользователя
                $user = $this->setUser();
                // создаем экземпляр модели
                $t = $this->abp_table;
                $this_model = $t->find($id);
                if ($this_model) {
                    // если есть права на изменение записи (добавление записи - это изменение записи)
                    if ($user->can('update', $this_model)) {
                        // все поля из запроса
                        $data = [];
                        // проверим модель и выберем только валидные столбцы из реквеста
                        foreach ($this_model->model() as $field) {
                            // имя поля
                            $field_name = $field["name"];
                            // только поля, которые служат только для проведения документа
                            if (isset($field["post"]) && $field["post"]) {
                                if ($request->has($field_name)) {
                                    $data[$field_name] = $request->$field_name;
                                }
                            }
                        }
                        // если есть данные в массиве после обработки
                        if (count($data) > 0) {
                            // сохраняем запись
                            // \DB::connection('db1')->enableQueryLog();
                            try {
                                $res_transaction = DB::connection($t->connection())->transaction(function () use ($data, $this_model) {
                                    $res = $this_model->fill($data)->save();
                                    if ($res) {
                                        return $this_model;
                                    } else {
                                        return false;
                                    }
                                });
                                if ($res_transaction) {
                                    $res_data = $res_transaction;
                                    $res_data = $res_data->makeVisible(['select_list_title']);
                                    $ext = $t->get_extensions();
                                    if (isset($ext["sub_tables"])) {
                                        $sub_tables_arr = [];
                                        foreach ($ext["sub_tables"] as $key => $sub_table) {
                                            if (isset($sub_table["method"])) {
                                                $sub_tables_arr[] = $sub_table["method"];
                                            }
                                        }
                                        if (count($sub_tables_arr) > 0) {
                                            $res_data = $res_data->where('id', $id)->with($sub_tables_arr)->get()->toArray();
                                        }
                                    }
                                    if (is_array($res_data)) $res_data = last($res_data);
                                    // var_dump(\DB::connection('db1')->getQueryLog());
                                    return $this->response->set_data($res_data, 1, 202)->response();
                                } else {
                                    // ошибка изменения записи в таблице
                                    return $this->response->set_err('Не удалось изменить запись', 400)->response();
                                }
                            } catch (Exception $e) {
                                return $this->response->exception($e)->response();
                            }
                        } else {
                            return $this->response->set_err('Не переданы данные для проведения документа', 400)->response();
                        }
                    } else {
                        return $this->response->set_err('Нет прав проведение документа', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не переданы параметры', 400)->response();
            }
        }
    }

    // удаление записи
    public function destroy($table, $id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id) && isset($table)) {
                // устанавливаем текущего пользователя
                $user = $this->setUser();
                // создаем экземпляр модели
                $t = $this->abp_table;
                $this_model = $t->find($id);
                if ($this_model) {
                    // если есть права на изменение записи (добавление записи - это изменение записи)
                    if ($user->can('delete', $this_model)) {
                        try {
                            $res = DB::connection($t->connection())->transaction(function () use ($this_model) {
                                $res = $this_model->delete_recursive(true);
                                return $res;
                            });
                            if (isset($res) && !$res["is_error"]) {
                                $res_data = $res["data"];
                                return $this->response->set_data($res_data, 1, 200)->response();
                            } else {
                                // ошибка удаления записи в таблице
                                return $this->response->set_err("Не удалось удалить запись. Ошибки:" . (isset($res) ? implode(",", $res["errors"]) : '#API.Ошибка удаления записи'), 400)->response();
                            }
                        } catch (Exception $e) {
                            return $this->response->exception($e)->response();
                        }
                    } else {
                        return $this->response->set_err('Нет прав на удаление записи', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не переданы параметры', 400)->response();
            }
        }
    }

    // выдаем список всех групп для таблицы
    public function get_groups(Request $request, $table)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            // создаем экземпляр модели
            $t = $this->abp_table;
            // устанавливаем текущего пользователя
            $user = $this->setUser();
            // права на просмотр списка таблицы
            if ($user->can('viewAny')) {
                $tags_model = new TableTag();
                $tags = $tags_model->where('table_type', $t->class_name())->select(["tag_id"])->distinct();
                // $data = $tags->get()->each(function (&$model) {
                //     $model->makeHidden(["id"]);
                // });
                $data = $tags->get()->sortBy('tag')->values()->all();
                // dd($tags->get()->toArray()[0]);
                return $this->response->set_data($data, $tags->count(), 200)->response();
            } else {
                return $this->response->set_err('Нет прав на просмотр', 403)->response();
            }
        }
    }

    // добавляем группу и присваиваем ее записи
    public function add_group(Request $request, $table, $id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id) && isset($table)) {
                // устанавливаем текущего пользователя
                $user = $this->setUser();
                // создаем экземпляр модели
                $t = $this->abp_table;
                $data = $t->find($id);
                if ($data) {
                    // результат не определен
                    $res = null;
                    // если есть права на изменение записи (добавление группы - это изменение записи)
                    if ($user->can('update', $data)) {
                        if ($request->has("data")) {
                            $req_data = json_decode($request->input('data'), true);
                            // получим все id групп для записи
                            $beginning_id = $data->groups()->pluck('tag_id')->toArray();
                            // если переданы значения
                            if (count($req_data) > 0) {
                                foreach ($req_data as $group) {
                                    if (!is_string($group)) {
                                        $tag_name = $group['tag'];
                                        $tag_id = $group['tag_id'];
                                        // проверим, есть ли такая группа у записи
                                        if (!$data->has_group_id($tag_id)) {
                                            // добавляем существующий
                                            $res = $data->add_group($tag_id);
                                        } else {
                                            // обрабатывать не надо - такая группа уже есть
                                            $el_id = array_search($tag_id, $beginning_id);
                                            if ($el_id !== false) {
                                                unset($beginning_id[$el_id]);
                                            }
                                        }
                                    } else {
                                        $tag_name = $group;
                                        $res = DB::connection($t->connection())->transaction(function () use ($tag_name, $t, $id, $data) {
                                            $tag = new Tag;
                                            // попытаемся найти тег с таким именем
                                            $tag_id = $tag->where('name', $tag_name)->first();
                                            // если тега не существует - добавляем и получаем его id
                                            if (!$tag_id) {
                                                // добавляем новый тег
                                                $tag->name = $tag_name;
                                                $tag->save();
                                                $tag_id = $tag;
                                            }
                                            // если id есть в итоге
                                            if ($tag_id) {
                                                // добавляем вставленные тег в список
                                                return $data->add_group($tag_id->id);
                                            }
                                        });
                                    }
                                }
                                // теперь удалим те группы, которые не переданы
                                if (count($beginning_id) > 0) {
                                    $res = $data->remove_groups($beginning_id);
                                }
                            } else {
                                // удалим все группы у записи
                                $res = $data->groups()->delete();
                            }

                            if ($res) {
                                // получим обновленный набор групп
                                return $this->response->set_data($data, 1, 201)->response();
                            } else {
                                return $this->response->set_err('Не удалось добавить группу', 400)->response();
                            }
                        } else {
                            return $this->response->set_err("Не передан обязательный параметр", 422)->response();
                        }
                    } else {
                        return $this->response->set_err('Нет прав на добавление файлов', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не переданы параметры', 400)->response();
            }
        }
    }

    // выдаем список всех файлов для добавления в список таблицы
    public function get_file_list(Request $request, $table)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            // создаем экземпляр модели
            $t = $this->abp_table;
            // устанавливаем текущего пользователя
            $user = $this->setUser();
            // права на просмотр списка таблицы
            if ($user->can('viewAny')) {
                $file_list_model = new FileList();
                $file_list = $file_list_model->where('table_type', $t->class_name())->withTrashed()->select(["file_id"])->distinct();
                // $data = $tags->get()->each(function (&$model) {
                //     $model->makeHidden(["id"]);
                // });
                $data = $file_list->with('file')->get()->sortBy('file.name')->values()->all();

                return $this->response->set_data($data, $file_list->count(), 200)->response();
            } else {
                return $this->response->set_err('Нет прав на просмотр', 403)->response();
            }
        }
    }

    // синхронизируем список файлов с переданным в теле запроса
    public function sync_file_list(Request $request, $table, $id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id) && isset($table)) {
                // устанавливаем текущего пользователя
                $user = $this->setUser();
                // создаем экземпляр модели
                $t = $this->abp_table;
                $data = $t->find($id);
                if ($data) {
                    // результат не определен
                    $res = null;
                    // если есть права на изменение записи (добавление группы - это изменение записи)
                    if ($user->can('update', $data)) {
                        if ($request->has("data")) {
                            $req_data = json_decode($request->input('data'), true);
                            // удалим все записи, которых нет в переданном массиве
                            $res = $data->file_list()->whereNotIn('file_id', $req_data)->delete();
                            // получим все id файлов в списке для записи
                            $beginning_files = $data->file_list()->pluck('file_id')->toArray();
                            // файлы для добавления
                            $add_files = array_diff($req_data, $beginning_files);
                            foreach ($add_files as $file_id) {
                                $res = $data->add_list_file($file_id);
                                if (!$res) dd($res);
                            }
                            // результат
                            if ($res !== false) {
                                // получим обновленный список файлов
                                return $this->response->set_data($data->file_list()->get(), 1, 201)->response();
                            } else {
                                return $this->response->set_err('Не удалось обновить список файлов', 400)->response();
                            }
                        } else {
                            return $this->response->set_err("Не передан обязательный параметр", 422)->response();
                        }
                    } else {
                        return $this->response->set_err('Нет прав на добавление файлов', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не переданы параметры', 400)->response();
            }
        }
    }

    // выдаем список файлов для записи
    public function get_files(Request $request, $table, $id, $type = 'image')
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id) && isset($table)) {
                // устанавливаем текущего пользователя
                $user = $this->setUser();
                // создаем экземпляр модели
                $t = $this->abp_table;
                $data = $t->find($id);
                if ($data) {
                    // если есть права на просмотр записи
                    if ($user->can('view', $data)) {
                        // получим файлы
                        switch ($type) {
                            case "document":
                            case "image": {
                                    $files = $data->files($type);
                                }
                                break;
                            case "list": {
                                    $files = $data->file_list();
                                }
                        }

                        // посчитаем файлы
                        $count = $files->count();
                        // результат
                        return $this->response->set_data($files->get(), $count, 200)->response();
                    } else {
                        return $this->response->set_err('Нет прав на получение файлов для записи таблицы', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не переданы параметры', 400)->response();
            }
        }
    }

    // сохраняем файл с привязкой к моделе
    public function store_file(Request $request, $table, $id)
    {

        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            // return $this->response->set_err(intVal($this->has_err()) . ", " . $table . "," . $id, 401)->response();
            // dd($id, $table);
            if (isset($id) && isset($table)) {
                // устанавливаем текущего пользователя
                $user = $this->setUser();
                // создаем экземпляр модели
                $t = $this->abp_table;
                $model = $t->find($id);

                if ($model) {
                    // dd($model);
                    // если есть права на изменение записи (добавление записи - это изменение записи)
                    if ($user->can('update', $model)) {
                        $replaces = [];
                        // загружаем файл на сервер
                        $file_model = new File;
                        // найдем тип файла
                        if ($request->has('file_type_id')) {
                            $request_file_type = $request->file_type_id;
                            $file_type = FileType::where('name', $request_file_type);
                            if ($file_type) {
                                $replaces["file_type_id"] = $file_type->first()->id;
                            }
                        } else {
                            $request_file_type = 'image';
                        }
                        // добавим полиморфные связи (свои для списка файлов и полиморфные для обычной таблицы)
                        if ($request_file_type == 'list') {
                            $replaces = array_merge($replaces, [
                                'table_type' => '',
                                'table_id' => 0
                            ]);
                        } else {
                            $replaces = array_merge($replaces, [
                                'table_type' => $t->class_name(),
                                'table_id' => $id
                            ]);
                        }
                        // dd($replaces);
                        // добавим запись
                        $data = $file_model->formalize_data_from_request($request, $replaces, 'add');

                        // если есть ошибки
                        if ($data["is_error"]) {
                            $err = '';
                            if (count($data["errors"]["require"])) {
                                $err .= "не заполнены обязательные поля: " . implode(',', $data["errors"]["require"]);
                            }
                            if (count($data["errors"]["invalid"])) {
                                if ($err != '') $err .= ", ";
                                $err .= "некорректное значение поля: " . implode(',', $data["errors"]["invalid"]);
                            }
                            return $this->response->set_err($err, 422)->response();
                        } else {
                            // сохраняем файлы на ФС
                            foreach ($data["files"] as $file_name => $file_settings) {
                                $driver = new FileDriver;
                                $driver_name = $driver->find($data["data"]["file_driver_id"]);
                                if ($driver_name) {
                                    // создаем диск
                                    $disk = new ABPStorage($driver_name->name);

                                    $saved_file = $disk->saveFile($request->file($file_name), $file_settings["filename"]);
                                    if ($saved_file) {
                                        $data["data"][$file_name] = $saved_file["filename"];
                                        $data["data"]["uid"] = $saved_file["uid"];
                                        $data["data"]["extension"] = $saved_file["extension"];
                                        $data["data"]["folder"] = $saved_file["folder"];
                                        // сохраняем запись
                                        $res = $file_model->fill($data["data"])->save();
                                        if ($res) {
                                            // если файл сохранился
                                            if ($request_file_type == 'list') {
                                                // если список - нужно добавить только что сохраненный файл в список
                                                $res = $model->add_list_file($file_model->id);
                                            }
                                            return $this->response->set_data($res, 1, 200)->response();
                                        } else {
                                            // удалить файл
                                            $disk->delete_file($data["data"]["uid"], $data["data"][$file_name]);
                                            // ошибка создания записи в таблице
                                            return $this->response->set_err('Не удалось создать запись', 400)->response();
                                        }
                                    } else {
                                        return $this->response->set_err('Не удалось создать файл в файловой системе', 502)->response();
                                    }
                                } else {
                                    return $this->response->set_err('Не удалось обнаружить драйвер файловой системы', 400)->response();
                                }
                            }
                        }
                    } else {
                        return $this->response->set_err('Нет прав на добавление файлов', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не переданы параметры', 400)->response();
            }
        }
    }

    // изменение файла
    public function edit_file(Request $request, $table, $id, $file_id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id) && isset($table)) {
                // устанавливаем текущего пользователя
                $user = $this->setUser();
                // создаем экземпляр модели
                $t = $this->abp_table;
                $data = $t->find($id);
                if ($data) {
                    // если есть права на изменение записи (добавление записи - это изменение записи)
                    if ($user->can('update', $data)) {
                        // загружаем файл на сервер
                        $file_model = File::find($file_id);
                        if ($file_model) {
                            // если только делаем картинку основной
                            if ($request->has('is_main') && count($request->all()) == 2) {
                                $res = $file_model->make_main_image();
                                if ($res) {
                                    return $this->response->set_data($res, 1, 200)->response();
                                } else {
                                    return $this->response->set_err('Неудалось обновить записи', 400)->response();
                                }
                            } else {
                                // изменяем основные параметры файла
                                $data = $file_model->formalize_data_from_request($request, [], 'edit');
                                // если есть ошибки
                                if ($data["is_error"]) {
                                    $err = '';
                                    if (count($data["errors"]["require"])) {
                                        $err .= "не заполнены обязательные поля: " . implode(',', $data["errors"]["require"]);
                                    }
                                    if (count($data["errors"]["invalid"])) {
                                        if ($err != '') $err .= ", ";
                                        $err .= "некорректное значение поля: " . implode(',', $data["errors"]["invalid"]);
                                    }
                                    return $this->response->set_err($err, 422)->response();
                                } else {
                                    // создаем новый файл
                                    $disk = new ABPStorage($file_model->driver);
                                    // обрабатываем файлы, если есть
                                    if (count($data["files"]) > 0) {
                                        foreach ($data["files"] as $file_name => $file_settings) {
                                            // если переданы параметры файла
                                            if ($file_settings) {
                                                // если есть уже файл в поле - удаляем его
                                                if ($file_model->$file_name) {
                                                    // удаляем существующий файлик
                                                    $disk->delete_file($file_model->uid, $file_model->filename);
                                                }
                                                $saved_file = $disk->saveFile($request->file($file_name), $file_settings["filename"]);
                                                if ($saved_file) {
                                                    $data["data"][$file_name] = $saved_file["filename"];
                                                    $data["data"]["uid"] = $saved_file["uid"];
                                                    $data["data"]["extension"] = $saved_file["extension"];
                                                    $data["data"]["folder"] = $saved_file["folder"];
                                                } else {
                                                    return $this->response->set_err('Не удалось создать файл в файловой системе', 502)->response();
                                                }
                                            }
                                        }
                                    } else {
                                        // // проверим, не изменили ли файловую систему хранения файла
                                        // $new_fs_driver = intval($data["data"]["file_driver_id"]);
                                        // if ($file_model->file_driver_id != $new_fs_driver) {
                                        //     $target_fs = FileDriver::find($new_fs_driver)->name;
                                        //     $res_replace = $disk->replace_to($file_model, $target_fs);
                                        //     if ($res_replace) {
                                        //         $data["data"]["filename"] = $res_replace["filename"];
                                        //         $data["data"]["uid"] = $res_replace["uid"];
                                        //         $data["data"]["extension"] = $res_replace["extension"];
                                        //         $data["data"]["folder"] = $res_replace["folder"];
                                        //     } else {
                                        //         return $this->response->set_err('Не удалось перенести файл в другую файловую систему', 502)->response();
                                        //     }
                                        // }
                                    }
                                    // сохраняем запись
                                    $res = $file_model->fill($data["data"])->save();
                                    if ($res) {
                                        return $this->response->set_data($res, 1, 200)->response();
                                    } else {
                                        // удалить файл
                                        $disk->delete_file($data["data"]["uid"], $data["data"][$file_name]);
                                        // ошибка создания записи в таблице
                                        return $this->response->set_err('Не удалось создать запись', 400)->response();
                                    }
                                }
                            }
                        } else {
                            return $this->response->set_err('Файл не найден в БД', 404)->response();
                        }
                    } else {
                        return $this->response->set_err('Нет прав на добавление файлов', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не переданы параметры', 400)->response();
            }
        }
    }

    // удаление файла
    public function delete_file(Request $request, $table, $id, $file_id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id) && isset($table)) {
                // устанавливаем текущего пользователя
                $user = $this->setUser();
                // создаем экземпляр модели
                $t = $this->abp_table;
                $data = $t->find($id);
                if ($data) {
                    // если есть права на изменение записи (удаление картинки - это изменение записи)
                    if ($user->can('update', $data)) {
                        // загружаем файл на сервер
                        $file_model = File::find($file_id);
                        if ($file_model) {
                            $disk = new ABPStorage($file_model->driver);
                            if ($disk->delete_file($file_model->uid, $file_model->filename)) {
                                $res = $file_model->delete();
                                if ($res) {
                                    return $this->response->set_data($res, 1, 200)->response();
                                } else {
                                    return $this->response->set_err('Не удалось удалить запись', 400)->response();
                                }
                            } else {

                                return $this->response->set_err('Не удалось удалить файл', 502)->response();
                            }
                        } else {
                            return $this->response->set_err('Файл не найден в БД', 404)->response();
                        }
                    } else {
                        return $this->response->set_err('Нет прав на добавление файлов', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не переданы параметры', 400)->response();
            }
        }
    }

    // получение списка серийных номеров
    public function get_serials(Request $request, $table, $id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id)) {
                // создаем экземпляр модели
                $t = $this->abp_table;
                $model = $t->find($id);
                if ($model) {
                    // устанавливаем текущего пользователя
                    $user = $this->setUser();
                    // права на просмотр списка таблицы
                    if ($user->can('view', $model)) {
                        // если возможно извлечь серийные номера
                        if (method_exists($model, 'get_serial_numbers')) {
                            $data = $model->get_serial_numbers();
                            return $this->response->set_data($data, count($data), 200)->response();
                        } else {
                            return $this->response->set_err('Таблица не предназначена для хранения серийных номеров', 400)->response();
                        }
                    } else {
                        return $this->response->set_err('Нет прав на просмотр записи', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не передан параметр id', 400)->response();
            }
        }
    }
    // обновление списка серийных номеров (синхронизация)
    public function set_serials(Request $request, $table, $id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id)) {
                // создаем экземпляр модели
                $t = $this->abp_table;
                $model = $t->find($id);
                if ($model) {
                    // устанавливаем текущего пользователя
                    $user = $this->setUser();
                    // права на изменение записи
                    if ($user->can('update', $model)) {
                        // если возможно извлечь серийные номера
                        if (method_exists($model, 'syncSerials')) {
                            if ($request->has('data')) {
                                try {
                                    $res_sync = $model->syncSerials(json_decode($request->data, true));
                                    if ($res_sync["is_error"]) {
                                        return $this->response->set_err($res_sync["errors"], 400)->response();
                                    } else {
                                        $data = $model->get_serial_numbers();
                                        return $this->response->set_data($data, count($data), 201)->response();
                                    }
                                } catch (Exception $e) {
                                    return $this->response->exception($e)->response();
                                }
                            } else {
                                return $this->response->set_err('Не переданы значения', 400)->response();
                            }
                        } else {
                            return $this->response->set_err('Таблица не предназначена для хранения серийных номеров', 400)->response();
                        }
                    } else {
                        return $this->response->set_err('Нет прав на просмотр записи', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не передан параметр id', 400)->response();
            }
        }
    }
    // получения возможных серийников для записи
    public function get_serials_list(Request $request, $table, $id)
    {
        // если ошибка - выдаем ответ
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id)) {
                // создаем экземпляр модели
                $t = $this->abp_table;
                $model = $t->find($id);
                if ($model) {
                    // устанавливаем текущего пользователя
                    $user = $this->setUser();
                    // права на просмотр списка таблицы
                    if ($user->can('view', $model)) {
                        // если возможно извлечь серийные номера
                        if (method_exists($model, 'get_available_sn')) {
                            $data = $model->get_available_sn();
                            return $this->response->set_data($data, count($data), 200)->response();
                        } else {
                            return $this->response->set_err('Таблица не поддерживается для работы с серийными номерами', 400)->response();
                        }
                    } else {
                        return $this->response->set_err('Нет прав на просмотр записи', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=[' . $id . '] в таблице [' . $table . '] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не передан параметр id', 400)->response();
            }
        }
    }
}