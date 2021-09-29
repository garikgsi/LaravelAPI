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



// ЯЗЫК ЗАПРОСОВ К API
//
// GET /api/v1/table_name - вывод 1-й страницы данных (по умолчанию сортируется по столбцу name, 10 строк на странице)
// GET /api/vi/table_name/N - вывод всех полуй записи таблицы table_name с id=N
// GET /api/v1/table_name?odata=[full|data|model|list] - формат вывода данных data - только данные, model - только модель столбцов таблицы,
//                                                      full - данные и модель, list - список в виде массива ['id'=>'N', 'title'=>'template']
//                                                      (формат вывода list определяется методом listFormat класса ABPTable, в случае неверно
//                                                      указанного шаблона будем передавать таблицу целиком)
//     Параметры фильтрации в запросе GET:
//         &fields=fieldName1,fieldName2,...,fieldNameN - вывод только перечисленных столбцов таблицы
//         &order=id,[desc|asc] - сортировка выдачи: поле,порядок сортировки
//         &filter=fieldName1[lt|gt|eq|ne|ge|le|like]filterValue1 [or|and] fieldName1[lt|gt|eq|ne|ge|le|like]filterValue1 -
//             доступные операнды:
//                 lt => меньше
//                 gt => больше
//                 eq => равно
//                 ne => не равно
//                 ge => больше или равно
//                 le => меньше или равно
//                 like => like
//             !! к операнду like значение обрамляется %% с обеих сторон
//             доступные условия:
//                 or => ИЛИ
//                 and => И
//             !!невозможно указывать условия, обрамленные в скобки
//         &search=text - поиск по всем возможным полям
//         &offset - смещение относительно 0-го элемента выдачи, отсортированного согласно правилам сортировки (только совместно с limit)
//         &limit - количество выдаваемых значений выдачи (-1 для отсутствия лимитов)

// POST /api/v1/table_name - добавление записи в таблицу table_name. Ответ при успехе - 201 и вставленная запись в объекте data, в случае ошибки - 500
// PUT|PATCH /api/v1/table_name/N - изменение записи с id=N в таблице table_name. В ответе count сервер вернет кол-во измененных записей
// DELETE /api/v1/table_name/N - удаление записи с id=N в таблице table_name. В ответе count сервер вернет кол-во удаленных записей или true

// Формат ответа сервера:
// {
//     "is_error": false,           /* булево поле наличия ошибки */
//     "error": "",                 /* текстовое описание ошибки */
//     "count": 4,                  /** количество записей в таблице соответствующих запросу */
//     "data": [],                  /** массив объектов данных */
//     "time_request": "0.596 sec", /** справочно - время выборки данных */
//     "model": []                  /** модель структуры таблицы, если передан параметр odata */
//     "extensions": []             /** расширения таблицы (использования групп, картинок и т.п.) - передается, если запрошена модель */
// }

// Коды ответов сервера:

// 200 OK - самый часто используемый код, свидетельствующий об успехе операции;
// 201 CREATED - используется, когда с помощью метода POST создается ресурс;
// 202 ACCEPTED - используется, чтобы сообщить, что ресурс принят на сервер;
// 400 BAD REQUEST - используется, когда со стороны клиента допущена ошибка в вводе;
// 401 UNAUTHORIZED / 403 FORBIDDEN - используются, если для выполнения операции требуется аутентификация пользователя или системы;
// 404 NOT FOUND - используется, если в системе отсутствуют искомые ресурсы;
// 500 INTERNAL SERVER ERROR - это никогда не используется просто так - в таком случае произошла ошибка в системе;
// 502 BAD GATEWAY - используется, если сервер получил некорректный ответ от предыдущего сервера.



class table extends Controller
{
    private $tclass;
    private $table;
    private $is_err = false;
    private $table_models;
    private $root_file = "";
    private $response;
    private $model = null;
    private $user;
    private $abp_table;


    public function __construct() {

        $table = trim(urldecode(Route::current()->parameter('table')));
        $this->abp_table = new ABPTable($table);

        $this->table_models = new ABPTable;
        $this->response = new ABPResponse();

        if (Route::current()) {
            $table = trim(urldecode(Route::current()->parameter('table')));
            // set database name
            \Config::set('database.connections.db1.database','db1');
            // \Config::set('database.connections.db1.database','u0184406_api_data');


            if (isset($table) && $table!=null) {
                $this->table = $table;
                // $tablesList = new Variables;
                // $tables = $tablesList->get();
                $tables = $this->table_models->get_tables();

                if (Schema::connection('db1')->hasTable($this->table)) {
                    if (isset($tables[$this->table])) {
                        // $className = 'App\\'.$tables[$this->table]['Class'];
                        $this->tclass = $this->table_models->new_table($this->table);
                        $this->model = $this->tclass->model();
                        $this->title = $tables[$table]['Title'];
                    } else {
                        return $this->response->set_err('Не найдено описание таблицы'.$table, 400)->response();
                    }
                } else {
                    return $this->response->set_err('Таблицы ['.$table.'] не существует в базе данных', 404)->response();
                }
            } else {
                return $this->response->set_err('Таблицы ['.$table.'] не существует в базе данных', 404)->response();
            }
        } else {
            return $this->response->set_err('Ошибка в параметрах маршрута', 400)->response();
        }
    }

    public function setUser() {
        $this->user = Auth::user();
    }

    public function has_err() {
        return $this->is_err;
    }

    /**
     * Вывод списка
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $table)
    {
        // dd($this->abp_table->first());

        $this->setUser();

        if ($this->has_err()) {
            return $this->response->response();
        } else {
            // параметры запроса
            $req = $request->input();

            // необходимо получать данные
            $getData = true;
            // данные в виде списка
            $getList = false;
            // необходимо передавать модель
            $getModel = false;
            if (isset($req['odata'])) {
                switch ($req['odata']) {
                    case 'full': {
                        $getData = true;
                        $getModel = true;
                    } break;
                    case 'model': {
                        $getData = false;
                        $getModel = true;
                    } break;
                    case 'list': {
                        $getData = true;
                        $getList = true;
                        $getModel = false;
                    }break;
                    case 'data': default: {
                        $getData = true;
                        $getModel = false;
                    } break;
                }
            }

            // если необходимо получать данные
            if ($getData) {
                // TODO : переписать выборку на Eloquent ORM https://laravel.ru/docs/v5/eloquent
                // настройки лимитов
                $defLimit = 10;
                // настройки сортировки
                $defaultOrder = ['name','asc'];


                // базовый экземляр таблицы
                // $data_table = DB::connection('db1')->table($table);
                $data_table = $this->tclass;
                $this_table = $data_table;

                // форматировать список
                $has_format_out = false;
                // если выводим список
                if ($getList) {
                    // список столбцов
                    $cols = array();
                    // формат вывода списка
                    $format = $data_table->listFormat();
                    // проверим правильность шаблона
                    // в таблице есть все указанные в шаблоне столбцы
                    $valid_all_fields = true;
                    foreach($format as $key=>$value) {
                        $col_template = trim($value);
                        $has_template = preg_match_all("/{{([\w\_]+)}}/i",$col_template,$match_fields);
                        // есть шаблон
                        if ($has_template) {
                            foreach($match_fields[1] as $col) {
                                $col = trim($col);
                                if ($this_table->has_column($col)) {
                                    $cols[] = $col;
                                } else {
                                    $valid_all_fields = false;
                                    break;
                                }
                            }
                        } else {
                            // рассматриваем как столбец
                            if ($this_table->has_column($col_template)) {
                                $cols[] = $col_template;
                                $format[$key] = "{{".$col_template."}}";
                            } else {
                                $valid_all_fields = false;
                            }
                        }
                    }
                    if ($valid_all_fields && count($cols)>0) {
                        $has_format_out = true;
                        $data_table = $data_table->select($cols);
                    }

                } else {
                    // если переданы столбцы
                        if (isset($req['fields'])) {
                        $cols = array();
                        $fields = explode(',',urldecode($req['fields']));
                        foreach($fields as $f) {
                            if ($this_table->has_column($f)) {
                                $cols[] = $f;
                            }
                        }
                        if (count($cols)>0) $data_table = $data_table->select($cols);
                        unset($req['fields']);
                    }
                }

                // если передана сортировка
                //order=name,desc
                if (isset($req['order'])) {
                    $req["order"] = urldecode($req["order"]);
                    $orders = explode(',',$req['order']);
// dd($orders);
                    $order_field = trim($orders[0]);
                    if (count($orders)>0 && $this_table->has_column($order_field)) {
                        if (count($orders)==1) {
                            $data_table = $data_table->orderBy($order_field,$defaultOrder[1]);
                        } else {
                            $data_table = $data_table->orderBy($order_field,$orders[1]);
                        }
                    } else {
                        $this->response->set_err("В таблице нет [".$table."] столбца [".$order_field."] для сортировки");
                        $data_table = $data_table->orderBy($defaultOrder[0],$defaultOrder[1]);
                    }

                    unset($req['order']);
                } else {
                    $data_table = $data_table->orderBy($defaultOrder[0],$defaultOrder[1]);
                }

// $data_table->getData($request);

                // блок фильтров
                if (isset($req['filter'])) {
                    $replaceSourceArray = ["lt","gt","eq","ne","ge","le","like"];
                    $replaceTargetArray = ["<",">","=","<>",">=","<=","like"];
                    $req["filter"] = urldecode($req["filter"]);
                    if (preg_match_all("/(\w+)\s+(lt|gt|eq|ne|ge|le|like|in)\s+([\[\w\]\,\"]+)(\s+(or|and)\s+)?/iu", $req['filter'],$filterResults,PREG_SET_ORDER)) {
                        $nextExp = "and";
                        foreach($filterResults as $filterResult) {
                            $column = trim($filterResult[1]);
                            if ($this_table->has_column($column)) {
                                $exp = str_replace($replaceSourceArray,$replaceTargetArray, strtolower($filterResult[2]));
                                switch ($exp) {
                                    case 'like': {
                                        $colValue = '%'.$filterResult[3].'%';
                                    } break;
                                    case 'in': {
                                        // проверим, не массив ли передан в формате JSON
                                        $colValue = json_decode($filterResult[3]);

                                        if (!is_array($colValue)) {
                                            $colValue = array($colValue);
                                        }
                                    } break;
                                    default: {
                                        $colValue = $filterResult[3];
                                    }
                                }
                                if ($nextExp=='and') {
                                    if ($exp=='in') {
                                        $data_table = $data_table->whereIn($column,$colValue);
                                    } else {
                                        $data_table = $data_table->where($column,$exp,$colValue);
                                    }
                                } else {
                                    if ($exp=='in') {
                                        $data_table = $data_table->orWhereIn($column,$colValue);
                                    } else {
                                        $data_table = $data_table->orWhere($column,$exp,$colValue);
                                    }
                                }
                                if (isset($filterResult[5])) $nextExp = strtolower($filterResult[5]);
                            }
                        }
                    }
                }

                //  блок поиска
                // if (isset($req['search'])) {
                //     $search = urldecode(trim($req['search']));
                //     $model = $data_table->model();
                //     $data_table = $data_table->where(function ($query) use ($model, $search) {
                //         foreach($model as $field) {
                //             if (isset($cols) && in_array($field["name"], $cols) || !isset($cols)) {
                //                 switch ($field["type"]) {
                //                     case "integer": {
                //                         $query->orWhere($field["name"], '=', $search);
                //                     } break;
                //                     case "string": {
                //                         $query->orWhere($field["name"], 'like', '%'.$search.'%');
                //                     } break;
                //                 }
                //             }
                //         }
                //     });
                // }

                // перед лимитами посчитаем кол-во записей выдачи
                $count = $data_table->count();

                // если передано смещение и лимит
                if (isset($req['limit'])) {
                    if (isset($req['offset'])) {
                        $req["limit"] = urldecode($req["limit"]);
                        $req["offset"] = urldecode($req["offset"]);
                        if ($req["limit"]!=-1) {
                            $data_table = $data_table->limit($req['limit']);
                            $data_table = $data_table->offset($req['offset']);
                            // $data_table->take($req['limit']);
                            // $data_table->skip($req['offset']);
                        }
                    } else {
                        if ($req['limit']!=-1) {
                            $data_table = $data_table->limit($defLimit);
                        }
                    }
                } else {
                    $data_table = $data_table->limit($defLimit);
                    // $data_table->take($defLimit);
                }

                // если список с форматом данных - отформатируем их
                if ($getList && $has_format_out) {
                    // отформатированные данные
                    $res = $data_table->get()->toArray();
                    $data = [];
                    // уже собран массив с шаблонами замен
                    $has_replace_from = false;
                    //  на что заменяем
                    $replace_from = [];
                    // по каждой строке подготовим вывод
                    foreach ($res as $row) {
                        $r = [];
                        $replace_to = [];
                        // замены по кол-ву столбцов шаблона
                        foreach($cols as $col) {
                            // массив шаблонов замен {{col}} - только 1я итерация
                            if (!$has_replace_from) $replace_from[] = "{{".$col."}}";
                            // массив замен - для каждой строки
                            $replace_to[] = $row[$col];
                        }
                        // после 1й итерации шаблоны не формируем
                        $has_replace_from = true;
                        // заменяем каждую пару ключ-значение шаблона
                        foreach ($format as $key=>$value) {
                            if ($key=="id") {
                                $r[$key] = intval(str_replace($replace_from, $replace_to, $value));
                            } else {
                                $r[$key] = str_replace($replace_from, $replace_to, $value);
                            }
                        }
                        $data[] = $r;
                    }
                } else {
                    // получим данные для таблицы
                                //      для вывода значений селектов
                                // class Book extends Model {
                                //     public function author()
                                //     {
                                //         return $this->belongsTo('App\Author');
                                //     }
                                // foreach (Book::with('author')->get() as $book)
                                // {
                                //     echo $book->author->name;
                                // }
                                // Разумное использование активной загрузки поможет сильно повысить производительность вашего приложения.
                                // Конечно, вы можете загрузить несколько отношений одновременно:
                                // $books = Book::with('author', 'publisher')->get();
                    // отформатируем данные таблицы
                    if ($model = $this_table->model()) {
                        // оставим в моделе только те столбцы, которые требуются для вывода
                        if (isset($cols)) {
                            $this_model = [];
                            foreach($cols as $col) {
                                foreach($model as $field) {
                                    if ($field["name"]==$col) {
                                        $this_model[] = $field;
                                        break;
                                    }
                                }
                            }
                            $model = $this_model;
                        }

                        $data = [];
                        foreach($data_table->get() as $table_row) {
// dd($table_row->id);
                            // $data_row = [];
                            // foreach($model as $field) {
                            //     $fname = $field["name"];
                            //     $ftype = $field["type"];
                            //     if ($table_row->$fname) {
                            //         switch ($ftype) {
                            //             case "image": {
                            //                 if (Storage::disk('local')->exists("thumbs/".$table_row->$fname)) $data_row[$fname] = asset("storage/thumbs/".$table_row->$fname);
                            //             } break;
                            //             case "select": {
                            //                 if (isset($field["table"])) {
                            //                     $select_table = $field["table"];
                            //                     if ($table_row->$select_table && $table_row->$select_table->name) {
                            //                         $data_row[$fname] = $table_row->$select_table->name;
                            //                     } else {
                            //                         $data_row[$fname] = $table_row->$fname;
                            //                     }
                            //                 }
                            //             } break;
                            //             default: {
                            //                 $data_row[$fname] = $table_row->$fname;
                            //             }
                            //         }
                            //     } else {
                            //         // echo "свойства ".$fname." не существует";
                            //     }
                            // }
                            // $data[] = $data_row;
                            $data[] = $this->prepare_row($table_row);
                        }
                    } else {
                        $data = $data_table->get()->toArray();
                    }
                }

                // формируем результат
                $this->response->set_data($data, $count, 200);
            }
            if ($getModel) {
                $model = $this->tclass->model();
                $this->response->set_model($model);
            }
            return $this->response->response($getModel);
        }

    }
    /**
     * Добавление новой записи
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $table)
    {
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            $this->setUser();

            // dd($request);
            // текущий пользователь
            $user = $this->user->id;
            // текущая дата
            $now = Carbon::now('utc')->toDateTimeString();
            // ошибки
            $err = [];
            // нормализованные данные
            $norm_data = [];
            // тело запроса
            $r = $request->all();
// dd($r);
            // экземпляр таблицы
            $data_table = $this->tclass;
            // получим модель
            $model = $data_table->model();

// dd($model);
            // проверим соответствие типам значения и обязательные поля
            foreach($model as $field) {
                // есть ошибка в данных
                $field_err = null;
                // значение поля
                $field_val = null;
                // файлы (для удаления, если вдруг запись не вставится)
                $files = [];
                // дополнительная обработка файлов
                switch($field["type"]) {
                    case "image": {
                        if ($request->file($field["name"])) {
                            // $field_val = $request->file($field["name"])->store($this->root_file.'/','public');
                            $extension = $request->file($field["name"])->extension();
                            $field_val = (string) Str::uuid().".".$extension;
                            // $files[$field["name"]] = $field_val;
                            $files[$field["name"]] = ["type"=>$field["type"], "file_name" => $field_val, "title"=>$field["title"], "require"=>(isset($field["require"]) && $field["require"]==true)?true:false];
                        }
                    } break;
                    case "phone": {
                        // +7(552)555-54-54
                        if (isset($r[$field["name"]])) {
                            $field_val = preg_replace("/[^0-9]/", '', $r[$field["name"]]);
                        }
                    } break;
                }
                // поля, которые имеют свой алгоритм обработки
                switch ($field["name"]) {
                    case "id": {
                        // нет вывода
                    } break;
                    case "uuid": {
                        // нет вывода
                    } break;
                    case "created_by": {
                        $field_val = $user;
                    } break;
                    case "created_at": {
                        // $field_val = $now;
                    } break;
                    case "updated_by": {
                        // нет вывода
                    } break;
                    case "updated_at": {
                        // нет вывода
                    } break;
                    case "deleted_by": {
                        // нет вывода
                    } break;
                    case "is_protected": {
                        $field_val = 0;
                    } break;
                    case "sync_1c_at": {
                        // нет вывода
                    } break;
                    case "deleted_at": {
                        // нет вывода
                    } break;
                    //  все остальные
                    default: {
                        //  проверим, вычислено ли значение
                        if (!$field_val) {
                            // если поле - обязательное
                            if ($field["require"]) {
                                    // проерим, передано ли значение
                                    if (isset($r[$field["name"]])) {
                                        // проверим, соответствует ли значение типу
                                        if ($norm_val = ABPField::checkType($field["type"],$r[$field["name"]])) {
                                            $field_val = $norm_val;
                                        } else {
                                            $field_err = "Значение поля ".$field["title"]." не соответствует типу";
                                        }
                                    } else {
                                        // если есть дефолтное значение заменяем
                                        if (isset($field["default"])) {
                                            $field_val = $field["default"];
                                        } else {
                                            $field_err = "Не передано значение для обязательного поля ".$field["title"];
                                        }
                                    }
                            } else {
                                if (isset($r[$field["name"]])) {
                                    // проверим, соответствует ли значение типу
                                    if ($norm_val = ABPField::checkType($field["type"],$r[$field["name"]])) {
                                        $field_val = $norm_val;
                                    }
                                }
                            }
                        }
                    }
                }

                // обрабатываем ошибки
                if ($field_err) {
                    // добавляем в массив ошибок
                    $err[] = $field_err;
                } else {
                    // если есть значение - внесем его в массив нормализованных данных
                    // обязательность поля проверяется на этапе формирования ошибки $field_err
                    // поэтому ветка else здесь лишняя
                    if ($field_val) {
                        $norm_data[$field["name"]] = $field_val;
                    }
                }
            }

// dd($norm_data);
            // если есть ошибки - выдаем 422
            if ($err) {
                return $this->response->set_err(implode(", ",$err), 422)->response();
            } else {
                // если есть файлы - запишем их в ФС
                if (isset($files)) {
                    foreach($files as $file_field=>$file) {
                        if (!$request->file($file_field)->storeAs($this->root_file.'/',$file["file_name"],'public')) {
                            if ($file["require"]) {
                                // выдаем 422
                                return $this->response->set_err(["Файл ".$file["title"]." не удалось сохранить"], 422)->response();
                            }
                            unset($norm_data[$file_field]);
                        } else {
                            // если это изображение - сохраним тамб
                            if ($file["type"]=="image") {
                                $img = \Image::make(Storage::disk('public')->path($file["file_name"]));
                                $img->resize(300, null, function ($constraint) {
                                    $constraint->aspectRatio();
                                });
                                $img->save(Storage::disk('public')->path('')."/thumbs/".$file["file_name"],50);
                            }
                        }
                    }
                }

                // заполняем запись нормализованными значениями и сохраняем
// dd($data_table);
                // если изменять существующую по переданным параметрам
                // $data_table = $data_table->firstOrNew($norm_data);
                // заполняем модель данными и получаем ее
                $res = $data_table->fill($norm_data)->save();

// dd($data_table->toArray(), $norm_data);

                if ($res) {
                    return $this->response->set_data($this->prepare_row($data_table), 1, 201)->response();
                } else {
                    // удалим файлы, если они были
                    if (isset($files)) {
                        foreach($files as $file_field=>$file) {
                            $this->delete_file($file["file_name"], $file["type"]);
                        }
                    }

                    return $this->response->set_err('Не удалось вставить запись', 500)->response();
                }
            }

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($table,$id)
    {
        $this->setUser();
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            if (isset($id)) {
                $data = $this->tclass->find($id);

                if ($data) {
                    if ($this->user->can('view', $data)) {
                        $data = $this->prepare_row($data);
                        return $this->response->set_data($data,1, 200)->response();
                    } else {
                        return $this->response->set_err('Нет прав на просмотр записи', 403)->response();
                    }
                } else {
                    return $this->response->set_err('Запись с id=['.$id.'] в таблице ['.$table.'] не найдена', 404)->response();
                }
            } else {
                return $this->response->set_err('Не передан параметр id', 400)->response();
            }
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request,$table, $id)
    public function update(Request $request,$table, $id)
    {
        $this->setUser();
        if (isset($id)) {
            // получим обновляемую запись модели
            $record = $this->tclass->find($id);
            // если запись найдена
            if ($record) {
                // текущий пользователь
                $user = $this->user->id;
                // ошибки
                $err = [];
                // нормализованные данные
                $norm_data = [];
                // тело запроса
                $r = $request->all();
    // dd($r);
                // экземпляр таблицы
                $data_table = $this->tclass;
                // получим модель
                $model = $data_table->model();

    // dd($model);
                // проверим соответствие типам значения и обязательные поля
                foreach($model as $field) {
                    // есть ошибка в данных
                    $field_err = null;
                    // значение поля
                    $field_val = null;
                    // файлы (для удаления, если вдруг запись не вставится)
                    $files = [];
                    // файлы, которые может быть были на момент создания, но удалены при изменении записи
                    $empty_files = [];
                    // дополнительная обработка файлов
                    switch($field["type"]) {
                        case "image": {
                            $field_name = $field["name"];
                            if ($request->file($field_name)) {
                                $extension = $request->file($field["name"])->extension();
                                $field_val = (string) Str::uuid().".".$extension;
                                $files[$field_name] = ["type"=>$field["type"], "file_name" => $field_val, "title"=>$field["title"], "require"=>(isset($field["require"]) && $field["require"]==true)?true:false];
                            } else {
                                if (isset($r[$field_name]) && asset("storage/thumbs/".$record->$field_name) != $r[$field_name]) {
                                    $empty_files[$field_name] = ["type"=>$field["type"], "title"=>$field["title"], "file_name" => $record->$field_name];
                                }
                            }
    // dd($field_val, $model);
                        } break;
                        case "phone": {
                            // +7(552)555-54-54
                            if (isset($r[$field["name"]])) {
                                $field_val = preg_replace("/[^0-9]/", '', $r[$field["name"]]);
                            }
                        } break;
                    }
                    // поля, которые имеют свой алгоритм обработки
                    switch ($field["name"]) {
                        case "id": {
                            // нет вывода
                        } break;
                        case "uuid": {
                            // нет вывода
                        } break;
                        case "created_by": {
                            // $field_val = $user;
                        } break;
                        case "created_at": {
                            // $field_val = $now;
                        } break;
                        case "updated_by": {
                            $field_val = $user;
                        } break;
                        case "updated_at": {
                            // нет вывода
                        } break;
                        case "deleted_by": {
                            // нет вывода
                        } break;
                        case "is_protected": {
                            $field_val = 0;
                        } break;
                        case "sync_1c_at": {
                            // нет вывода
                        } break;
                        case "deleted_at": {
                            // нет вывода
                        } break;
                        //  все остальные
                        default: {
                            //  проверим, вычислено ли значение
                            if (!$field_val && isset($r[$field["name"]])) {
                                if ($norm_val = ABPField::checkType($field["type"],$r[$field["name"]])) {
                                    $field_val = $norm_val;
                                } else {
                                    $field_err = "Значение поля ".$field["title"]." не соответствует типу";
                                }
                            }
                        }
                    }

                    // обрабатываем ошибки
                    if ($field_err) {
                        // добавляем в массив ошибок
                        $err[] = $field_err;
                    } else {
                        // если есть значение - внесем его в массив нормализованных данных
                        // обязательность поля проверяется на этапе формирования ошибки $field_err
                        // поэтому ветка else здесь лишняя
                        if ($field_val) {
                            $norm_data[$field["name"]] = $field_val;
                        }
                    }
                }

    // dd($norm_data);
                // если есть ошибки - выдаем 422
                if ($err) {
                    return $this->response->set_err(implode(", ",$err), 422)->response();
                } else {
                    // если есть файлы - запишем их в ФС
                    if (isset($files)) {
                        foreach($files as $file_field=>$file) {
                            // если не удалось сохранить новый файл - уберем его из списка
                            if (!$request->file($file_field)->storeAs($this->root_file.'/',$file["file_name"],'public')) {
                                // удалим из запроса файл
                                unset($norm_data[$file_field]);
                                // если поле обязательное - выдаем ошибку
                                if ($file["require"]) {
                                    return $this->response->set_err(["Файл ".$file["title"]." не удалось сохранить"], 422)->response();
                                }
                            } else {
                                // если это изображение - сохраним тамб
                                if ($file["type"]=="image") {
                                    $img = \Image::make(Storage::disk('public')->path($file["file_name"]));
                                    $img->resize(300, null, function ($constraint) {
                                        $constraint->aspectRatio();
                                    });
                                    $img->save(Storage::disk('public')->path('')."/thumbs/".$file["file_name"],50);
                                }
                                // если есть файл в текущей записи - удалим старый
                                if ($record->$file_field) {
                                    $this->delete_file($record->$file_field, $file["type"]);
                                }
                            }
                        }
                    }
                    // сохраняем, если есть что
                    if (count($norm_data)>0) {
                        $data = $record->update($norm_data);
                        // чистим файлы, которые больше не нужны (ссылки удалены)
                        if (count($empty_files)>0) {
                            foreach($empty_files as $file) {
                                $this->delete_file($file["file_name"], $file["type"]);
                            }
                        }
                        return $this->response->set_data($this->prepare_row($record), 1, 200)->response();
                    } else {
                        $data = 0;
                        return $this->response->set_err('Нет данных для обновления', 200, $data)->response();
                    }
                }
            } else {
                $data = 0;
                return $this->response->set_err('Нет записей для обновления', 200, $data)->response();
            }
        } else {
            return $this->response->set_err('Не передан параметр id', 400)->response();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($table,$id)
    {
        if ($this->has_err()) {
            return $this->response->response();
        } else {
            $this->setUser();
            if (isset($id)) {
                $record = $this->tclass->find($id);
                if ($record) {
                    $data = $record->delete();
                    return $this->response->set_data([], 1, 200)->response();
                } else {
                    $data = 0;
                    return $this->response->set_err('Нет записей для удаления', 200)->response();
                }
            } else {
                return $this->response->set_err('Не передан параметр id', 400)->response();
            }
        }
    }

    // удаление файла в таблицах АБП
    public function delete_file($file_name, $type) {
        if (Storage::has($file_name)) {
            Storage::delete($file_name);
        }

        if ($type=="image") {
            if (Storage::has("/thumbs/".$file_name)) {
                Storage::delete("/thumbs/".$file_name);
            }
        }
    }

    // подготовка строки таблицы для вывода (форматирование)
    public function prepare_row($data) {
        if (isset($this->model)) {
            $model = $this->model;
        } else {
            $model = $this->tclass->model();
        }
        $data_row = [];
        if ($model) {
            foreach($model as $field) {
                $fname = $field["name"];
                $ftype = $field["type"];
                if ($data->$fname) {
                    switch ($ftype) {
                        case "image": {
                            if (Storage::disk('local')->exists("thumbs/".$data->$fname)) {
                                $data_row[$fname] = asset("storage/thumbs/".$data->$fname);
                            } else {
                                // dd('файла '."thumbs/".$data->$fname.' не существует');
                            }
                        } break;
                        case "select": {
                            if (isset($field["table"])) {
                                $select_table = $field["table"];
                                if (property_exists($data,$select_table) && property_exists($data->$select_table,'name')) {
                                    $data_row[$fname] = $data->$select_table->name;
                                } else {
                                    $data_row[$fname] = $data->$fname;
                                }
                            }
                        } break;
                        default: {
                            $data_row[$fname] = $data->$fname;
                        }
                    }
                } else {
                    // echo "свойства ".$fname." не существует";
                }
            }
        }
        return $data_row;
    }

}
