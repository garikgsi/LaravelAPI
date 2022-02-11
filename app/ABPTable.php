<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ABPSoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Common\API1C;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Common\ABPCache;
use App\Common\ABPField;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\FileType;
use App\Common\ABPStorage;
use Illuminate\Database\QueryException;
use Exception;






use App\Nomenklatura;
use Hamcrest\Arrays\IsArray;

set_time_limit(0);

class ABPTable extends Model
{
    use ABPSoftDeletes;

    // protected static $user_id = null;
    // модель
    private $t;
    // текущая коллекция
    private $data = null;
    // кол-во экземпляров в коллекции
    private $count = 0;
    // имя соединения = базы данных
    protected $connection = 'db1';
    // мягкое удаление
    protected $softDelete = true;
    // имя таблицы
    protected $table;
    // есть группы
    private $has_folders = false;
    // есть файлы
    private $has_files = false;
    // есть файлы с выбором из списка
    private $has_file_list = false;
    // есть изображения
    private $has_images = false;
    // есть группы
    private $has_groups = false;
    // есть группы в 1С
    private $has_folders_1c = false;
    // добавить серийные номера
    private $has_series = false;
    // перемещать серийные номера
    private $has_sub_series = false;
    // имя таблицы в 1С
    private $tableName1C;
    // тип таблицы
    private $table_type = "catalog"; // document | catalog | sub_table | register | report
    // сортировка по умолчанию
    private $default_order = ['name', 'asc'];
    // подчиненные таблицы
    private $sub_tables = [];
    // это подчиненная таблица
    private $is_sub_table = false;
    // иконка таблицы
    private $icon = null;
    // мягкое удаление
    // protected $dates = ['deleted_at'];
    // метки создания/обновления
    public $timestamps = true;
    // заносимые поля
    // protected $fillable = ['uuid','name','comment','created_at','created_by','updated_at','updated_by',
    //     'deleted_by','deleted_at','is_protected','sync_1c_at'
    // ];
    protected $guarded = [];
    // виртуальные столбцы
    protected $appends = ['select_list_title', 'permissions'];
    // скрытые столбцы
    // protected $hidden = ['select_list_title','files', 'main_image','images','file_list'];
    protected $hidden = ['select_list_title', 'files', 'images'];
    // список полей в выводимых файлах
    private $files_fields = ["id", "name", "comment", "file_driver_id", "file_type_id", "filename", "uid", "extension", "is_main"];
    // модель столбцов
    private $model = [
        /*
            max = Поле должно иметь совпадающий с value размер. Для строк это обозначает длину, для чисел — число, для массивов — число элементов массива, для файлов — размер в килобайтах.
            index = ai (автоинкремент) | index (индекс) | unique (уникально для таблицы)
            name = имя столбца в таблице БД
            type = тип поля (дб согласован с компонентом ABPField.vue)
            title = рускоязычное обозначение поля / столбца таблицы
            require = true | false - обязательное поле
            name_1c = имя столбца в таблице 1С
            table = имя таблицы БД для выбора значений (только для type=select)
            default = значение по умолчанию
            show_in_table - true | false - показывать в таблице
            out_index - порядок вывода
        */
        ["name" => "id", "type" => "integer", "title" => "ID", "require" => true, "index" => "ai", "show_in_table" => false],
        ["name" => "uuid", "name_1c" => "Ref_Key", "type" => "uuid", "title" => "uuid", "require" => false, "index" => "index", "show_in_table" => false],
        ["name" => "created_by", "type" => "select", "table" => "sotrudniki", "title" => "Создатель", "require" => false, "index" => "index", "default" => 1, "show_in_table" => false],
        ["name" => "created_at", "type" => "datetime", "title" => "Время создания", "require" => false, "index" => "index", "show_in_table" => false],
        ["name" => "updated_by", "type" => "select", "table" => "sotrudniki", "title" => "Последнее изменение", "require" => false, "index" => "index", "default" => 1, "show_in_table" => false],
        ["name" => "updated_at", "type" => "datetime", "title" => "Время последнего изменения", "require" => false, "index" => "index", "show_in_table" => false],
        ["name" => "deleted_by", "type" => "select", "table" => "sotrudniki", "title" => "Удаливший", "require" => false, "index" => "index", "default" => 1, "show_in_table" => false],
        // ["name"=>"is_deleted","name_1c"=>"DeletionMark","type"=>"boolean","title"=>"Удален","require"=>true,'default'=>false,"index"=>"index"],
        ["name" => "is_protected", "type" => "boolean", "title" => "Защищен от изменения", "require" => true, 'default' => false, "index" => "index", "show_in_table" => false],
        ["name" => "sync_1c_at", "type" => "datetime", "title" => "Время синхронизации с 1С", "require" => false, 'default' => false, "index" => "index", "show_in_table" => false],
    ];
    // уникальный ключ
    protected $unique_key = ['id'];
    // преобразователи типов
    protected $casts = [
        // 'is_protected' => 'boolean',
        'name' => 'string',
        'comment' => 'string',
    ];

    // все таблицы в БД
    private $tables =  [
        // "tables" => ["Title" => "Таблицы", "Class" => "Table"],
        // "users" => ["Title" => "Пользователи", "Class" => "User"],
        // "calendars" => ["Title" => "Календари", "Class" => "Calendar"],
        // "events" => ["Title" => "События", "Class" => "Event"],
        "nomenklatura" => ["Title" => "Справочник номенклатуры", "Class" => "App\Nomenklatura", "Name1C" => "Catalog_Номенклатура", "Name" => "nomenklatura"],
        "doc_types" => ["Title" => "Типы документов", "Class" => "App\DocType", "Name1C" => "Catalog_ВидыНоменклатуры", "Name" => "doc_types"],
        "ed_ism" => ["Title" => "Единицы измерения", "Class" => "App\EdIsm", "Name1C" => "Catalog_КлассификаторЕдиницИзмерения", "Name" => "ed_ism"],
        "manufacturers" => ["Title" => "Производители", "Class" => "App\Manufacturer", "Name" => "manufacturers"],
        "nds" => ["Title" => "Ставки НДС", "Class" => "App\NDS", "Name" => "nds"],
        "sklad_receives" => ["Title" => "Приходные накладные", "Class" => "App\SkladReceive", "Name1C" => "Document_ПоступлениеТоваровУслуг", "Name" => "sklad_receives"],
        "sklad_receive_items" => ["Title" => "Позиции приходных накладных", "Class" => "App\SkladReceiveItem", "Name" => "sklad_receive_items"],
        "dogovors" => ["Title" => "Договоры", "Class" => "App\Dogovor", "Name1C" => "Catalog_ДоговорыКонтрагентов", "Name" => "dogovors"],
        "kontragents" => ["Title" => "Контрагенты", "Class" => "App\Kontragent", "Name1C" => "Catalog_Контрагенты", "Name" => "kontragents"],
        "sklads" => ["Title" => "Склады", "Class" => "App\Sklad", "Name1C" => "Catalog_Склады", "Name" => "sklads"],
        "firms" => ["Title" => "Организации", "Class" => "App\Firm", "Name1C" => "Catalog_Организации", "Name" => "firms"],
        "rs" => ["Title" => "Расчетные счета", "Class" => "App\RS", "Name1C" => "Catalog_БанковскиеСчета", "Name" => "rs"],
        "banks" => ["Title" => "Банки", "Class" => "App\Bank", "Name1C" => "Catalog_Банки", "Name" => "banks"],
        "valuta" => ["Title" => "Валюта", "Class" => "App\Valuta", "Name1C" => "Catalog_Валюты", "Name" => "valuta"],
        "fizlica" => ["Title" => "Физические лица", "Class" => "App\FizLico", "Name1C" => "Catalog_ФизическиеЛица", "Name" => "fizlica"],
        "sotrudniks" => ["Title" => "Сотрудники", "Class" => "App\Sotrudnik", "Name" => "sotrudniks"],
        "firm_positions" => ["Title" => "Должности", "Class" => "App\FirmPosition", "Name" => "firm_positions"],
        "shipping_companies" => ["Title" => "Транспортные компании", "Class" => "App\ShippingCompany", "Name" => "shipping_companies"],
        "sklad_moves" => ["Title" => "Перемещения по складам", "Class" => "App\SkladMove", "Name" => "sklad_moves"],
        "sklad_move_items" => ["Title" => "Позиции складских перемещений", "Class" => "App\SkladMoveItem", "Name" => "sklad_move_items"],
        "notifications" => ["Title" => "Уведомления", "Class" => "App\Notification", "Name" => "notifications"],
        "notification_types" => ["Title" => "Типы уведомлений", "Class" => "App\NotificationType", "Name" => "notification_types"],
        "recipes" => ["Title" => "Рецептуры", "Class" => "App\Recipe", "Name" => "recipes"],
        "recipe_items" => ["Title" => "Компоненты", "Class" => "App\RecipeItem", "Name" => "recipe_items"],
        "recipe_item_replaces" => ["Title" => "Замены в рецептуре", "Class" => "App\RecipeItemReplace", "Name" => "recipe_item_replaces"],
        "productions" => ["Title" => "Производство", "Class" => "App\Production", "Name1C" => "Document_ОтчетПроизводстваЗаСмену", "Name" => "productions"],
        "production_items" => ["Title" => "Производимые изделия", "Class" => "App\ProductionItem", "Name" => "production_items"],
        "production_components" => ["Title" => "Компоненты изделия", "Class" => "App\ProductionComponent", "Name" => "production_components"],
        "production_replaces" => ["Title" => "Замены в производстве", "Class" => "App\ProductionReplace", "Name" => "production_replaces"],


        "contract_types" => ["Title" => "Виды договоров", "Class" => "App\ContractType", "Name" => "contract_types"],
        "contracts" => ["Title" => "Договоры", "Class" => "App\Contract", "Name" => "contracts"],
        "orders" => ["Title" => "Заказы", "Class" => "App\Order", "Name" => "orders"],
        "order_items" => ["Title" => "Позиции заказа", "Class" => "App\OrderItem", "Name" => "order_items"],
        "invoices" => ["Title" => "Счета", "Class" => "App\Invoice", "Name" => "invoices"],
        "invoice_items" => ["Title" => "Позиции счета", "Class" => "App\InvoiceItem", "Name" => "invoice_items"],
        "acts" => ["Title" => "Реализации", "Class" => "App\Act", "Name" => "acts"],
        "act_items" => ["Title" => "Позиции накладной", "Class" => "App\ActItem", "Name" => "act_items"],
        // админка сайта
        "site_contents" => ["Title" => "Контент раздела", "Class" => "App\SiteContent", "Name" => "site_contents"],
        "site_menu_points" => ["Title" => "Раздел сайта", "Class" => "App\SiteMenuPoint", "Name" => "site_menu_points"],
        "site_modules" => ["Title" => "Модуль раздела", "Class" => "App\SiteModule", "Name" => "site_modules"],
        // права доступа
        // информация о пользователе
        "user_info" => ["Title" => "Информация пользователя", "Class" => "App\UserInfo", "Name" => "user_info"],
        // файлы
        "files" => ["Title" => "Файлы", "Class" => "App\File", "Name" => "files"],
        "file_drivers" => ["Title" => "Места хранения файлов", "Class" => "App\FileDriver", "Name" => "file_drivers"],
        "file_list" => ["Title" => "Списки файлов", "Class" => "App\FileList", "Name" => "file_list"],
        // группы
        "tags" => ["Title" => "Группы", "Class" => "App\Tag", "Name" => "tags"],
        "table_tags" => ["Title" => "Группы записей", "Class" => "App\TableTag", "Name" => "table_tags"],
        // отчеты
        "report_sklad_remains" => ["Title" => "Отчет по остаткам", "Class" => "App\ReportSkladRemains", "Name" => "report_sklad_remains"],
        // серийники
        "serial_nums" => ["Title" => "Серийные номера", "Class" => "App\SerialNum", "Name" => "serial_nums"],
    ];
    // преобразователь модели
    private $modModel = [];

    public function __construct($table = null)
    {
        parent::__construct();

        if ($table) {
            if (isset($this->tables[$table])) {
                $this->table = $table;

                //         $table_description = $this->tables[$table];
                //         $this->title($table_description["Title"]);
                //         $this->class_name($table_description["Class"]);
                //         if (isset($table_description["Name1C"])) $this->name_1c($table_description["Name1C"]);
                //         // new instance
                //         $class_name = $this->class_name();
                //         $table = new $class_name;
                //         $this->model = $table->model();
                //         if (isset($table->appends)) $this->appends = $table->appends;
                //         $this->t = $table;
                //         return $table;
            } else {
                return null;
            }
        }
    }

    // public static function boot()
    // {
    //     parent::boot();
    // }

    // общие для всех читатели
    // разрешения
    public function getPermissionsAttribute()
    {
        if (isset($this->attributes['id'])) {
            $user = Auth::user();
            $model = $this->find($this->attributes['id']);
            if ($model && $user) {
                return [
                    "show" => (int)$user->can('view', $model),
                    "copy" => (int)$user->can('create', $this),
                    "edit" => (int)$user->can('update', $model),
                    "delete" => (int)$user->can('delete', $model)
                ];
            }
        }
        return [];
    }
    // файлы
    public function getFilesAttribute()
    {
        if (isset($this->attributes['id'])) {
            $value = $this->attributes['id'];
            $model = $this->find($value);
            return $model ? $model->files('document')->get($this->files_fields) : null;
        }
        return null;
    }
    // файлы с выбором из списка
    public function getFileListAttribute()
    {
        if (isset($this->attributes['id'])) {
            $value = $this->attributes['id'];
            $model = $this->find($value);
            return $model ? $model->file_list()->select(['id', 'file_id'])->get() : null;
        }
        return null;
    }
    // картинки
    public function getImagesAttribute()
    {
        if (isset($this->attributes['id'])) {
            $value = $this->attributes['id'];
            $model = $this->find($value);
            return $model ? $model->files('image')->get($this->files_fields) : null;
        }
    }
    // основная картинка
    public function getMainImageAttribute()
    {
        if ($this->has_images) {
            if (isset($this->attributes['id'])) {

                $value = $this->attributes['id'];
                // найдем основное изображение
                // $image = $this->find($value)->files('image')->where('is_main',true)->first();
                $model = $this->find($value);
                $image = $model ? $model->files('image')->orderBy('is_main', 'desc')->first() : null;
                if ($image) {
                    try {
                        $disk = new ABPStorage($image->driver);
                        if ($disk) {
                            $preview = $disk->file_preview($image);
                            return $preview;
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }
        }
        return null;
    }

    // получаем список файлов
    public function file_list()
    {
        if ($this->has_file_list) {
            $file_type = new FileType;
            $file_type_id = $file_type->where('name', '=', 'list')->first();
            if ($file_type_id) {
                $files_collection = $this->morphMany('App\FileList', 'table')->with('file');
                return $files_collection;
            }
        }
        return null;
    }

    // получаем коллекцию файлов
    public function files($type)
    {
        if ($this->has_files) {
            $file_type = new FileType;
            $file_type_id = $file_type->where('name', '=', $type)->first();
            if ($file_type_id) {
                $files_collection = $this->morphMany('App\File', 'table');
                $files = $files_collection->where('file_type_id', $file_type_id->id);
                return $files;
            }
        }
        return null;
    }

    // получаем коллекцию картинок
    public function images()
    {
        if ($this->has_images) {
            return $this->files('image');
        }
        return null;
    }
    // получаем коллекцию документов
    public function documents()
    {
        if ($this->has_files) {
            return $this->files('document');
        }
        return null;
    }

    // связь с файлами всех типов
    public function files_()
    {
        if ($this->has_files || $this->has_images || $this->has_file_list) {
            return $this->morphMany('App\File', 'table');
        }
        return null;
    }

    // блок работы с группами
    // группы для вывода в таблице
    public function getGroupsAttribute()
    {
        if ($this->has_groups()) {
            return $this->morphMany('App\TableTag', 'table')->get(["id", "tag_id"]);
        }
    }

    // коллекция групп
    public function groups()
    {
        if ($this->has_groups()) {
            return $this->morphMany('App\TableTag', 'table');
        }
    }

    // выдаем - есть ли группа с id
    public function has_group_id($tag_id)
    {
        if ($this->has_groups() && isset($this->attributes["id"])) {
            if ($this->groups->where('tag_id', $tag_id)->count() > 0) {
                return true;
            }
        }
        return false;
    }

    // добавляем группу
    public function add_group($tag_id)
    {
        if ($this->has_groups() && isset($this->attributes["id"])) {
            $table_tag = new TableTag;
            return $table_tag->fill([
                "tag_id" => $tag_id,
                "table_type" => $this->class_name(),
                "table_id" => $this->attributes["id"]
            ])->save();
        }
        return false;
    }

    // удаляем группы
    public function remove_groups($tags)
    {
        if ($this->has_groups() && isset($this->attributes["id"])) {
            return $this->groups()->whereIn('tag_id', $tags)->get()->each->delete();
        }
        return false;
    }

    // добавляем файл в список
    public function add_list_file($file_id)
    {
        if ($this->has_file_list() && isset($this->attributes["id"])) {
            $file_list = new FileList;
            return $file_list->fill([
                "file_id" => $file_id,
                "table_type" => $this->class_name(),
                "table_id" => $this->attributes["id"]
            ])->save();
        }
        return false;
    }

    // выдаем строку для селекта
    public function getSelectListTitleAttribute()
    {
        $title = '';
        if (isset($this->attributes["id"])) {
            $model = $this->withTrashed()->find($this->attributes["id"]);
            switch ($this->table_type) {
                case "document": {
                        $doc_date = Carbon::createFromFormat('Y-m-d', $model->doc_date);
                        $format_date = $doc_date ? $doc_date->format('d.m.Y') : $model->doc_date;
                        $title = $model ? "№ " . $model->doc_num . " от " . $format_date : 'Не установлено';
                    }
                    break;
                case "sub_table": {
                        $title = $model ? $model->name : 'Не установлено';
                    }
                    break;
                case "catalog":
                default: {
                        $title = $model ? $model->name : 'Не установлено';
                    }
                    break;
            }
            $title = trim($title);
            ABPCache::put_select_list($this->table, $this->attributes["id"], $title);
        }

        return $title;
    }

    //
    // public static function user_id($id=null) {
    //     if (is_null($id)) {
    //         return self::$user_id;
    //     } else {
    //         self::$user_id = $id;
    //     }
    // }

    // сеттер или геттер иконки модификатора модели
    public function modModel($newMod)
    {
        if (is_null($newMod)) {
            return $this->modModel;
        } else {
            $this->modModel = $newMod;
            return;
        }
    }

    // геттер всех таблиц в бд
    public function tables()
    {
        return $this->tables;
    }

    // сеттер или геттер иконки таблицы
    public function icon($icon = null)
    {
        if (is_null($icon)) {
            return $this->icon;
        } else {
            $this->icon = $icon;
            return;
        }
    }

    // получить имя соединения с БД
    public function connection($connection_name = null)
    {
        if ($connection_name) {
            $this->connection = $connection_name;
        } else {
            return $this->connection;
        }
    }

    // получить все значения для селектов
    public function get_select_data()
    {
        return $this->whereNull('deleted_at')->pluck('name', 'id');
    }

    // геттер уникального ключа таблицы
    public function unique_key($key = null)
    {
        if (is_null($key)) {
            return $this->unique_key;
        } else {
            return $this->unique_key = $key;
        }
    }

    // геттер класса таблицы
    public function class_name()
    {
        if (isset($this->tables[$this->table])) {
            return $this->tables[$this->table]["Class"];
        } else {
            return false;
        }
    }

    // сеттер или геттер имени таблицы
    public function table($name = '')
    {
        if ($name == '') {
            return $this->table;
        } else {
            $this->table = $name;
            return;
        }
    }

    // сеттер или геттер имени таблицы в 1С
    public function name_1c($name = '')
    {
        if ($name == '') {
            return $this->tableName1C;
        } else {
            $this->tableName1C = $name;
            return;
        }
    }

    // сеттер или геттер тайтла таблицы
    public function title($name = '')
    {
        if ($name == '') {
            return isset($this->tables[$this->table]["Title"]) ? $this->tables[$this->table]["Title"] : '';
        } else {
            $this->tables[$this->table]["Title"] = $name;
            return;
        }
    }

    // сеттер или геттер сортировки таблицы
    public function default_order($order = null)
    {
        if ($order) {
            if (is_array($order)) {
                if (count($order) == 2) {
                    $this->default_order = $order;
                }
            } else {
                $this->default_order = [$order, 'asc'];
            }
        } else {
            return $this->default_order;
        }
    }

    // сеттер или геттер параметра наличия папок в таблице
    public function has_folders($hasFolders = false)
    {
        if ($hasFolders === false) {
            return $this->has_folders;
        } else {
            $this->has_folders = true;
            return;
        }
    }

    // сеттер или геттер параметра наличия папок в таблице 1c
    public function has_folders_1c($hasFolders = false)
    {
        if ($hasFolders === false) {
            return $this->has_folders_1c;
        } else {
            $this->has_folders_1c = true;
            return;
        }
    }

    // // сеттер или геттер параметра использования файлов
    public function has_files($hasFiles = false)
    {
        if ($hasFiles === false) {
            return $this->has_files;
        } else {
            $this->appends[] = 'files';
            $this->has_files = true;
            return;
        }
    }

    // // сеттер или геттер параметра использования списка файлов
    public function has_file_list($has_file_list = false)
    {
        if ($has_file_list === false) {
            return $this->has_file_list;
        } else {
            $this->appends[] = 'file_list';
            $this->has_file_list = true;
            return;
        }
    }

    // // сеттер или геттер параметра использования картинок
    public function has_images($has_images = false)
    {
        if ($has_images === false) {
            return $this->has_images;
        } else {
            $this->appends[] = 'images';
            $this->appends[] = 'main_image';
            $this->has_images = true;
            return;
        }
    }

    // // сеттер или геттер параметра использования групп
    public function has_groups($has_groups = false)
    {
        if ($has_groups === false) {
            return $this->has_groups;
        } else {
            $this->appends[] = 'groups';
            $this->has_groups = true;
            return;
        }
    }

    // // сеттер или геттер параметра использования серийных номеров (добавление в БД)
    public function has_series($has_series = null)
    {
        if ($has_series === null) {
            return $this->has_series;
        } else {
            $this->appends[] = 'serials';
            $this->has_series = true;
            return;
        }
    }

    // // сеттер или геттер параметра использования серийных номеров (перемещение)
    public function has_sub_series($has_sub_series = null)
    {
        if ($has_sub_series === null) {
            return $this->has_sub_series;
        } else {
            $this->appends[] = 'serials';
            $this->has_sub_series = true;
            return;
        }
    }

    // проверяем, есть ли столбец в моделе
    public function has_model_column($column)
    {
        $model = collect($this->model());
        return $model->contains('name', $column);
    }
    // проверяем, есть ли столбец в БД
    public function has_column($column)
    {
        return Schema::connection($this->connection)->hasColumn($this->table, $column);
    }

    // проверяем, есть ли таблица в заданном соединении
    public function table_exists()
    {
        return Schema::connection($this->connection)->hasTable($this->table);
    }


    // получим коллекции на основании фильтров полученного запроса
    public function apply_request_filters($request, $replaces = [], $id = null, $only_count = false)
    {
        // параметры запроса
        $req = array_merge($request->input(), $replaces);
        // текущие данные - неопределены
        $this->data = null;
        // текущий набор данных
        $data = $this;
        // настройки сортировки
        $defaultOrder = $this->default_order();
        // сделать по умолчанию скрытые столбцы видимыми
        $make_visible = [];
        // показывать все поля
        $show_all_fields = true;
        // если переданы столбцы
        if ($this->table_type == 'report') {
            // фильтр столбцов для отчетов
        } else {
            if (isset($req['fields']) || isset($req["trashed"])) {
                $cols = array();
                // с удаленными записями
                if (isset($req["trashed"])) {
                    $cols[] = "deleted_at";
                }
                $fields = explode(',', urldecode($req['fields']));
                foreach ($fields as $f) {
                    $f = trim($f);
                    // если столбец есть в таблице
                    if ($this->has_column($f)) {
                        $cols[] = $f;
                    } else {
                        // если столбец виртуальный
                        if (in_array($f, $this->appends)) {
                            // если столбец есть в массиве hidden - нужно добавить в массив makeVisible
                            // if (in_array($f,$this->hidden)) {
                            //     $make_visible[] = $f;
                            // }
                            $make_visible[] = $f;
                        }
                    }
                }
                if (count($cols) > 0) {
                    $data = $data->select($cols);
                    $show_all_fields = false;
                }
                unset($req['fields']);
            }
        }

        // если передана сортировка
        if ($this->table_type == 'report') {
            // сортировка для отчетов
        } else {
            // сортировка по id
            $sort_by_id = true;
            // если передана сортировка
            if (isset($req['order'])) {
                $req["order"] = urldecode($req["order"]);
                $orders = explode(',', $req['order']);
                $order_field = trim($orders[0]);
                // если сотрировка не передана
                if (count($orders) > 0 && $this->has_column($order_field)) {
                    if (count($orders) == 1) {
                        if ($order_field == 'id') $sort_by_id = false;
                        $data = $data->orderBy($order_field, $defaultOrder[1]);
                    } else {
                        if (in_array('id', $orders)) $sort_by_id = false;
                        $data = $data->orderBy($order_field, $orders[1]);
                    }
                } else {
                    if ($defaultOrder[0] == 'id') $sort_by_id = false;
                    $data = $data->orderBy($defaultOrder[0], $defaultOrder[1]);
                }
                unset($req['order']);
            } else {
                if ($defaultOrder[0] == 'id') $sort_by_id = false;
                $data = $data->orderBy($defaultOrder[0], $defaultOrder[1]);
            }
            // если в процессе упорядочивания не была передана сортировка по id - добавим ее последней
            if ($sort_by_id) {
                $data = $data->orderByRaw('CAST(id AS UNSIGNED) ASC');
            }
        }

        // блок фильтров
        // базовая фильтрация
        $data = $data->where('id', '>', 1);

        if (isset($req['filter'])) {
            $replaceSourceArray = ["lt", "gt", "eq", "ne", "ge", "le", "like"];
            $replaceTargetArray = ["<", ">", "=", "<>", ">=", "<=", "like"];
            $req["filter"] = urldecode($req["filter"]);
            if (preg_match_all("/((\w+\.?)+)\s+(lt|gt|eq|ne|ge|le|like|in|ni|morph|morphin|morphni)\s+([\[\w\]\.\,\-\"\\\]+)(\s+(or|and)\s+)?/iu", $req['filter'], $filterResults, PREG_SET_ORDER)) {
                $nextExp = "and";
                // dd($filterResults);
                foreach ($filterResults as $filterResult) {
                    $join_column = trim($filterResult[1]);
                    $joins = explode('.', $join_column);
                    if (count($joins) == 1) {
                        $exp = str_replace($replaceSourceArray, $replaceTargetArray, strtolower($filterResult[3]));
                        $filterVal = $filterResult[4];
                        $column = $joins[0];
                        // dd($column, $exp, $filterVal);
                        // без связей
                        if ($this->has_column($column) || $this->has_model_column($column) || ($this->has_groups() && $column == 'groups')) {
                            // dd($column, $exp, $filterVal);
                            switch ($exp) {
                                case 'like': {
                                        $colValue = '%' . $filterVal . '%';
                                    }
                                    break;
                                case 'in':
                                case 'ni': {
                                        // проверим, не массив ли передан в формате JSON
                                        $colValue = json_decode($filterVal);

                                        if (!is_array($colValue)) {
                                            $colValue = array($colValue);
                                        }
                                    }
                                    break;
                                default: {
                                        $colValue = $filterVal;
                                    }
                            }
                            $method = $nextExp == 'and' ? 'where' : 'orWhere';
                            // var_dump($method);
                            $data = $data->{$method}(function ($data) use ($filterVal, $exp, $colValue, $column) {
                                switch ($exp) {
                                    case 'morphin':
                                    case 'morphni':
                                    case 'morph': {
                                            $morphFilterValue = explode('.', $filterVal);
                                            $data->whereHasMorph($column, json_decode($morphFilterValue[0]), function ($query) use ($morphFilterValue, $exp) {
                                                $fomalizeFilterValue = json_decode($morphFilterValue[1]);
                                                if (count($fomalizeFilterValue)) {
                                                    switch ($exp) {
                                                        case 'morphin': {
                                                                $query->whereIn('id', $fomalizeFilterValue);
                                                            }
                                                            break;
                                                        case 'morphni': {
                                                                $query->whereNotIn('id', $fomalizeFilterValue);
                                                            }
                                                            break;
                                                        case 'morph': {
                                                                $query->where('id', $fomalizeFilterValue[0]);
                                                            }
                                                            break;
                                                    }
                                                }
                                            });
                                            // dd($data->dd());
                                        }
                                        break;
                                    case 'in': {
                                            // для групп
                                            if ($column == 'groups') {
                                                $data = $data->whereHas('groups', function ($query) use ($colValue) {
                                                    $query->whereIn('tag_id', $colValue);
                                                });
                                            } else {
                                                // для всех остальных полей
                                                $data = $data->whereIn($column, $colValue);
                                            }
                                        }
                                        break;
                                    case 'ni': {
                                            // для групп
                                            if ($column == 'groups') {
                                                $data = $data->whereHas('groups', function ($query) use ($colValue) {
                                                    $query->whereNotIn('tag_id', $colValue);
                                                });
                                            } else {
                                                // для всех остальных полей
                                                $data = $data->whereNotIn($column, $colValue);
                                            }
                                        }
                                        break;
                                    default: {
                                            // для групп
                                            if ($column == 'groups') {
                                                $data = $data->whereHas('groups', function ($query) use ($colValue, $exp) {
                                                    switch ($exp) {
                                                        case '=': {
                                                                $filter = 'where';
                                                                $expr = '=';
                                                                $val = is_array($colValue) ? $colValue[0] : $colValue;
                                                            }
                                                            break;
                                                        case '<>': {
                                                                $filter = 'where';
                                                                $expr = '<>';
                                                                $val = is_array($colValue) ? $colValue[0] : $colValue;
                                                            }
                                                            break;
                                                        case 'in': {
                                                                $filter = 'whereIn';
                                                                $val = is_array($colValue) ? $colValue : [$colValue];
                                                            }
                                                            break;
                                                        case 'ni': {
                                                                $filter = 'whereNotIn';
                                                                $val = is_array($colValue) ? $colValue : [$colValue];
                                                            }
                                                            break;
                                                        default: {
                                                                $filter = 'whereIn';
                                                                $val = is_array($colValue) ? $colValue : [$colValue];
                                                            }
                                                    }
                                                    if (isset($expr)) {
                                                        $query->{$filter}('tag_id', $expr, $val);
                                                    } else {
                                                        $query->{$filter}('tag_id', $val);
                                                    }

                                                    // $query->whereNotIn('tag_id', $exp, $colValue);
                                                });
                                            } else {
                                                // для всех остальных полей
                                                $data = $data->where($column, $exp, $colValue);
                                            }
                                        }
                                }
                            });

                            // $data->dd();
                        } else {
                            // dd('no col ' . $column);
                        }
                        $nextExp = isset($filterResult[5]) ? trim(strtolower($filterResult[5])) : 'and';
                    } else {
                        // dd($exp);
                        // со связями
                        $exp = str_replace($replaceSourceArray, $replaceTargetArray, strtolower($filterResult[3]));
                        $filterVal = $filterResult[4];
                        // последний параметр - для фильтрации
                        $last_join = array_pop($joins);
                        // если последний параметр == группы
                        if ($last_join == 'groups') {
                            $joins[] = $last_join;
                            $last_join = 'tag_id';
                        }
                        // блок фильтрации без последнего параметра
                        $where_has_join = implode('.', $joins);
                        // метод в зависимости от следующего логического условия выбора
                        $method = $nextExp == 'and' ? 'where' : 'orWhere';
                        // dd($method, $nextExp);
                        // фильтруем
                        $data->{$method}(function ($query) use ($where_has_join, $last_join, $exp, $filterVal) {
                            $query->whereHas($where_has_join, function ($query) use ($last_join, $exp, $filterVal) {
                                switch ($exp) {
                                    case 'morphin':
                                    case 'morphni':
                                    case 'morph': {
                                            $morphFilterValue = explode('.', $filterVal);
                                            $query->whereHasMorph($last_join, json_decode($morphFilterValue[0]), function ($query) use ($morphFilterValue, $exp) {
                                                $fomalizeFilterValue = json_decode($morphFilterValue[1]);
                                                if (count($fomalizeFilterValue)) {
                                                    switch ($exp) {
                                                        case 'morphin': {
                                                                $query->whereIn('id', $fomalizeFilterValue);
                                                            }
                                                            break;
                                                        case 'morphni': {
                                                                $query->whereNotIn('id', $fomalizeFilterValue);
                                                            }
                                                            break;
                                                        case 'morph': {
                                                                $query->where('id', $fomalizeFilterValue[0]);
                                                            }
                                                            break;
                                                    }
                                                }
                                            });
                                        }
                                        break;
                                    case 'in': {
                                            $query->whereIn($last_join, json_decode($filterVal));
                                        }
                                        break;
                                    case 'ni': {
                                            $query->whereNotIn($last_join, json_decode($filterVal));
                                        }
                                        break;
                                        // case 'like': {
                                        //         $query->where($last_join, 'like', '%' . $filterVal . '%');
                                        //     }
                                        //     break;
                                    default: {
                                            $query->where($last_join, $exp, $filterVal);
                                        }
                                }
                            });
                        });
                        $nextExp = isset($filterResult[5]) ? trim(strtolower($filterResult[5])) : 'and';

                        // $data->dd();
                    }
                }
            }
        }
        // блок поиска
        if (isset($req['search'])) {
            $search = urldecode(trim($req['search']));
            $search_fields = $this->search_fields();
            if (count($search_fields) > 0) {
                $data = $data->where(function ($query) use ($search_fields, $search) {
                    foreach ($search_fields as $field => $exp) {
                        switch ($exp) {
                            case 'like': {
                                    $query->orWhere(DB::raw('lower(' . $field . ')'), 'like', '%' . strtolower($search) . '%');
                                    // $query->orWhere($field, 'ilike', '%' . $search . '%');
                                }
                                break;
                            default: {
                                    //  =
                                    $query->orWhere($field, '=', $search);
                                }
                        }
                    }
                });
            }
        }
        // $data->dd();
        // блок фильтрации по группам
        if (isset($req['tags'])) {
            $tags = explode(',', urldecode($req['tags']));
            $data = $data->whereHas('groups', function ($query) use ($tags) {
                $query->whereIn('tag_id', $tags);
            });
        }

        // если передан id
        if ($id) {
            $data = $data->where('id', $id);
        }

        // с удаленными записями
        if (isset($req["trashed"])) {
            $data = $data->withTrashed();
        }
        // проверим контексты
        if (isset($req["scope"])) {
            $scopes = explode(",", $req["scope"]);
            foreach ($scopes as $scope_with_param) {
                $scope_with_param_arr = explode(".", $scope_with_param);
                $scope = $scope_with_param_arr[0];
                if (count($scope_with_param_arr) > 1) {
                    unset($scope_with_param_arr[0]);
                    $param = implode(",", $scope_with_param_arr);
                    $data = $data->$scope($param);
                } else {
                    $data = $data->$scope();
                }
            }
        }
        // $data->dd();
        // dd($data->count());

        // посчитаем общее кол-во записей перед лимитами
        $this->set_count($data->count());

        if ($only_count) {
            return $this->get_count();
        } else {
            // итоги
            $itogs = [];
            if ($this->table_type != 'catalog') {
                $data_collection = collect($data->get());
                foreach ($this->model() as $field) {
                    $itog_types = ['kolvo', 'money'];
                    $itog_fields = [];
                    if (in_array($field['type'], $itog_types)) {
                        $itog_fields[] = $field['name'];
                    }
                    if (count($itog_fields) > 0) {
                        foreach ($itog_fields as $f) {
                            $itogs[$f] = $data_collection->sum($f);
                        }
                    }
                }
            }
            // дефолтные настройки лимитов
            $defLimit = 10;
            // если передано смещение и лимит
            if (isset($req['limit'])) {
                $req["limit"] = intval(urldecode($req["limit"]));
                if ($req['limit'] != -1) $data = $data->limit($req['limit']);
            } else {
                $data = $data->limit($defLimit);
            }
            // если передано смещение
            if (isset($req['offset']) && (!isset($req['limit']) || (isset($req['limit']) && $req['limit'] != -1))) {
                $req["offset"] = urldecode($req["offset"]);
                $data = $data->offset($req['offset']);
            } else {
                if (isset($req['limit']) && $req['limit'] != -1) $data->offset(0);
            }

            // проверим расширения
            if (isset($req["extensions"])) {
                $extensions = explode(',', urldecode($req['extensions']));
                foreach ($extensions as $extension) {
                    $make_visible[] = trim($extension);
                }
            }

            // // если это документ - выдадим еще и таблицу
            // if ($this->has_items()) {
            // // if ($this->table_type == 'document') {
            //     $data = $data->with("items");
            // }

            // выдадим подчиненные таблицы
            $sub_tables_arr = [];

            foreach ($this->sub_tables() as $st) {
                if (isset($st["method"]) && $st["method"] != "items") $sub_tables_arr[] = $st["method"];
                // if (isset($st["method"]) && method_exists($this, $st["method"])) $sub_tables_arr[] = $st["method"];
            }
            $data = $data->with($sub_tables_arr);

            // var_dump($data->dd());
            // return;

            // сохраним коллекцию
            $this->data = $data;

            if ($this->table_type == 'report') {
                return [
                    "data" => $data,
                    "itogs" => isset($itogs) ? $itogs : null
                ];
            } else {

                // проеобразуем коллекцию в массив
                $collection = $data->get();

                // скроем ненужные поля
                // if (!$show_all_fields) {
                // $make_hidden = $this->appends;
                $make_hidden = [];
                // $make_visible = array_merge($make_visible, ["permissions"]);
                if (count($make_visible) > 0 || count($make_hidden) > 0) {
                    // dd($make_visible, $make_hidden);

                    $collection = $collection->each(function (&$model) use ($make_visible, $make_hidden) {
                        if (count($make_hidden) > 0) $model->makeHidden($make_hidden);
                        if (count($make_visible) > 0) $model->makeVisible($make_visible);
                    });
                }
                // }

                // для записей NULL выведем пустую строку
                if ($collection) $collection = $collection->toArray();
                // array_walk_recursive($collection, function(&$item) {
                //     $item = is_null($item) || $item=='null' ? '' : $item;
                // });

                // возвращаем массив данных
                return [
                    "data" => $collection,
                    "itogs" => isset($itogs) ? $itogs : null
                ];
            }
        }
    }

    // возвращаем список данных для таблицы в соответствии с фильтрами в запросе
    public function get_table_row($request, $id)
    {
        $res = $this->apply_request_filters($request, [], $id);
        return [
            "data" => $this->get_count() > 0 ? $res["data"][0] : null,
            "count" => $this->get_count()
        ];
    }

    // возвращаем список данных для таблицы в соответствии с фильтрами в запросе
    public function get_table_data($request, $only_count = false)
    {
        $data = $this->apply_request_filters($request, [], null, $only_count);

        return [
            "data" => $data["data"],
            "itogs" => $data["itogs"],
            "count" => $this->get_count()
        ];
    }

    // возвращаем список данных для селекта в соответствии с фильтрами в запросе
    // в формате [{id:id1, select_list_title:'title_1'},...,{id:idn, select_list_title:'title_n'}]
    public function get_list_data($request)
    {
        $list_arr = ["id", "select_list_title"];
        $req_field_arr = [];
        if ($request->has("fields")) {
            $req_field_arr = explode(",", $request->fields);
        }
        $list_arr = array_merge($list_arr, $req_field_arr);
        $replaces = ["fields" => implode(",", $list_arr)];
        $data = $this->apply_request_filters($request, $replaces);
        return [
            "data" => $data["data"],
            "count" => $this->get_count()
        ];
    }

    // получим список поле, по которым можно искать
    public function search_fields()
    {
        $fields = [];
        foreach ($this->model() as $field) {
            if (isset($field["virtual"]) && $field["virtual"]) {
            } else {
                switch ($field["type"]) {
                    case "integer": {
                            $fields[$field["name"]] = '=';
                        }
                        break;
                    case "string": {
                            $fields[$field["name"]] = 'like';
                        }
                        break;
                }
            }
        }
        return $fields;
    }

    // передадим кол-во записей в коллекции
    private function set_count($count)
    {
        $this->count = $count;
    }
    // передадим кол-во записей в коллекции
    public function get_count()
    {
        return $this->count;
    }


    // удаляем рекурсивно с подчиненными таблицами и всеми расширениями текущую модель
    // например, Nomenklatura::find(300)->copy_recursive()
    public function delete_recursive($remove_files = false)
    {
        // ошибки
        $errors = [];
        // все расширения
        $exts = $this->get_extensions();
        // dd($exts);
        // все зависимости
        $relations = [];
        // файлы
        if ($exts["has_files"]) $relations[] = 'documents';
        // картинки
        if ($exts["has_images"]) $relations[] = 'images';
        // каталоги
        if ($exts["has_file_list"]) $relations[] = 'file_list';
        // группы
        if ($exts["has_groups"]) $relations[] = 'groups';
        // серийники
        if ($exts["has_series"] || $exts["has_sub_series"]) $relations += ['series', 'sn_movable'];
        // таблички
        if (isset($exts["sub_tables"])) {
            foreach ($exts["sub_tables"] as $sub_table_name => $sub_table_props) {
                $rel = isset($sub_table_props["method"]) ? $sub_table_props["method"] : $sub_table_name;
                // проверим, есть такой метод у экземпляра модели
                if (method_exists($this, $rel)) {
                    $relations[] = $rel;
                }
            }
        }
        // если это список файлов или список групп - связные файлы и группы удалять не будем
        // остальные типы удаляем рекурсивно
        $FileListClass = 'App\FileList';
        $TableTagsClass = 'App\TableTag';
        $FileClass = 'App\File';
        if (!$this instanceof $FileListClass && !$this instanceof $TableTagsClass) {
            // загрузим в модель все найденные связи
            $this->load($relations);
            // получим все связи
            $relations = $this->getRelations();
            // обраб=атываем каждую связь отдельно
            foreach ($relations as $relation => $items) {
                // echo "<p>Relation: " . $relation . "</p>";
                // print_r($items->toArray());
                // получаем экземпляр модели связной таблицы
                foreach ($items as $item) {
                    $res_del_item = $item->delete_recursive($remove_files);
                    if ($res_del_item["is_error"]) {
                        $errors += $res_del_item["errors"];
                    }
                }
            }
        }
        // наличие ошибки
        $is_error = count($errors) > 0;
        // если ошибок нет - удаляем запись
        if (!$is_error) $res = $this->delete();
        // ошибки добавим
        if ($res) {
            // если модель == файл, его надо удалить с ФС
            if ($this instanceof $FileClass && $remove_files) {
                // удаляем файл с ФС
                $disk = new ABPStorage($this->driver);
                $disk->delete_file($this->uid, $this->filename);
            }
        } else {
            $errors[] = "Не удалось удалить запись" . (isset($exts["props"]["titles"]["table"]) ? " в таблице " . $exts["props"]["titles"]["table"] : '');
        }
        // наличие ошибки
        $is_error = count($errors) > 0;
        return [
            "is_error" => $is_error,
            "errors" => $errors,
            "res" => !$is_error,
            "data" => $is_error ? 0 : 1
        ];
    }


    // копируем рекурсивно с подчиненными таблицами текущую модель
    // например, Nomenklatura::find(300)->copy_recursive()
    public function copy_recursive($add_sub_tables = false, $copy_options = null, Request $request = null)
    {
        // ошибки
        $errors = [];
        // текущий пользователь
        $user = auth()->user()->id;
        // данные, которые необходимо переопределить в моделе любого типа
        $replace_data = [];
        // столбцы, которые необходимо удалить из нового объекта
        $remove_fields = ['id', 'updated_at', 'updated_by', 'uuid', 'sync_1c_at'];
        // источник копирования
        $source = $this;
        // копируем модель
        $target = $source->replicate();
        // получим модель - список полей
        $model = collect($target->model());
        // столбцы, которые нужно оставлять при копировании (описаны в моделе)
        $model_fields = $model->filter(function ($field) {
            $except_types = ["morph", "key"];
            return !in_array($field["type"], $except_types);
        })->pluck("name");
        // стандартные замены в соответствии с типом данных
        $model->each(function ($field) use (&$model_fields) {
            if ($field["type"] == "morph") {
                $model_fields = $model_fields->merge([$field["name"] . "_id", $field["name"] . "_type"]);
            }
        });
        // аттрибуты модели
        $all_model_fields = collect($target->attributes)->keys();
        // удалим поля, которые не описаны в моделе
        $kill_fields = $all_model_fields->diff($model_fields)->all();
        // удалим все свойства, кроме описанных в моделе
        // и полей типа key (ассоциироваться с родителем будет на этапе сохранения)
        $remove_fields += $kill_fields;
        // если копируемая модель == File
        // все расширения
        $exts = $this->get_extensions();
        // замены для различных типов таблиц
        switch ($exts["props"]["table_type"]) {
            case "document": {
                    // у документов удаляем признак проведения
                    $remove_fields += ["doc_num"];
                    $replace_data += [
                        "is_active" => 0
                    ];
                }
                break;
            case "catalog": {
                    // имя в каталоге
                    if (isset($source->name)) $replace_data += ["name" => $source->name . "(копия)"];
                }
                break;
        }
        // у перемещения дополнительные признаки проведения
        $SkladMoveClass = "App\SkladMove";
        if ($source instanceof $SkladMoveClass) {
            $replace_data += [
                "is_out" => 0,
                "is_in" => 0
            ];
        }
        // у производства дополнительные признаки проведения
        $ProductionItemClass = "App\ProductionItem";
        if ($source instanceof $ProductionItemClass) {
            $remove_fields[] = "serial";
            $replace_data += [
                "is_producted" => 0,
            ];
        }
        $ProductionClass = "App\Production";
        if ($source instanceof $ProductionClass) {
            $replace_data += [
                "is_copied" => 1,
            ];
        }
        // удаляем ненужные столбцы
        foreach ($remove_fields as $field) {
            unset($target->{$field});
        }
        // данные, которые необходимо переопределить в моделе любого типа
        $replace_data += [
            "is_protected" => 0,
            "created_by" => $user
        ];
        // если источник - файл, его надо скопировать в ФС
        $FileClass = 'App\File';
        if ($source instanceof $FileClass) {
            // копируем файл
            $disk = new ABPStorage($source->driver);
            $new_file = $disk->copy_file($source);
            // задаем новое имя
            if (!$new_file["is_error"]) {
                $replace_data += $new_file["data"];
                // dd($replace_data);
            } else {
                $errors[] = "Не удалось скопировать файл " . $source->name;
            }
        }
        // если переданы данные
        if ($request) {
            $formalize_res = $this->formalize_data_from_request($request, [], 'copy');
            if (!$formalize_res["is_error"]) {
                $replace_data += $formalize_res["data"];
            }
        }
        // заменяем переданными значениями
        // foreach ($replace_data as $field => $value) {
        //     $target->{$field} = $value;
        // }
        $target->fill($replace_data);
        // сохраняем
        $target->save();
        // dd($target->toArray());
        // все подчиненные таблицы
        $all_sub_tables = [];
        $model_sub_tables = $source->sub_tables();
        foreach ($model_sub_tables as $sub_table) {
            if (isset($sub_table["method"]) || isset($sub_table["table"])) {
                // или метод или название подчиненной таблицы
                $method = isset($sub_table["method"]) ? $sub_table["method"] : $sub_table["table"];
                // если связь определена в моделе
                if (method_exists($source, $method)) {
                    $all_sub_tables[] = $method;
                }
            }
        }
        // копировать таблицы
        $sub_tables = [];
        // копировать расширения
        $extensions = [];
        // опции копирования, если переданы
        // $copy_options = json_decode('{"ext_groups":true,"ext_images":true,"ext_documents":true,"ext_file_list":true,"sub_table_recipes":true}', true);
        if ($copy_options) {
            // обработаем в соответствии с принятыми в API алгоритмами:
            // расширения имеют префикс ext_
            // подчиненные таблицы - префикс sub_table_
            foreach ($copy_options as $relation => $to_copy) {
                if ($to_copy) {
                    // если расширение
                    preg_match('/ext_(.+)/', $relation, $matches);
                    if (count($matches) > 1) {
                        $ext_name = $matches[1];
                        if (method_exists($source, $ext_name)) $extensions[] = $ext_name;
                    }
                    // если подчиненная таблица
                    preg_match('/sub_table_(.+)/', $relation, $matches);
                    // нашли и не указано, что все таблицы нужно копировать
                    if (!$add_sub_tables && count($matches) > 1) {
                        // загрузим список подчиненных таблиц, если его еще нет
                        $all_model_sub_tables = collect($model_sub_tables);
                        // имя подчиненной таблицы в описании
                        $sub_table_name = $matches[1];
                        // ищем в коллекции подчиненных таблиц
                        $sub_table = $all_model_sub_tables->first(function ($st) use ($sub_table_name) {
                            return $st["table"] == $sub_table_name;
                        });
                        // возвращаем метод или название подчиненной таблицы, если  метод не указан
                        // если в запросе были переданы данные подчиненной таблицы - добавлять не будем, потом занесем данные
                        if (isset($sub_table)) {
                            $method = isset($sub_table["method"]) ? $sub_table["method"] : $sub_table_name;
                            if (method_exists($source, $method)) {
                                $sub_tables[] = $method;
                            } else {
                                $errors[] = "Не удалось связать таблицы " . $source->table() . " и " . $sub_table_name;
                            }
                        }
                    }
                }
            }
        }
        // если копировать все подчиненные таблицы
        if ($add_sub_tables) {
            $sub_tables = array_merge($sub_tables, $all_sub_tables);
        }
        // $errors[] = json_encode(array_merge($copy_options, $sub_tables, $extensions));

        if (count($errors) == 0) {
            //  Если есть подчиненные таблицы или расширения для копирования
            if (count($sub_tables) > 0 || count($extensions) > 0) {
                // загрузим в модель все найденные связи
                $source->load(array_merge($sub_tables, $extensions));
                // получим все связи
                $relations = $source->getRelations();
                // dd($source->toArray());
                // обраб=атываем каждую связь отдельно
                foreach ($relations as $relation => $items) {
                    // dd($relation);
                    // получаем экземпляр модели связной таблицы
                    foreach ($items as $item) {
                        // echo "<p>Обрабатываю для " . $target->name . " связь " . $relation . ", дочку ";
                        // print_r($item->toArray());
                        // скопируем модель связной таблицы
                        $res_copy_item = $item->copy_recursive(true);
                        // dd($res_copy_item);
                        if ($res_copy_item["is_error"]) {
                            $errors += $res_copy_item["errors"];
                        } else {
                            $new_item = $res_copy_item["res"];
                            // ассоциируем новый объект с новым родителем
                            $target->{$relation}()->save($new_item);
                        }
                    }
                    // // обновим регистры
                    // $new_items = $target->{$relation}()->get();
                    // foreach ($new_items as $ni) {
                    //     $ni->touch();
                    // }
                }
            }
            // данные
            $result_data = $target->load($all_sub_tables)->makeVisible(['select_list_title'])->toArray();
        }
        $is_error = count($errors) > 0;
        return [
            "is_error" => $is_error,
            "errors" => $errors,
            "res" => $is_error ? null : $target,
            "data" => !$is_error && isset($result_data) ? $result_data : null
        ];
    }

    // сохраняем рекурсивно со всеми подчиненными таблицами
    public function save_recursive(Request $request, $data, $mod_type = 'add')
    {
        // dd($data);
        // класс модели
        $this_class = $this->class_name();
        // модель
        $this_model = new $this_class;

        // метод обработки по умолчанию - add
        $action = 'create';
        // // текст обработки по умолчанию
        // $action_title = 'создания';
        // ошибки
        $errors = [];
        // вывод результата
        $result_data = [];
        // // пользователь
        // $user = Auth::user();
        // формализованные данные - массив
        $data_array = $data["data"];
        // действие для проверки прав
        // если передан id записи
        if (isset($data_array["id"]) && !is_null($data_array["id"])) {
            if ($mod_type != 'copy') {
                // получаем экземпляр редактируемой записи
                $this_model = $this_model->find($data_array["id"]);
                // если модель найдена
                if ($this_model) {
                    $action = 'update';
                    // $action_title = 'редактирования';
                } else {
                    $this_model = new $this_class;
                    unset($data_array["id"]);
                }
            } else {
                // копирование отдельным методом
                // если копирование
                // $copy_options = $request->has('_copy_options') ? $request->_copy_options : [];
                // if ($request->has('id') && intval($request->id) > 1) {
                //     $source_id = $request->id;
                //     $source_model = new $this_class;
                //     $source_model->find($source_id);
                // } else {
                //     $errors["require"][] = 'id';
                // }
            }
        }
        // switch($mod_type) {
        //     case 'copy': {
        //         // $action_title = 'копирования';
        //     } break;
        //     case 'add': {
        //     } break;
        //     case 'edit': {
        //     } break;
        // }
        // // если есть права
        // if ($user->can($action, $this_model)) {
        // если есть файлы
        if (isset($data["files"]) && count($data["files"]) > 0) {
            // создаем экземпляр хранилища
            $disk = new ABPStorage('local');
            // обрабатываем файлы
            foreach ($data["files"] as $file_name => $file_settings) {
                // создаем новый файл
                $saved_file = $disk->saveFile($request->file($file_name), $file_settings["filename"]);
                // $errors[] = $saved_file;
                if ($saved_file) {
                    $data_array[$file_name] = $saved_file["uid"];
                } else {
                    $errors[] = 'Не удалось создать файл [' . $file_name . '] в файловой системе';
                }
            }
        }
        // если редактирование
        if ($action == 'update') {
            if (isset($data["files"])) {
                // удаляем старые файлы (новые уже созданы)
                foreach ($data["files"] as $file_name => $file_settings) {
                    // если есть уже файл в поле - удаляем его
                    if ($this_model->$file_name) {
                        // удаляем существующий файлик
                        $disk->delete_file($this_model->$file_name);
                    }
                }
            }
            // изменяем данные
            $res_update = $this_model->fill($data_array)->save();
            if ($res_update) $new_model = $this_model;
        } else {
            // проверки уникальности
            // если указан уникальный ключ
            if (method_exists($this_model, 'unique_key')) {
                $unique_key = $this_model->unique_key();
            }
            if (isset($unique_key)) {
                // сформируем проверку уникального ключа для updateOrCreate
                if (is_array($unique_key)) {
                    foreach ($unique_key as $key_field) {
                        if (isset($data_array[$key_field])) {
                            $unique_check[$key_field] = $data_array[$key_field];
                        }
                    }
                } else {
                    if (isset($data_array[$unique_key])) {
                        $unique_check = [$unique_key => $data_array[$unique_key]];
                    }
                }
            }
            // если не нашли проверку уникального ключа
            if (!isset($unique_check)) {
                // проверка уникальности по id
                if (isset($data_array["id"])) {
                    $unique_check = ["id" => $data_array["id"]];
                }
            }
            // var_dump($action, $data_array, isset($unique_check));
            // var_dump($action,$unique_check, $this_model, $data_array);
            // добавляем или изменяем существующую запись
            // print_r($data_array);
            // dd($data_array);
            try {
                if (isset($unique_check)) {
                    $new_model = $this_model->updateOrCreate($unique_check, $data_array);
                } else {
                    $new_model = $this_model->fill($data_array);
                    $new_model->save();
                }
            } catch (QueryException $e) {
                // dd($e);
                // ошибка исполнения запроса
                // $errors[] = 'Ошибка БД №' . $e->code . ', SQL:' . $e->sql;
                $errors[] = 'Ошибка БД: bindings=' . implode(',', $e->getBindings()) . ', SQL=' . $e->getSql();
                // $errors[] = "Ошибка базы данных";
            } catch (\Exception $e) {
                // dd($e);
                $errors[] = "Ошибка обработки запроса " . (isset($e->message) ? $e->message : '') . " in file " . (isset($e->file) ? $e->file : '') . ", line " . (isset($e->line) ? $e->line : '');
            }
        }
        // если все успешно сохранено
        if ($new_model) {
            // для выдачи результата
            $with_sub_tables = [];
            // обработаем подчиненные таблицы, при наличии
            if (isset($data["sub_tables"])) {
                foreach ($data["sub_tables"] as $sub_table) {

                    // метод извлечения табличной части
                    if (isset($sub_table["method"]) && method_exists($new_model, $sub_table["method"])) {
                        // id записей подчиненной таблицы, которые необходимо оставить
                        $existed_id = [];
                        // var_dump($sub_table);
                        $method = $sub_table["method"];
                        // передадим в ответе эту таблицу
                        $with_sub_tables[] = $method;
                        // есть ошибки сохранения в подчиненной таблице
                        $has_sub_table_save_errors = false;
                        // проходим по всем строкам таблицы
                        // print_r($sub_table["data"]);



                        // [{"id":102,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:42.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_id":46,"serial":"3000000000223","kolvo":"1.000","price":"1.0000","summa":"1.0000","is_producted":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"components":
                        // [{"id":306,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:49.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":102,"nomenklatura_id":305,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Материнская плата ASUS H360M-K2","replaces":[],"ed_ism":"шт"},
                        // {"id":307,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:49.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":102,"nomenklatura_id":301,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Процессор Intel Core i3","replaces":[],"ed_ism":"шт"},
                        // {"id":308,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:49.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":102,"nomenklatura_id":302,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Модуль памяти DDR-4 4Gb","replaces":[],"ed_ism":"шт"},
                        // {"id":309,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:49.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":102,"nomenklatura_id":303,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__SSD Kingstone 240Gb SATA-III","replaces":[],"ed_ism":"шт"},
                        // {"id":310,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:49.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":102,"nomenklatura_id":304,"kolvo":"0.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Windows 10 Home","replaces":[],"ed_ism":"шт"},
                        // {"id":311,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:49.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":102,"nomenklatura_id":306,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Сборка ПК","replaces":[],"ed_ism":"шт"},
                        // {"id":312,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:49.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":102,"nomenklatura_id":170,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Установка операционной системы Windows Home","replaces":[],"ed_ism":"шт"},
                        // {"id":320,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T14:01:49.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:49.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":102,"nomenklatura_id":308,"kolvo":"2.000","price":"0.0000","summa":"0.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Модуль памяти DDR-4 2Gb","replaces":[],"ed_ism":"шт"},
                        // {"id":321,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T14:01:49.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:49.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":102,"nomenklatura_id":307,"kolvo":"1.000","price":"0.0000","summa":"0.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"Linux Ubuntu","replaces":[],"ed_ism":"шт"},
                        // {"id":325,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T14:04:33.000000Z","created_by":2,"updated_at":"2021-06-03T06:33:49.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":102,"nomenklatura_id":207,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"1С:Бухгалтерия 8. 1С:Бухгалтерия 8.","replaces":[],"ed_ism":"шт"}]
                        // ,"nomenklatura":"0__ПЭВМ CPU Intel i3 / 8Gb RAM / 240 Gb SSD / Win10","production":{"id":46,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":2,"updated_at":"2021-06-03T06:33:42.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"doc_num":"2","doc_date":"2021-05-31","is_active":0,"sklad_id":7,"recipe_id":5,"kolvo":"2.000","permissions":{"show":1,"copy":1,"edit":1,"delete":1},"recipe":"0__ПЭВМ CPU Intel i3 / 8Gb RAM / 240 Gb SSD / Win10 (ПК + Win 10 Home)","sklad":"Производственный склад1","replaces":[{"id":2,"uuid":null,"name":"","comment":"","created_at":"2021-04-21T14:17:32.000000Z","created_by":2,"updated_at":"2021-06-03T06:33:29.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_id":46,"component_id":1,"nomenklatura_from_id":302,"nomenklatura_to_id":308,"kolvo_from":"1.000","kolvo_to":"2.000","save_to_recipe":1,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura_from":"0__Модуль памяти DDR-4 4Gb","nomenklatura_to":"0__Модуль памяти DDR-4 2Gb"},{"id":7,"uuid":null,"name":"","comment":"","created_at":"2021-04-21T14:17:32.000000Z","created_by":2,"updated_at":"2021-06-03T06:33:29.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_id":46,"component_id":1,"nomenklatura_from_id":304,"nomenklatura_to_id":307,"kolvo_from":"1.000","kolvo_to":"1.000","save_to_recipe":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura_from":"0__Windows 10 Home","nomenklatura_to":"Linux Ubuntu"}],"nomenklatura_id":300,"nomenklatura":"0__ПЭВМ CPU Intel i3 / 8Gb RAM / 240 Gb SSD / Win10","ed_ism":"шт","recipes":{"id":5,"uuid":null,"name":"ПК + Win 10 Home","comment":"","created_at":"2021-04-08T10:44:35.000000Z","created_by":2,"updated_at":"2021-04-08T10:57:23.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"nomenklatura_id":300,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"main_image":null,"nomenklatura":{"id":300,"uuid":null,"name":"0__ПЭВМ","comment":"","created_at":"2021-04-08T10:37:13.000000Z","created_by":2,"updated_at":"2021-04-08T10:38:55.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"doc_type_id":2,"ed_ism_id":2,"description":"CPU Intel i3 / 8Gb RAM / 240 Gb SSD / Win10","part_num":"","manufacturer_id":1,"artikul":"","price":"0.0000000000","nds_id":1,"is_usluga":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"main_image":null,"file_list":[],"groups":[{"id":173,"tag_id":82,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"tag":"ПК в сборе"}],"manufacturer":"","ed_ism":"шт","doc_type":"Продукция","ostatok":2,"avg_price":31811}}}},{"id":103,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:43.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_id":46,"serial":"3000000000224","kolvo":"1.000","price":"1.0000","summa":"1.0000","is_producted":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},
                        // "components":[
                        //     {"id":313,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:54.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":103,"nomenklatura_id":305,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Материнская плата ASUS H360M-K2","replaces":[],"ed_ism":"шт"},
                        //     {"id":314,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:54.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":103,"nomenklatura_id":301,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Процессор Intel Core i3","replaces":[],"ed_ism":"шт"},
                        //     {"id":315,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:55.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":103,"nomenklatura_id":302,"kolvo":"0.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Модуль памяти DDR-4 4Gb","replaces":[],"ed_ism":"шт"},
                        //     {"id":316,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:55.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":103,"nomenklatura_id":303,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__SSD Kingstone 240Gb SATA-III","replaces":[],"ed_ism":"шт"},
                        //     {"id":317,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:55.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":103,"nomenklatura_id":304,"kolvo":"0.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Windows 10 Home","replaces":[],"ed_ism":"шт"},
                        //     {"id":318,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:55.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":103,"nomenklatura_id":306,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Сборка ПК","replaces":[],"ed_ism":"шт"},
                        //     {"id":319,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:55.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":103,"nomenklatura_id":170,"kolvo":"1.000","price":"1.0000","summa":"1.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Установка операционной системы Windows Home","replaces":[],"ed_ism":"шт"},
                        //     {"id":322,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T14:01:49.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:55.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":103,"nomenklatura_id":308,"kolvo":"4.000","price":"0.0000","summa":"0.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"0__Модуль памяти DDR-4 2Gb","replaces":[],"ed_ism":"шт"},
                        //     {"id":323,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T14:01:49.000000Z","created_by":null,"updated_at":"2021-06-03T06:33:55.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_item_id":103,"nomenklatura_id":307,"kolvo":"1.000","price":"0.0000","summa":"0.0000","is_replaced":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura":"Linux Ubuntu","replaces":[],"ed_ism":"шт"}]
                        // ,"nomenklatura":"0__ПЭВМ CPU Intel i3 / 8Gb RAM / 240 Gb SSD / Win10","production":{"id":46,"uuid":null,"name":"","comment":"","created_at":"2021-05-31T13:59:57.000000Z","created_by":2,"updated_at":"2021-06-03T06:33:42.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"doc_num":"2","doc_date":"2021-05-31","is_active":0,"sklad_id":7,"recipe_id":5,"kolvo":"2.000","permissions":{"show":1,"copy":1,"edit":1,"delete":1},"recipe":"0__ПЭВМ CPU Intel i3 / 8Gb RAM / 240 Gb SSD / Win10 (ПК + Win 10 Home)","sklad":"Производственный склад1","replaces":[{"id":2,"uuid":null,"name":"","comment":"","created_at":"2021-04-21T14:17:32.000000Z","created_by":2,"updated_at":"2021-06-03T06:33:29.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_id":46,"component_id":1,"nomenklatura_from_id":302,"nomenklatura_to_id":308,"kolvo_from":"1.000","kolvo_to":"2.000","save_to_recipe":1,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura_from":"0__Модуль памяти DDR-4 4Gb","nomenklatura_to":"0__Модуль памяти DDR-4 2Gb"},{"id":7,"uuid":null,"name":"","comment":"","created_at":"2021-04-21T14:17:32.000000Z","created_by":2,"updated_at":"2021-06-03T06:33:29.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"production_id":46,"component_id":1,"nomenklatura_from_id":304,"nomenklatura_to_id":307,"kolvo_from":"1.000","kolvo_to":"1.000","save_to_recipe":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"nomenklatura_from":"0__Windows 10 Home","nomenklatura_to":"Linux Ubuntu"}],"nomenklatura_id":300,"nomenklatura":"0__ПЭВМ CPU Intel i3 / 8Gb RAM / 240 Gb SSD / Win10","ed_ism":"шт","recipes":{"id":5,"uuid":null,"name":"ПК + Win 10 Home","comment":"","created_at":"2021-04-08T10:44:35.000000Z","created_by":2,"updated_at":"2021-04-08T10:57:23.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"nomenklatura_id":300,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"main_image":null,"nomenklatura":{"id":300,"uuid":null,"name":"0__ПЭВМ","comment":"","created_at":"2021-04-08T10:37:13.000000Z","created_by":2,"updated_at":"2021-04-08T10:38:55.000000Z","updated_by":2,"deleted_by":null,"is_protected":0,"sync_1c_at":null,"deleted_at":null,"doc_type_id":2,"ed_ism_id":2,"description":"CPU Intel i3 / 8Gb RAM / 240 Gb SSD / Win10","part_num":"","manufacturer_id":1,"artikul":"","price":"0.0000000000","nds_id":1,"is_usluga":0,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"main_image":null,"file_list":[],"groups":[{"id":173,"tag_id":82,"permissions":{"show":1,"copy":1,"edit":1,"delete":1},"tag":"ПК в сборе"}],"manufacturer":"","ed_ism":"шт","doc_type":"Продукция","ostatok":2,"avg_price":31811}}}}]

                        // если переданы строки накладной
                        if (count($sub_table["data"]) > 0) {
                            // получим id-шники записей из переданного массива
                            $existed_db_id = collect($sub_table["data"])->pluck('data.id')->all();
                            // получим id-шники записей, которые есть в БД, но нет в переданном списке
                            $delete_id = $new_model->$method()->whereNotIn('id', $existed_db_id)->pluck('id')->all();

                            foreach ($sub_table["data"] as $sub_table_data) {
                                // получим класс модели подчиненной таблицы
                                if (isset($sub_table["item_class"])) {
                                    $class = $sub_table["item_class"];
                                } else {
                                    $class = $this_model->item_class();
                                }
                                // экземпляр класса модели
                                $sub_table_model = new $class();
                                // если создан экземпляр класса
                                if ($sub_table_model) {
                                    // print_r($sub_table_model->toArray());
                                    // print_r($sub_table_data);
                                    // если запись удалена
                                    if (isset($sub_table_data["data"]["deleted"]) && boolval($sub_table_data["data"]["deleted"]) === true) {
                                        // если запись есть - ее нужно удалить
                                        if (isset($sub_table_data["data"]["id"])) {
                                            $sub_table_model->find($sub_table_data["data"]["id"])->delete();
                                        }
                                    } else {
                                        // запись нужно создавать
                                        // // данные для сохранения
                                        // $data_for_recursive_save = array_merge($sub_table, ['data'=>$sub_table_data]);
                                        // // сохраняем строку
                                        // $res_save_sub_table_row = $sub_table_model->save_recursive($request, $data_for_recursive_save);
                                        $res_save_sub_table_row = $sub_table_model->save_recursive($request, $sub_table_data);
                                        // print_r($sub_table_data);
                                        // var_dump($res_save_sub_table_row);
                                        // если есть ошибки
                                        if ($res_save_sub_table_row["is_error"]) {
                                            $errors = array_merge($errors, $res_save_sub_table_row["errors"]);
                                        }
                                        // если получили сохраненную модель
                                        if ($res_save_sub_table_row["res"]) {
                                            // полученная модель подчиненной таблицы
                                            $sub_table_item_model = $res_save_sub_table_row["res"];
                                            // если передан метод связи с родительской таблицей
                                            if (isset($sub_table["belongs_method"]) && method_exists($sub_table_item_model, $sub_table["belongs_method"])) {
                                                // ассоциируем сохраненную запись с родительской моделью
                                                $belongs_method = $sub_table["belongs_method"];
                                                $sub_table_item_model->$belongs_method()->associate($new_model)->save();
                                                // не будем удалять эту запись
                                                $existed_id[] = $sub_table_item_model->id;
                                            } else {
                                            }
                                        } else {
                                            // модель не передана - ошибка сохранения
                                            $has_sub_table_save_errors = true;
                                        }
                                    }
                                    unset($sub_table_model);
                                }
                            }
                            // если все записи подчиненной таблицы успешно сохранены
                            if (!$has_sub_table_save_errors) {
                                if ($mod_type != 'add') {
                                    // удаляем все непереданные id
                                    $to_delete_id = $new_model->$method()->whereNotIn('id', $existed_id)->pluck('id');
                                    foreach ($to_delete_id as $del_id) {
                                        $del_item = $new_model->$method()->where('id', $del_id)->first();
                                        if ($del_item) $del_item->delete();
                                    }
                                }
                                // удаляем непереданные id-шники
                                foreach ($delete_id as $del_id) {
                                    $del_item = $new_model->$method()->where('id', $del_id)->first();
                                    if ($del_item) $del_item->delete();
                                }
                            }
                            $result_data[$method] = $new_model->$method()->get();
                            // $result_data[$method] = $new_model->$method()->first()->toArray();
                            // print_r($result_data[$method]);
                        }
                    } else {
                        // не обрабатываем эти данные
                    }
                }
            }
            // $new_model = $new_model->makeVisible(['select_list_title'])->with($with_sub_tables);
        } else {
            // удалить файлы
            foreach ($data["files"] as $file_name => $file_settings) {
                $disk->delete_file($data["data"][$file_name], $data["data"][$file_name]);
            }
        }
        // } else {
        //     $errors[] = "Не достаточно прав для ".$action_title." записи в ".$this_model->table_title();
        // }


        // данные
        if ($new_model) {
            $result_data = array_merge($new_model->makeVisible(['select_list_title'])->toArray(), $result_data);
        } else {
            $result_data = null;
        }
        // print_r($result_data);

        // результат сохранения
        return [
            "is_error" => count($errors) > 0,
            "errors" => $errors,
            "res" => $new_model,
            "data" => $result_data
        ];
    }

    // формализуем данные для вставки или изменения записи
    public function formalize_data_from_request(Request $request = null, $replaces = [], $mod_type = 'add')
    {
        // возвращаем данные в формате
        // return [
        //     "data" => $data,                 данные для заполнения модели значениями
        //     "sub_tables" => $sub_table=> [   подчиненные таблицы
        //          'table' => 'sklad_receive_items'    имя таблицы БД
        //          'class' => 'SkladReceive'           класс экземпляра
        //          'method' => 'items'                 метод извлечения/изменения записей
        //          'title' => 'Позиции накладной'      тайтл подчиненной таблицы
        //          'data' => '[1,2]'                   сырые данные
        //      ]
        //     "errors" => $errors=> [          ошибки
        //                  "require" => [],        массив незаполненых обязательных полей
        //                  "invalid" => []         массив полей с некорректным типом данных
        //      ]
        //     "files" => $files => [           файлы в формате
        //                  "type"                  тип (file, image) и т.п.
        //                  "filename"             имя файла для копирования и сохранения в БД (временный файл)
        //                  "extension"             расширение файла
        //                  "require"               признак обязательного поля
        //      ]
        //     "is_error" => $is_error          наличие ошибки
        // ];

        // столбцы, существующие в моделе
        $valid_fields = [];
        // возвращаемые данные
        if ($request) {
            $data = array_merge($request->all(), $replaces);
        } elseif (count($replaces) > 0) {
            $data = $replaces;
        }
        if (isset($data)) {
            // текущий пользователь
            $user = auth()->user()->id;
            // ошибки
            $errors = [
                "require" => [],    // не заполнены обязательные поля
                "invalid" => []     // некорректный тип данных
            ];
            // есть ошибка
            $is_error = false;

            // файлы
            $files = [];
            // проверять обязательные поля (для вставки)
            $check_require = $mod_type == 'add' ? true : false;

            // обработки служебных полей
            $ignore_fields = ["uuid", "sync_1c_at"];
            switch ($mod_type) {
                    // добавление записи
                case 'add': {
                        $data["created_by"] = $user;
                        $data["is_protected"] = 0;
                        // поля для игнорирования
                        $ignore_fields = array_merge($ignore_fields, ["id", "updated_by", "updated_at", "deleted_by", "deleted_at"]);
                    }
                    break;
                case 'copy': {
                        if (isset($data["uuid"])) unset($data["uuid"]);
                        $data["created_by"] = $user;
                        $data["is_protected"] = 0;
                        // поля для игнорирования
                        $ignore_fields = array_merge($ignore_fields, ["id", "updated_by", "updated_at", "deleted_by", "deleted_at"]);
                    }
                    break;
                    // редактирование записи
                case 'edit': {
                        $data["updated_by"] = $user;
                        // поля для игнорирования
                        $ignore_fields = array_merge($ignore_fields, ["created_by", "created_at", "deleted_by", "deleted_at", "is_protected"]);
                    }
                    break;
            }
            // обработка запроса в соответствии с моделью
            foreach ($this->model() as $field) {
                // нужно ли проверять обязательное поле?
                // $check_require = false;
                if (isset($field["require"]) && $mod_type != 'edit') {
                    if (is_array($field["require"])) {
                        $check_require = in_array($mod_type, $field["require"]);
                    } else {
                        $check_require = $field["require"];
                    }
                }

                // валидные столбцы (существующие в моделе)
                if ($field["type"] == 'morph') {
                    $valid_fields[] = $field["name"] . "_id";
                    $valid_fields[] = $field["name"] . "_type";
                } else {
                    $valid_fields[] = $field["name"];
                }

                // игнорируем поля в соответствии с типом запроса
                if (in_array($field["name"], $ignore_fields)) {
                    unset($data[$field["name"]]);
                    continue;
                }
                // игнорируем виртуальные поля (читатели)
                if (isset($field["virtual"]) && $field["virtual"]) {
                    unset($data[$field["name"]]);
                    continue;
                }
                // игнорируем поля, которые служат только для проведения документа
                if (isset($field["post"]) && $field["post"]) {
                    unset($data[$field["name"]]);
                    continue;
                }

                // если поле обязательное
                if ($check_require && !isset($data[$field["name"]])) {
                    switch ($field["type"]) {
                        case "image":
                        case "file":
                        case "document": {
                                if (!$request->file($field["name"])) {
                                    $errors["require"][] = $field["name"];
                                    $is_error = true;
                                }
                            }
                            break;
                            // полиморфы
                        case 'morph': {
                                if (!isset($data[$field["name"] . "_id"]) && !isset($data[$field["name"] . "_type"]) /*&& $data[$field["name"] . "_type"]*/) {
                                    $errors["require"][] = $field["name"];
                                    $is_error = true;
                                }
                            }
                            break;
                        default: {
                                // если есть дефолтное значение - укажем его
                                if (isset($field["default"])) {
                                    $data[$field["name"]] = $field["default"];
                                } else {
                                    $errors["require"][] = $field["name"];
                                    $is_error = true;
                                }
                            }
                    }
                }
                // дополнительные обработки в соответствии с типом поля
                if (isset($data[$field["name"]]) || $field["type"] == "morph") {
                    switch ($field["type"]) {
                            // файлы и изображения
                        case "image":
                        case "file": {
                                if ($mod_type == 'add' || $mod_type == 'copy') {
                                    if ($request->file($field["name"])) {
                                        // определим расширение
                                        $extension = $request->file($field["name"])->extension();
                                        // сгенерим uuid имени файла
                                        $file_name = (string) Str::uuid() . "." . $extension;
                                        // добавим в массив файлов
                                        $files[$field["name"]] = [
                                            "type" => $field["type"], // тип файл, картинка и т.п.
                                            "filename" => $file_name, // имя файла для копирования и сохранения в БД
                                            "extension" => $extension, // расширение файла
                                            "require" => (isset($field["require"]) && $field["require"] == true) ? true : false // обязательное поле
                                        ];
                                        // добавим значение
                                        $data[$field["name"]] = $file_name;
                                    }
                                } else {
                                    // при изменении
                                    // если передан новый файл
                                    if ($request->file($field["name"])) {
                                        // определим расширение
                                        $extension = $request->file($field["name"])->extension();
                                        // сгенерим uuid имени файла
                                        $file_name = (string) Str::uuid() . "." . $extension;
                                        // добавим в массив файлов
                                        $files[$field["name"]] = [
                                            "type" => $field["type"], // тип файл, картинка и т.п.
                                            "filename" => $file_name, // имя файла для копирования и сохранения в БД
                                            "extension" => $extension, // расширение файла
                                            "require" => (isset($field["require"]) && $field["require"] == true) ? true : false // обязательное поле
                                        ];
                                    } else {
                                        // в параметре $field["name"] по соглашению должен быть передан null
                                        // тогда файл необходимо удалить из ФС и очистить поле
                                        // иначе ничего не изменяем (и не передаем)
                                        if (isset($data[$field["name"]]) && $data[$field["name"]]) {
                                            // файл остается без изменений
                                            unset($data[$field["name"]]);
                                        } else {
                                            // удаляем файл
                                            $files[$field["name"]] = null;
                                            $data[$field["name"]] = null;
                                        }
                                    }
                                }
                            }
                            break;
                            // телефонные номера
                        case "phone": {
                                $data[$field["name"]] = preg_replace("/[^0-9]/", '', $data[$field["name"]]);
                            }
                            break;
                        case 'morph': {
                                // var_dump(isset($data[$field["name"]."_id"]), intVal($data[$field["name"]."_id"])>1, isset($data[$field["name"]."_type"]), (is_null($data[$field["name"]."_type"]) || strval($data[$field["name"]."_type"])!='null'));
                                if (isset($data[$field["name"] . "_id"]) && intVal($data[$field["name"] . "_id"]) > 1  && (is_null($data[$field["name"] . "_type"]) || strval($data[$field["name"] . "_type"]) != 'null')) {
                                } else {
                                    unset($data[$field["name"] . "_id"]);
                                    unset($data[$field["name"] . "_type"]);
                                }
                            }
                            break;
                            // case "string": {
                            //     if ($mod_type=='edit') {
                            //         var_dump($field);
                            //         if (isset($data[$field["name"]])) $data[$field["name"]] = '';
                            //     }
                            // } break;
                    }
                }
                // проверяем типы данных
                if (array_key_exists($field["name"], $data)) {
                    $norm_val = ABPField::checkType($field["type"], $data[$field["name"]]);
                    if ($norm_val !== false) {
                        // if ($field["name"]=="npp") dd($field, array_key_exists($field["name"], $data), $norm_val);
                        $data[$field["name"]] = $norm_val;
                    } else {
                        unset($data[$field["name"]]);
                    }
                }
            }

            // подчиненные таблицы
            $ext = $this->get_extensions();
            if (isset($ext["sub_tables"])) {
                $sub_tables = [];
                foreach ($ext["sub_tables"] as $sub_table => $sub_table_props) {
                    // если переданы данные подчиненной таблицы
                    if (isset($data[$sub_table_props["method"]])) {
                        // если данные уже в виде массива
                        if (is_array($data[$sub_table_props["method"]])) {
                            $sub_table_data_request = $data[$sub_table_props["method"]];
                        } else {
                            // данные в JSON
                            $sub_table_data_request = json_decode($data[$sub_table_props["method"]], true);
                        }
                        // если указан метод
                        if (isset($data[$sub_table_props["method"]])) {
                            $sub_table_data = [];
                            // получим экземпляр класса модели подчиненной таблицы
                            if (isset($sub_table_props["item_class"])) {
                                $sub_model = $sub_table_props["item_class"];
                                $sub_table_model = new $sub_model;
                                if ($sub_table_model) {
                                    // удаленные записи не будем добавлять
                                    foreach ($sub_table_data_request as $item) {
                                        if (!isset($item["deleted"]) || $item["deleted"] == false) {
                                            // формализуем данные для подчиненной таблицы в зависимости от типа редактирования
                                            if (isset($item["id"])) {
                                                $item_data = $sub_table_model->formalize_data_from_request(null, $item, $mod_type);
                                            } else {
                                                $item_data = $sub_table_model->formalize_data_from_request(null, $item);
                                            }
                                            if ($item_data["is_error"]) {
                                                // добавим ошибки сразу в родительский массив
                                                $errors = array_merge_recursive($errors, $item_data["errors"]);
                                            }
                                            // добавляем нормализованные данные строки
                                            $sub_table_data[] = $item_data;
                                        }
                                    }
                                    // добавляем нормализованные данные подчиненной таблицы
                                    $sub_tables[$sub_table] = array_merge($sub_table_props, ["data" => $sub_table_data]);
                                }
                            } else {
                                // не передан item_class в модели - просто отдаем массив (старый метод добавления данных)
                                // удаленные записи не будем добавлять
                                foreach ($sub_table_data_request as $item) {
                                    if (!isset($item["deleted"]) || $item["deleted"] == false) {
                                        $sub_table_data[] = $item;
                                    }
                                }
                                $sub_tables[$sub_table] = array_merge($sub_table_props, ["data" => $sub_table_data]);
                            }
                        }
                    }
                }
            }

            // передаем только существующие в модели значения
            $res = [];
            foreach ($valid_fields as $valid_field) {
                if (array_key_exists($valid_field, $data)) {
                    $res[$valid_field] = $data[$valid_field];
                }
            }
            // dd($data);
            // var_dump($res);
            return [
                "data" => $res,
                "sub_tables" => count($sub_tables) > 0 ? $sub_tables : null,
                "errors" => $errors,
                "files" => $files,
                "is_error" => $is_error
            ];
        } else {
            return null;
        }
    }

    // добавление подчиненных таблиц
    public function sub_tables($sub_tables = [])
    {
        if (count($sub_tables) == 0) {
            return $this->sub_tables;
        } else {
            $this->sub_tables = array_merge($this->sub_tables, $sub_tables);
            return;
        }
    }

    // есть подчиненные таблицы
    public function has_sub_tables()
    {
        if (count($this->sub_tables()) > 0) return true;
        else return false;
    }

    // // добавление столбцов к списку заполнения и выдача списка
    public function fill_fields($fillable = [])
    {
        if (count($fillable) == 0) {
            return $this->fillable;
        } else {
            $this->fillable = array_merge($this->fillable, $fillable);
            return;
        }
    }

    // добавление модели таблицы к общей и выдача модели
    public function model($model = [])
    {
        if (count($model) == 0) {
            $model = $this->model;
            // добавим поля в зависимости от типа таблицы
            switch ($this->table_type()) {
                case 'catalog': {
                        $model[] = ["name" => "name", "name_1c" => "Description", "type" => "string", "max" => 1024, "title" => "Наименование", "require" => true, "default" => '', "index" => "index", "show_in_table" => true, "out_index" => 0];
                        $model[] = ["name" => "comment", "type" => "string", "max" => 255, "title" => "Комментарий", "require" => false, "default" => '', "out_index" => 1000, "show_in_table" => false];
                    }
                    break;
                case 'document': {
                        $model[] = ["name" => "doc_num", "name_1c" => "Number", "type" => "string", "title" => "№ документа", "require" => ["edit"], "default" => "", "index" => "index", "show_in_table" => true, "out_index" => 1, "show_in_form" => ["edit"]];
                        $model[] = ["name" => "doc_date", "name_1c" => "Date", "type" => "date", "title" => "Дата документа", "require" => true, "default" => date("Y-m-d"), "index" => "index", "show_in_table" => true, "out_index" => 2];
                        $model[] = ["name" => "is_active", "name_1c" => "Posted", "type" => "boolean", "title" => "Проведен", "require" => true, 'default' => 0, "index" => "index", "show_in_form" => false, "post" => true];
                        $model[] = ["name" => "comment", "type" => "string", "max" => 255, "title" => "Комментарий", "require" => false, "default" => '', "out_index" => 1000, "show_in_table" => false];
                    }
                    break;
                case 'sub_table': {
                    }
                    break;
                case 'register': {
                    }
                    break;
                case 'report': {
                    }
                    break;
            }
            // модифицируем модель, если передан преобразователь
            if ($this->modModel) {
                foreach ($this->modModel as $modField) {
                    if (isset($modField["name"])) {
                        $model_collection = collect($model);
                        $mod_model = $model_collection->map(function ($item) use ($modField) {
                            $fieldName = $modField["name"];
                            if ($item["name"] == $fieldName) {
                                foreach ($modField as $key => $value) {
                                    $item[$key] = $value;
                                }
                            }
                            return $item;
                        });
                        $model = $mod_model->values()->all();
                    }
                }
            }
            // сортируем поля ввода
            $model_collection = collect($model);
            $sorted_model = $model_collection->sortBy(function ($field) {
                if (isset($field["out_index"])) {
                    $order_num = intVal($field["out_index"]);
                } else {
                    if (isset($field["require"]) && $field["require"]) {
                        $order_num = 0;
                    } else {
                        $order_num = 1000;
                    }
                }
                return $order_num;
            });
            return $sorted_model->values()->all();
        } else {
            $this->model = array_merge($this->model, $model);
            return;
        }
    }

    // выдаем расширения таблицы (файлы, картинки и т.п.)
    public function get_extensions()
    {
        // текущий пользователь
        $user = Auth::user();
        $res = [];
        // свойства таблицы
        $props = [
            "app_model" => get_class($this),
            "table_type" => $this->table_type(),
            "titles" => [
                "table" => $this->title(),
                "add" => "Создание записи в " . $this->title(),
                "edit" => "Редактирование записи в " . $this->title(),
                "copy" => "Копирование записи в " . $this->title(),
            ],
            "icon" => $this->icon(),
            // печатные формы
            "printable" => method_exists($this, 'pf_data') && $user->can('viewAny', $this)
        ];
        $res["props"] = $props;
        // стандартные расширения (файлы и т.п.)
        $table_extensions = ['has_files', 'has_images', 'has_file_list', 'has_groups', 'has_series', 'has_sub_series'];
        foreach ($table_extensions as $table_extension) {
            $res[$table_extension] = $this->$table_extension;
        }
        // подчиненные таблицы
        $sub_tables = [];
        if ($this->sub_tables()) {
            foreach ($this->sub_tables() as $table) {
                if (isset($table["table"])) $sub_tables[$table["table"]] = $table;
            }
        }
        $res["sub_tables"] = $sub_tables;
        // права доступа
        $res["permissions"] = [
            "add" => (int)$user->can('create', $this),
            "view" => (int)$user->can('viewAny', $this),
        ];


        return $res;
    }

    public function has_items()
    {
        if ($this->sub_tables()) {
            foreach ($this->sub_tables() as $table) {
                if (isset($table["method"]) && $table["method"] == "items") return true;
            }
        }
    }

    // геттер родительской таблицы
    public function get_key_table()
    {
        foreach ($this->model() as $field) {
            if ($field["type"] == "key") {
                return $field;
            }
        }
        return null;
    }

    // тип таблицы
    public function table_type($table_type = null)
    {
        if ($table_type === null) {
            return $this->table_type;
        } else {
            $valid_types = [
                "catalog",      // справочник
                "document",     // документ
                "sub_table",    // подчиненная таблицы в документе
                "report",       // отчет
            ];
            if (in_array($table_type, $valid_types)) $this->table_type = $table_type;
            return;
        }
    }

    // геттер признака подчиненности таблицы
    public function is_sub_table($is_sub_table = false)
    {
        if ($this->table_type === "sub_table") {
            return true;
        } else {
            return false;
        }
    }

    // выдать все таблицы модели
    public function get_tables()
    {
        return $this->tables;
    }

    // получить все значения по умолчанию
    public function get_default_values()
    {
        $res = [];

        foreach ($this->model() as $field) {
            if (isset($field["default"])) {
                $res[$field["name"]] = $field["default"];
            } else {
                $res[$field["name"]] = null;
            }
        }

        return $res;
    }

    // выдать поля для синхронизации с 1С - только содержащиеся в 1с сущности
    public function fields_1c()
    {
        $res = [];
        // для справочника
        switch ($this->table_type) {
            case "catalog": {
                    foreach ($this->model as $field) {
                        if (isset($field["name_1c"])) {
                            $res_field = ["name" => $field["name"], "name_1c" => $field["name_1c"], "type" => $field["type"]];
                            if (isset($field["table"])) {
                                $res_field["table"] = $field["table"];
                                if ($field["table"] != "polymorph") {
                                    if (isset($this->tables[$field["table"]]["Class"])) $res_field["table_class"] = $this->tables[$field["table"]]["Class"];
                                    if (isset($this->tables[$field["table"]]["Name1C"])) $res_field["table_name_1c"] = $this->tables[$field["table"]]["Name1C"];
                                    $res_field["table_title"] = $this->tables[$field["table"]]["Title"];
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
                    foreach ($this->model as $field) {
                        if (!in_array($field["name"], $except_fields) && isset($field["name_1c"])) {
                            $res_field = ["name" => $field["name"], "name_1c" => $field["name_1c"], "type" => $field["type"]];
                            if (isset($field["table"])) {
                                if (isset($this->tables[$field["table"]]["Class"])) $res_field["table_class"] = $this->tables[$field["table"]]["Class"];
                                if (isset($this->tables[$field["table"]]["Name1C"])) $res_field["table_name_1c"] = $this->tables[$field["table"]]["Name1C"];
                                $res_field["table_title"] = $this->tables[$field["table"]]["Title"];
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
                    foreach ($this->model as $field) {
                        if (!in_array($field["name"], $except_fields) && isset($field["name_1c"])) {
                            $res_field = ["name" => $field["name"], "name_1c" => $field["name_1c"], "type" => $field["type"]];
                            if (isset($field["table"])) {
                                if (isset($this->tables[$field["table"]]["Class"])) $res_field["table_class"] = $this->tables[$field["table"]]["Class"];
                                if (isset($this->tables[$field["table"]]["Name1C"])) $res_field["table_name_1c"] = $this->tables[$field["table"]]["Name1C"];
                                $res_field["table_title"] = $this->tables[$field["table"]]["Title"];
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

    // выдать id по uuid из таблицы $table
    public function get_id_by_uuid($table, $uuid)
    {
        // результат
        $res = null;
        // создадим экземпляр таблицы
        $table = $this->new_table($table);
        if ($table) {
            $res_data = $table->where("uuid", $uuid)->first();
            if ($res_data) {
                // значение найдено
                $res = $res_data->id;
            }
        }
        return $res;
    }

    // выдать uuid по id на основании поля, возвращенного функцией fields_1c
    private function get_uuid_by_id($table_name, $id)
    {
        // создадим экземпляр таблицы
        $table = $this->new_table($table_name);
        $res_data = $table->find($id);
        if ($res_data) {
            // значение найдено
            return $res_data->uuid;
        } else {
            // значение не найдено
            return false;
        }
    }


    // устанавливаем или выдаем значение uuid из кэша по id (table - имя таблицы в БД)
    private function cache($table, $uuid = false, $id = false)
    {
        $cache_time_remember = 30;
        $uuid_key = "uuids_" . $table . "_" . $uuid;
        $id_key = "ids_" . $table . "_" . $id;
        if ($uuid && $id) {
            // устанваливаем значение
            $uuid_key = "uuids_" . $table . "_" . $uuid;
            $id_key = "ids_" . $table . "_" . $id;
            Cache::put($uuid_key, $id, $cache_time_remember);
            Cache::put($id_key, $uuid, $cache_time_remember);
            return;
        } else {
            // читаем значение
            if ($uuid) {
                // выдаем id
                return Cache::get($uuid_key, null);
            } elseif ($id) {
                // выдаем uuid
                return Cache::get($id_key, null);
            } else {
                return null;
            }
        }
    }

    // выдаем имя таблицы БД по имени столбца 1С
    public function get_table_name_by_polyfield_name_1c($field_name, $row)
    {
        $field_id["db"] = $field_name;
        $field_type["db"] = substr_replace($field_name, "type", -2);
        $table = null;
        foreach ($this->model() as $field) {
            if ($field["name"] == $field_type["db"]) {
                $table_name_1c_search = $field["name_1c"];
                $field_type["1c"] = $table_name_1c_search;
                $field_type["val"] = substr($row->$table_name_1c_search, 14);
                foreach ($this->tables as $t => $params) {
                    if (isset($params["Name1C"])) {
                        if ($params["Name1C"] == $field_type["val"]) {
                            $table["table"] = $this->tables[$t];
                            break;
                        }
                    }
                }
            }
            if ($field["name"] == $field_id["db"]) {
                $field_id["1c"] = $field["name_1c"];
                $field_id["val"] = $row->{$field["name_1c"]};
            }
        }
        $table["field_id"] = $field_id;
        $table["field_type"] = $field_type;
        return $table;
    }

    // выдаем полиморфную таблицу по имени столбца
    public function get_table_name_1c_by_polyfield($field_name, $row)
    {

        // поле table_id в обозначениях БД
        $field_id["db"] = $field_name;
        // поле table_type в обозначениях БД
        $field_type["db"] = substr_replace($field_name, "type", -2);
        // имя класса таблицы table_type
        $poly_table_class = $row->{$field_type["db"]};
        // экземпляр класса таблицы table_type
        $poly_table_item = new $poly_table_class();
        // передадим таблицу, используемую в качестве полиморфной
        if ($poly_table_item) {
            $table["table"] = $this->tables[$poly_table_item->table()];
        }

        // найдем наименование столбца table_id в 1С
        foreach ($this->model() as $field) {
            if (isset($field["name_1c"]) && $field["name"] == $field_id["db"]) {
                $field_id["1c"] = $field["name_1c"];
                break;
            }
        }
        // найдем наименование столбца table_type в 1С
        foreach ($this->model() as $field) {
            if (isset($field["name_1c"]) && $field["name"] == $field_type["db"]) {
                $field_type["1c"] = $field["name_1c"];
                break;
            }
        }

        $table["field_id"] = $field_id;
        $table["field_type"] = $field_type;
        return $table;
    }

    // выдаем класс по имени таблицы
    public function new_table($table_name)
    {
        $res = null;
        if (isset($this->tables[$table_name]["Class"])) {
            // $class_name = "App\\".$this->tables[$table_name]["Class"];
            $class_name = $this->tables[$table_name]["Class"];
            $res = new $class_name();
        }
        return $res;
    }
    // выдаем имя таблицы 1С по имени таблицы
    public function get_name_1c($table_name)
    {
        $res = null;
        if (isset($this->tables[$table_name]["Name1C"])) $res = $this->tables[$table_name]["Name1C"];
        return $res;
    }

    // добавляем запись в 1С на основании id
    public function sync_by_id($id)
    {
        // результат
        $res = null;
        // нет ошибок
        $is_ok = false;
        // определим текущий момент
        $now = DB::raw("NOW()");
        // получим данные из БД
        $data_db = $this->find($id);
        // если есть данные
        // dd($data_db);
        if ($data_db) {
            // соединение c 1C
            $http = new API1C();
            // удалена ли запись
            $is_deleted = $data_db->trashed();
            // есть запись в 1С
            $has_record_in_1c = false;
            // если есть uuid в модели - попытаемся найти запись в 1С
            if ($data_db->uuid) {
                // получаем запись из справочника из 1С
                $data_1c = $http->is_exists($this->name_1c(), $data_db->uuid);
                // $data_1c = $client->{$this->name_1c()}->get($data_db->uuid)->first();
                if ($data_1c) $has_record_in_1c = true;
            }
            // dd($has_record_in_1c);
            // если нашли запись
            if ($has_record_in_1c) {
                // проверим, нужно ли ее изменять
                if ($this->table_type() == "document" && property_exists($data_1c, "Posted") && (bool)$data_1c->Posted == false) {
                    if ($this->need_update_1c($id)) {
                        // нужно изменять
                        // массив значениями модели
                        $new_values = $this->formalize_data_for_1c($data_db, $id, "patch");
                        // здесь запрос PATCH
                        // var_dump($data_db->uuid);
                        // echo "UPDATE";
                        // var_dump($new_values);
                        // dd($new_values);
                        // dd(json_encode($new_values));
                        $response_from_1c = $http->update($this->name_1c(), $data_db->uuid, $new_values["data"], 'patch');
                        if (!$response_from_1c["is_error"]) {
                            $is_ok = true;
                            switch ($response_from_1c["code"]) {
                                case 200: {
                                        // если ок - изменить время синхронизации с 1с в записи
                                        if (isset($response_from_1c["data"])) {
                                            $res = $response_from_1c["data"];
                                        }
                                        $data_db->sync_1c_at = $now;
                                        $data_db->save();
                                    }
                                case 412: {
                                        // Для оптимистической блокировки данных (т.е. для проверки, что данные не изменились с момента считывания) нужно использовать заголовок If-Match HTTP-запроса, связанного с модификацией (PATCH) или удалением (DELETE) данных. В качестве значения заголовка должно выступать значение свойства DataVersion, которое получено при предварительном чтении сущности.
                                        // Механизм работает следующим образом:
                                        // ● При запросе экземпляра сущности среди прочих возвращается свойство DataVersion. При запросе набора сущностей с каждой из сущностей набора возвращается свойство DataVersion.
                                        // ● Клиентское приложение должно сохранить у себя это значение версии объекта и затем использовать его в запросах PATCH и DELETE, передавая его в заголовке If-Match.
                                        // ● Если значение свойства DataVersion в момент исполнения запроса совпадает со значением заголовка If-Match, то происходит запрошенное действие. В противном случае действие не выполняется, а клиенту возвращается HTTP статус 412.
                                    }
                                    break;
                            }
                        }
                        // var_dump($response_from_1c);
                    }
                }
            } else {
                // добавляем

                $new_values = $this->formalize_data_for_1c($data_db, $id, "post");
                // echo "INSERT";
                // var_dump($new_values);
                // здесь запрос POST
                $response_from_1c = $http->post($this->name_1c(), $new_values["data"]);
                // var_dump($response_from_1c);
                if (!$response_from_1c["is_error"]) {
                    $is_ok = true;
                    switch ($response_from_1c["code"]) {
                        case 201: {
                                // created
                                // обновим уид и время синхронизации с 1С
                                $inserted_row_1c = $response_from_1c["data"];
                                $res = $inserted_row_1c;
                                $data_db->uuid = $inserted_row_1c->Ref_Key;
                                $data_db->sync_1c_at = $now;
                                $data_db->save();
                            }
                            break;
                    }
                }
                // var_dump($response_from_1c);
            }
            // дополнительные синхронизации - селекты, в т.ч. полиморфные таблицы
            if ($is_ok && isset($new_values["sync_data"])) {
                // если были полиморфные поля или не удалось вставить записи в родительские таблицы - нужно обновить uuid-ы
                foreach ($new_values["sync_data"] as $sync_data) {
                    // table_source - таблица из которой нужно взять uuid после записи
                    // id_source - id в таблице из которой возьмем uuid
                    // table_target - таблица в которую нужно будет вставить полученный uuid
                    // field_target - поле в которое нужно будет вставить полученный uuid
                    // table_target_id - идентификатор записи таблицы в которую вставляем полученный uuid
                    // target_class_field - поле в которое вставляем класс таблицы
                    // target_class_val - значение класса
                    // var_dump($sync_data);
                    $source_table = $this->new_table($sync_data["table_source"]);
                    $res_sync_data = $source_table->sync_by_id($sync_data["id_source"]);
                    // var_dump($res_sync_data);
                    if ($res_sync_data && $res_sync_data->Ref_Key) {
                        $target_table = $this->new_table($sync_data["table_target"]);
                        $target_row = $target_table->find($sync_data["table_target_id"]);
                        if ($target_row) {
                            $target_uuid = $target_table->get_uuid_by_id($sync_data["table_target"], $sync_data["table_target_id"]);
                            if ($target_uuid) {
                                $target_data = [$sync_data["field_target"] => $res_sync_data->Ref_Key];
                                if (isset($sync_data["target_class_field"]) && isset($sync_data["target_class_val"])) {
                                    $target_data[$sync_data["target_class_field"]] = $sync_data["target_class_val"];
                                }
                                $sync_res = $http->update($target_table->name_1c(), $target_uuid, $target_data, 'patch');
                                if (!$sync_res) {
                                    var_dump("Update fail", $target_table->name_1c(), $target_uuid, $target_data);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // не найдено
        }
        return $res;
    }

    // синхронизирум подчиненные таблицы из 1с в бд
    private function sync_sub_tables($data_1c)
    {
        if ($this->id) {
            // id ключевого поля
            $key_field_value_id = $this->id;
            // определим текущий момент
            $now = DB::raw("NOW()");
            if ($this->has_sub_tables()) {
                // поиск всех таблиц, которые можно синхронизировать
                foreach ($this->sub_tables() as $sub_table) {
                    // если переданы данные для синхронизации - есть аналогичная таблица в БД
                    if (isset($sub_table["name"]) && isset($sub_table["name_1с"])) {
                        // имя таблицы 1С - $sub_table["name_1с"]
                        $sub_table_name_1c = $sub_table["name_1с"];
                        // если есть данные в 1с
                        if (count($data_1c->$sub_table_name_1c) > 0) {
                            // источник полей - в таблице $sub_table["name"]
                            // класс для извлечения данных - $sub_table["class"] и метод $sub_table["method"]
                            $sub_table_item = $this->new_table($sub_table["name"]);
                            if ($sub_table_item) {
                                foreach ($data_1c->$sub_table_name_1c as $sub_data_1c) {
                                    // массив значениями модели
                                    $sub_values = $sub_table_item->formalize_data_for_db($sub_data_1c, $key_field_value_id);
                                    // служебные поля
                                    $sub_values["sync_1c_at"] = $now;
                                    // нужно сохранять
                                    $need_save = false;
                                    // найденная запись
                                    $sub_finded_row = $sub_table_item->is_exists($sub_values);
                                    // если есть запись
                                    if ($sub_finded_row) {
                                        // проверим, нужно ли ее изменять
                                        if ($sub_table_item->is_modified($sub_values)) {
                                            // нужно изменять
                                            $sub_values["updated_by"] = 1;
                                            $need_save = true;
                                        }
                                    } else {
                                        // добавляем
                                        $sub_values["created_by"] = 1;
                                        $need_save = true;
                                        // создаем новый класс
                                        $sub_finded_row = $sub_table_item->new_table($sub_table_item->table());
                                    }
                                    // если нужно сохранять
                                    if ($need_save) {
                                        $sub_finded_row->fill($sub_values);
                                        $sub_finded_row->save();
                                        // если запись удалена - восстановим
                                        if ($sub_finded_row->trashed()) $sub_finded_row->restore();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // dd("Can't sync (id not set)");
        }
    }

    // формализуем данные в формат 1с
    private function formalize_data_for_1c($row, $key = null, $method = "post")
    {
        // результирующий массив из строки 1с
        $res = [];
        // дополнительные синхронизации
        $sync_data = [];
        // исключения - типы полей, которые не надо обрабатывать
        $except_types = ["polymorph_table"];
        // исключения - имена полей, которые не надо обрабатывать
        $except_fields = ["Ref_Key", "Posted"];
        // обрабатываем все описанные поля 1с
        foreach ($this->fields_1c() as $field_1c) {
            // имя столбца 1С
            $field_name_1c = $field_1c["name_1c"];
            // имя столбца в БД
            $field_name = $field_1c["name"];
            // echo $field_1c["name"].".".$field_name." / ";
            if (!in_array($field_1c["type"], $except_types) && !in_array($field_1c["name_1c"], $except_fields)) {
                switch ($field_1c["type"]) {
                        // если select - нужно сопоставить uuid из 1с - id в БД
                    case "select":
                    case "key": {
                            // if (in_array($field_1c["table"],$exceptions)) {
                            //     // связь полиморфной таблицы - пока игнорируем, после вставки записи в полиморфную таблицу - изменим уид на правильный
                            //     $field_val = "00000000-0000-0000-0000-000000000000";
                            //     if ($method=="put" || $method=="patch") {
                            //         $field_name_1c = $field_1c["name_1c"]."@odata.bind";
                            //     } else {
                            //         $field_name_1c = $field_1c["name_1c"]."_Key";
                            //     }
                            // } else {
                            // если подчиненная таблица в тип key и передан ключ
                            if ($this->table_type() == "sub_table" && $field_1c["type"] == "key" && !is_null($key)) {
                                $field_val = $key;
                            } else {
                                // если uuid не нулевой
                                if ($field_1c["table"] == "polymorph") {
                                    // значение поля
                                    $value = $row->$field_name;
                                    // дефолтное значение
                                    $field_val = "00000000-0000-0000-0000-000000000000";
                                    // для неопределенных значений
                                    if ($value != 1) {
                                        // получим название столбца с таблицей
                                        $poly_table_res = $this->get_table_name_1c_by_polyfield($field_name, $row);
                                        /* $poly_table_res = array:3 [▼
                                            "table" => array:4 [▼
                                                "Title" => "Контрагенты"
                                                "Class" => "Kontragent"
                                                "Name1C" => "Catalog_Контрагенты"
                                                "Name" => "kontragents"
                                            ]
                                            "field_id" => array:2 [▼
                                                "db" => "rs_table_id"
                                                "1c" => "Owner"
                                            ]
                                            "field_type" => array:2 [▼
                                                "db" => "rs_table_type"
                                                "1c" => "Owner_Type"
                                            ]
                                        ]*/
                                        if (isset($poly_table_res["table"]["Name"])) {
                                            // если найдена таблица, используемая в качестве полиморфной
                                            $poly_table_name = $poly_table_res["table"]["Name"];
                                            if ($poly_table_name) {
                                                // проверим, не является ли полиморфная таблица текущего класса
                                                if ($poly_table_res["table"]["Name"] != $this->table) {

                                                    // создадим экземпляр класса полиморфной таблицы
                                                    $poly_table = $this->new_table($poly_table_name);
                                                    if ($poly_table) {
                                                        // пробуем получить значения id из кэша
                                                        $cache = $this->cache($poly_table_name, false, $row->$field_name);
                                                        if ($cache) {
                                                            $field_val = $cache;
                                                        } else {
                                                            $search_id = $this->get_uuid_by_id($poly_table_name, $row->$field_name);
                                                            if ($search_id) {
                                                                // закэшим
                                                                $this->cache($poly_table_name, $search_id, $row->$field_name);
                                                                $field_val = $search_id;
                                                            }
                                                        }
                                                        // удаляем экземпляр таблицы
                                                        unset($poly_table);
                                                    }
                                                }

                                                // если значения так и не удалось найти
                                                if ($field_val == "00000000-0000-0000-0000-000000000000") {
                                                    // table_source - таблица из которой нужно взять uuid после записи
                                                    // id_source - id в таблице из которой возьмем uuid
                                                    // table_target - таблица в которую нужно будет вставить полученный uuid
                                                    // field_target - поле в которое нужно будет вставить полученный uuid
                                                    // table_target_id - идентификатор записи таблицы в которую вставляем полученный uuid
                                                    // target_class_field - поле в которое вставляем класс таблицы
                                                    // target_class_val - значение класса
                                                    $sync_data[] = [
                                                        "table_source" => $poly_table_name,
                                                        "id_source" => $row->$field_name,
                                                        "table_target" => $this->table,
                                                        "field_target" => $field_name_1c,
                                                        "table_target_id" => $row->id,
                                                        "target_class_field" => $poly_table_res["field_type"]["1c"],
                                                        "target_class_val" => "StandardODATA." . $this->tables[$poly_table_name]["Name1C"]
                                                    ];
                                                } else {
                                                    // вставляем класс таблицы
                                                    $res[$poly_table_res["field_type"]["1c"]] = "StandardODATA." . $this->tables[$poly_table_name]["Name1C"];
                                                }
                                            }
                                        } else {
                                            // не нашли полиморфную связь
                                            // dd($poly_table_res);
                                        }
                                    }
                                } else {
                                    // селект из статичной таблицы
                                    // имя столбца 1С
                                    if ($method == "put" || $method == "patch") {
                                        $field_name_1c = $field_1c["name_1c"] . "@odata.bind";
                                    } else {
                                        $field_name_1c = $field_1c["name_1c"] . "_Key";
                                    }
                                    // значение поля
                                    $value = $row->$field_name;
                                    if ($value == 1) {
                                        $field_val = "00000000-0000-0000-0000-000000000000";
                                    } else {
                                        // проверим, есть ли в кэше значение
                                        $cache = $this->cache($field_1c["table"], false, $value);
                                        if ($cache) {
                                            $field_val = $cache;
                                        } else {
                                            $search_id = $this->get_uuid_by_id($field_1c["table"], $value);
                                            if ($search_id) {
                                                // закэшим
                                                $this->cache($field_1c["table"], $search_id, $value);
                                                $field_val = $search_id;
                                            } else {
                                                // добавим запись
                                                $sync_data[] = [
                                                    "table_source" => $field_1c["table"],
                                                    "id_source" => $value,
                                                    "table_target" => $this->table,
                                                    "field_target" => $field_name_1c,
                                                    "table_target_id" => $row->id
                                                ];
                                                $field_val = "00000000-0000-0000-0000-000000000000";
                                                // $parent_table = $this->new_table($field_1c["table"]);
                                                // $inserted_row = $parent_table->sync_by_id($value);
                                                // if ($inserted_row) {
                                                //     $field_val = $inserted_row->Ref_Key;
                                                //     $this->cache($field_1c["table"],$field_val,$value);
                                                // } else {
                                                //     $field_val = "00000000-0000-0000-0000-000000000000";
                                                //     // укажем, что необходимо обновить id ненайденной записи после импорта
                                                //     // if (Cache::has("sync_1c")) $sync_data = json_decode(Cache::get("sync_1c"));
                                                //     $sync_data[] = ["table_source"=>$field_1c["table"],"id_source"=>$value,"table_target"=>$this->table,"field_target"=>$field_name,"table_target_id"=>$row->id];
                                                //     // Cache::set("sync_1c", json_encode($sync_data),30);
                                                // }
                                                unset($parent_table);
                                            }
                                        }
                                    }
                                    if ($method == "put" || $method == "patch") $field_val = $field_1c["table_name_1c"] . "(guid'" . $field_val . "')";
                                }
                            }
                            // }
                        }
                        break;
                    case "date":
                    case "datetime": {
                            $field_val = date('c', strtotime($row->$field_name));
                        }
                        break;
                    case "boolean": {
                            $field_val = (bool)$row->$field_name;
                        }
                        break;
                    default: {
                            if (isset($row->$field_name)) $field_val = $row->$field_name;
                        }
                }
                if (isset($field_val)) $res[$field_name_1c] = $field_val;
            }
        }
        // проверим удаление записи
        if ($row->trashed()) $res["DeletionMark"] = (bool)true;
        // табличные части
        if ($this->has_sub_tables()) {
            // найдем строку
            $this_table_row = $this->find($row->id);
            foreach ($this->sub_tables() as $sub_table) {
                // dd($sub_table);
                $sub_table_data = [];
                if (isset($sub_table["name_1c"])) {
                    // получим модель подчиненной таблицы
                    if (isset($sub_table["name"]) && $this_table_row && isset($sub_table["method"])) {
                        $sub_table_item = $this->new_table($sub_table["name"]);
                        $db_sub_table_data = $this_table_row->{$sub_table["method"]}()->get();
                        if (count($db_sub_table_data) > 0) {
                            $lineNumber = 1;
                            foreach ($db_sub_table_data as $db_sub_table_data_row) {
                                $st_data_row = $sub_table_item->formalize_data_for_1c($db_sub_table_data_row);
                                if (isset($st_data_row["data"]["LineNumber"])) {
                                    $st_data_row["data"]["LineNumber"] = $lineNumber;
                                    $lineNumber++;
                                }
                                $sub_table_data[] = $st_data_row;
                            }
                        }
                    }

                    $sub_table_name_1c = $sub_table["name_1c"];
                    if ($method == "post") {
                        if (isset($db_sub_table_data) && count($db_sub_table_data) > 0) {
                            $res[$sub_table_name_1c] = $sub_table_data;
                        }
                    } elseif ($method == "put" || $method == "patch") {
                        $res[$sub_table_name_1c] = $sub_table_data;
                    }
                }
            }
        }
        // dd($res);
        return ["data" => $res, "sync_data" => $sync_data];
    }

    // формализуем данные в формат БД
    private function formalize_data_for_db($row, $key = null)
    {
        // var_dump($row);
        // результирующий массив из строки 1с
        $res = [];
        // дополнительные синхронизации
        $sync_data = [];
        // исключения - типы полей, которые не надо обрабатывать
        $except_types = ["polymorph_table"];
        // обрабатываем все описанные поля 1с
        // dd($this->get_1c_fields());
        foreach ($this->get_1c_fields() as $field_1c) {
            // имя столбца 1С
            $field_name_1c = $field_1c["name_1c"];
            // имя столбца в БД
            $field_name = $field_1c["name"];
            if (!in_array($field_1c["type"], $except_types)) {
                switch ($field_1c["type"]) {
                        // если select - нужно сопоставить uuid из 1с - id в БД
                    case "select":
                    case "key": {
                            // если подчиненная таблица в тип key и передан ключ
                            if ($this->table_type() == "sub_table" && $field_1c["type"] == "key" && !is_null($key)) {
                                $field_val = $key;
                            } else {
                                // var_dump($field_1c);
                                // если uuid не нулевой
                                if ($field_1c["table"] == "polymorph") {
                                    if ($row->$field_name_1c == "00000000-0000-0000-0000-000000000000") {
                                        $field_val = 1;
                                    } else {
                                        // селект из полиморфной таблицы
                                        // получим название столбца с таблицей
                                        $poly_table_res = $this->get_table_name_by_polyfield_name_1c($field_name, $row);
                                        // var_dump($poly_table_res);
                                        if (isset($poly_table_res["table"]["Name"])) {
                                            $poly_table_name = $poly_table_res["table"]["Name"];
                                            if ($poly_table_name) {
                                                $poly_table = $this->new_table($poly_table_name);
                                                if ($poly_table) {
                                                    // пробуем получить значения id из кэша
                                                    $cache = $this->cache($poly_table_name, $row->$field_name_1c);
                                                    if ($cache) {
                                                        $field_val = $cache;
                                                    } else {
                                                        $search_id = $this->get_id_by_uuid($poly_table_name, $row->$field_name_1c);
                                                        if ($search_id) {
                                                            // закэшим
                                                            $this->cache($poly_table_name, $row->$field_name_1c, $search_id);
                                                            $field_val = $search_id;
                                                        } else {
                                                            $field_val = 1;
                                                            // добавим полиморфную связь
                                                            // if (Cache::has("sync")) $sync_data = json_decode(Cache::get("sync"));

                                                            // [
                                                            //     [
                                                            //         "Title" => "Контрагенты"
                                                            //         "Class" => "Kontragent"
                                                            //         "Name1C" => "Catalog_Контрагенты"
                                                            //         "Name" => "kontragents"
                                                            //     ]
                                                            //     "field_id" => array:3 [▼
                                                            //         "db" => "rs_table_id"
                                                            //         "1c" => "Owner"
                                                            //         "val" => "a348aa8c-d629-11e5-0a9b-000c295e30eb"
                                                            //     ]
                                                            //     "field_type" => array:3 [▼
                                                            //         "db" => "rs_table_type"
                                                            //         "1c" => "Owner_Type"
                                                            //         "val" => "Catalog_Контрагенты"
                                                            //     ]
                                                            // ]
                                                            $sync_data[] = [
                                                                "table_source" => $poly_table_name,
                                                                "uuid_source" => $row->$field_name_1c,
                                                                "table_target" => $this->table,
                                                                "field_target" => $field_name,
                                                                /* "target_row_uuid"=>$row->uuid */
                                                            ];
                                                            // Cache::set("sync", json_encode($sync_data),30);
                                                        }
                                                    }
                                                    // вставляем класс таблицы
                                                    $res[$poly_table_res["field_type"]["db"]] = "App\\" . $this->tables[$poly_table_name]["Class"];
                                                    unset($poly_table);
                                                }
                                            }
                                        } else {
                                            // dd($poly_table_res);
                                        }
                                    }
                                } else {
                                    // селект из статичной таблицы
                                    // имя столбца 1С
                                    $field_name_1c = $field_1c["name_1c"] . "_Key";
                                    // значение поля
                                    $value = $row->$field_name_1c;
                                    // var_dump($value);
                                    if ($value == "00000000-0000-0000-0000-000000000000") {
                                        $field_val = 1;
                                    } else {
                                        // проверим, есть ли в кэше значение
                                        $cache = $this->cache($field_1c["table"], $value);
                                        if ($cache) {
                                            $field_val = $cache;
                                        } else {
                                            $search_id = $this->get_id_by_uuid($field_1c["table"], $value);
                                            if ($search_id) {
                                                // закэшим
                                                $this->cache($field_1c["table"], $value, $search_id);
                                                $field_val = $search_id;
                                            } else {
                                                // добавим запись
                                                $field_val = 1;
                                                $sync_data[] = [
                                                    "table_source" => $field_1c["table"],
                                                    "uuid_source" => $value,
                                                    "table_target" => $this->table,
                                                    "field_target" => $field_name
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    default: {
                            if (isset($row->$field_name_1c)) $field_val = $row->$field_name_1c;
                        }
                }
                if (isset($field_val)) $res[$field_name] = $field_val;
            }
        }
        return [
            "data" => $res,
            "sync_data" => $sync_data
        ];
    }

    // добавлякм запись в бд на основании uuid 1с
    public function sync_by_uuid($uuid)
    {
        // результат
        $res = null;
        // если сохранена запись
        $is_ok = false;
        // соединение
        $http = new API1C();
        // $http = new API1C($apiURI,$apiLogin,$apiPassword,1,3000);
        // определим текущий момент
        $now = DB::raw("NOW()");
        // вносим записи в БД (обновляем, если не совпадают поля)
        // получаем запись из справочника из 1С
        $data_1c = $http->is_exists($this->name_1c(), $uuid);
        // dd($data_1c);
        if ($data_1c) {
            // массив значениями модели
            $formalize_data = $this->formalize_data_for_db($data_1c);
            // dd($formalize_data);
            $new_values = $formalize_data["data"];
            // служебные поля
            $new_values["sync_1c_at"] = $now;
            // нужно сохранять
            $need_save = false;
            // удалена ли запись
            if (property_exists($data_1c, "DeletionMark")) {
                $is_deleted = (bool)$data_1c->DeletionMark;
            } else {
                $is_deleted = false;
            }
            // найденная запись
            $finded_row = $this->is_exists($new_values);
            // если есть запись
            if ($finded_row) {
                // проверим, нужно ли ее изменять
                if ($this->is_modified($new_values, $is_deleted)) {
                    // нужно изменять
                    $new_values["updated_by"] = 1;
                    $need_save = true;
                }
            } else {
                // добавляем
                $new_values["created_by"] = 1;
                $need_save = true;
                // создаем новый класс
                $finded_row = $this->new_table($this->table());
            }
            // dd($new_values);
            // если нужно сохранять
            if ($need_save) {
                $finded_row->fill($new_values);
                $finded_row->save();
                $is_ok = true;
                // если запись удалена
                if ($is_deleted) {
                    $finded_row->deleted_by = 1;
                    $finded_row->save();
                    $finded_row->delete();
                } else {
                    if ($finded_row->trashed()) $finded_row->restore();
                }
                // подчиненные таблицы
                $finded_row->sync_sub_tables($data_1c);
            }
            // если есть дополнительные синхронизации
            if ($is_ok && isset($formalize_data["sync_data"])) {
                foreach ($formalize_data["sync_data"] as $sync_data) {
                    if (isset($sync_data["table_source"]) && $sync_data["uuid_source"]) {
                        // таблица назначения
                        $source_table = $this->new_table($sync_data["table_source"]);
                        $inserted_source_row = $source_table->sync_by_uuid($sync_data["uuid_source"]);
                        if ($inserted_source_row) {
                            $finded_row->{$sync_data["field_target"]} = $inserted_source_row->id;
                            $finded_row->save();
                        } else {
                            // dd("Fail", $inserted_source_row);
                        }
                    }
                }
            }
            $res = $finded_row;
        }
        return $res;
    }

    // проверяем, есть ли такая запись в БД (таблице $this) и, если есть изменена ли она с момента прошлой синхронизации
    // $row - формализованные данные, подготовленные функцией formalize_data_for_db
    public function is_exists($row)
    {
        $res = null;
        switch ($this->table_type()) {
            case "catalog":
            case "document": {
                    // справочник,
                    if (isset($row["uuid"])) {
                        $id = $this->where("uuid", $row["uuid"])->first();
                        if ($id) $res = $id;
                    }
                }
                break;
            case "sub_table": {
                    // подчиненная таблицы в документе
                    if (isset($row["LineNumber"])) {
                        $id = $this->where("LineNumber", $row["LineNumber"])->first();
                        if ($id) $res = $id;
                    }
                }
        }
        return $res;
    }

    // проверяем, изменена ли запись в БД  с момента прошлой синхронизации
    // $row - формализованные данные, подготовленные функцией formalize_data_for_db
    public function is_modified($row, $is_deleted = false)
    {
        $res = false;
        if ($this->is_exists($row)) {
            switch ($this->table_type()) {
                case "catalog":
                case "document": {
                        // справочник, документ
                        // попытаемся найти строку содержащую уид, не измененную и не удаленную с момента последней синхронизации
                        // и у которой хотя бы 1 поле из списка 1С не совпадает с сущностью записи в БД
                        $find_edited_row_in_db = $this->where("uuid", $row["uuid"])
                            ->where(function ($query) {
                                $query->whereNull('updated_at')->orwhereColumn('updated_at', '<=', 'sync_1c_at');
                            })
                            ->where(function ($query) use ($row, $is_deleted) {
                                // 1-я запись
                                $is_first = true;
                                // поля, которые не проверять на соответствие
                                $except_fields = [
                                    'id', 'uuid', 'created_at', 'created_by', 'updated_at', 'updated_by',
                                    'deleted_by', 'deleted_at', 'is_protected', 'sync_1c_at'
                                ];
                                foreach ($row as $field => $value) {
                                    if (!in_array($field, $except_fields)) {
                                        if ($is_first) {
                                            $query->where($field, "<>", $value);
                                            $is_first = false;
                                        } else {
                                            $query->orWhere($field, "<>", $value);
                                        }
                                    }
                                }
                                if ($is_deleted) {
                                    $query->orWhereNull('deleted_at');
                                } else {
                                    // $query->orWhereNotNull('deleted_at');
                                    $query->orWhere(function ($query) {
                                        $query->whereNotNull('deleted_at')->whereColumn('deleted_at', '<=', 'sync_1c_at');
                                    });
                                }
                            })
                            ->first();
                        // ->toSql();
                        // dd($find_edited_row_in_db);
                        if ($find_edited_row_in_db) {
                            $res = true;
                        } else {
                            // проверим подчиненные таблицы
                            if ($this->has_sub_tables()) {
                                // только не понятно как
                                // пока выведем true
                                foreach ($this->sub_tables() as $sub_table) {
                                    if (isset($sub_table["Method"])) {
                                        try {
                                            //code...
                                            // $res_data = $this->$method()->where('npp',0)->orWhere('deleted_at', '>', 'sync_1c_at')->orWhere('updated_at', '>', 'sync_1c_at');
                                            // if ($res_data) return true;
                                        } catch (\Throwable $th) {
                                            //throw $th;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;
                case "sub_table": {
                        // подчиненная таблицы в документе
                        // попытаемся найти строку содержащую LineNumber, не измененную и не удаленную с момента последней синхронизации
                        // и у которой хотя бы 1 поле из списка 1С не совпадает с сущностью записи в БД
                        if (isset($row["LineNumber"])) {
                            $find_edited_row_in_db = $this->where("LineNumber", $row["LineNumber"])
                                ->where(function ($query) {
                                    $query->whereNull('updated_at')->orwhereColumn('updated_at', '<=', 'sync_1c_at');
                                })
                                ->where(function ($query) use ($row) {
                                    // 1-я запись
                                    $is_first = true;
                                    // поля, которые не проверять на соответствие
                                    $except_fields = [
                                        'id', 'uuid', 'created_at', 'created_by', 'updated_at', 'updated_by',
                                        'deleted_by', 'deleted_at', 'is_protected', 'sync_1c_at'
                                    ];
                                    foreach ($row as $field => $value) {
                                        if (!in_array($field, $except_fields)) {
                                            if ($is_first) {
                                                $query->where($field, "<>", $value);
                                                $is_first = false;
                                            } else {
                                                $query->orWhere($field, "<>", $value);
                                            }
                                        }
                                    }
                                    $query->orWhere(function ($query) {
                                        $query->whereNotNull('deleted_at')->whereColumn('deleted_at', '<=', 'sync_1c_at');
                                    });
                                })
                                ->first();
                            if ($find_edited_row_in_db) {
                                $res = true;
                            } else {
                                $res = false;
                            }
                        } else {
                            // нет № позиции - не понятно что делать с такой таблицей
                            return false;
                        }
                    }
            }
        }
        return $res;
    }

    // проверяем, нужно ли обновлять запись в 1С - смотрим даты
    public function need_update_1c($id)
    {
        // результат
        $res = true;
        // строка с id
        $row_id = $this->where('id', $id);
        // делаем выборку
        $finded_row = $row_id->first();
        if ($finded_row) {
            // проверка для подчиненной таблицы
            $is_changed = $row_id->where(function ($query) {
                $query->whereNull('sync_1c_at')->orWhereColumn('created_at', '>', 'sync_1c_at')
                    ->orWhere(function ($query) {
                        $query->whereNotNull('updated_at')->whereColumn('updated_at', '>', 'sync_1c_at');
                    })
                    ->orWhere(function ($query) {
                        $query->whereNotNull('deleted_at')->whereColumn('deleted_at', '>', 'sync_1c_at');
                    });
            })
                ->count();

            if ($this->table_type() != "sub_table") {
                if ($is_changed == 0) {
                    // проверим подчиненные таблицы
                    foreach ($this->sub_tables() as $table) {
                        if (isset($table["method"])) {
                            $sub_data_changed = $finded_row->{$table["method"]}()
                                ->where(function ($query) {
                                    $query->whereNull('sync_1c_at')->orWhereColumn('created_at', '>', 'sync_1c_at')
                                        ->orWhere(function ($query) {
                                            $query->whereNotNull('updated_at')->whereColumn('updated_at', '>', 'sync_1c_at');
                                        })
                                        ->orWhere(function ($query) {
                                            $query->whereNotNull('deleted_at')->whereColumn('deleted_at', '>', 'sync_1c_at');
                                        });
                                })
                                ->count();
                            if ($sub_data_changed > 0) {
                                $is_changed = 1;
                                break;
                            }
                        }
                    }
                }
            }
            if ($is_changed == 0) $res = false;
        }
        return $res;
    }

    // синхронизация с 1С
    public function sync_1c()
    {
        // результат синхронизации
        $sync_res_errors = [];
        $sync_res_info[] = "Начало синхронизации таблицы " . $this->table() . "(" . $this->name_1c() . ")";
        // $inserted_count_db = 0;
        // $updated_count_db = 0;
        // $inserted_count_1c = 0;
        // $updated_count_1c = 0;

        // добавляем в БД или обновляем измененные записи
        // создаем соединение
        if (isset($http)) unset($http);
        $http = new API1C();
        // $http = new API1C($apiURI,$apiLogin,$apiPassword,1,3000);

        // вносим записи в БД (обновляем, если не совпадают поля)
        // параметры запроса
        // $request_params_1c = "$"."select=Ref_Key&$"."top=10";
        $request_params_1c = "$" . "select=Ref_Key";
        if ($this->has_folders_1c()) $request_params_1c .= "&$" . "filter=IsFolder eq false";
        // получаем список справочника из 1С
        $response_from_1c = $http->get($this->name_1c(), $request_params_1c);
        // обрабатываем ошибки
        if ($response_from_1c["is_error"]) {
            $sync_res_errors[] = $response_from_1c["err_text"] . ", код [" . $response_from_1c["code"] . "]";
        } else {
            // проверяем код ответа сервера и если 200, то заносим данные в БД
            switch ($response_from_1c["code"]) {
                case 200: {
                        // добавим данные из 1С
                        // dd($response_from_1c["data"]->value);
                        foreach ($response_from_1c["data"]->value as $data_1c) {
                            // var_dump($data_1c);
                            if (isset($data_1c->Ref_Key)) {
                                $uuid = $data_1c->Ref_Key;
                                // var_dump($uuid);
                                $res_sync = $this->sync_by_uuid($uuid);
                                if ($res_sync) {
                                    $sync_res_info[] = "Добавлена/обновлена запись в таблице " . $this->table() . "(" . $this->name_1c() . ") с uuid=" . $uuid;
                                } else {
                                    // dd($res_sync);
                                }
                            }
                        }
                    }
            }
        }

        // теперь вставим недостающие записи в 1С

        // $rows_to_1c = $this->whereNull("uuid")->where("id",">",1)->get();
        $rows_to_1c = $this->where("id", ">", 1)->where(function ($query) {
            $query->whereNull("uuid")
                ->orWhere("uuid", "");
        })->get();
        // dd($rows_to_1c);
        foreach ($rows_to_1c as $row_to_1c) {
            $res_sync = $this->sync_by_id($row_to_1c->id);
            if ($res_sync) {
                $sync_res_info[] = "Добавлена/обновлена запись в таблице " . $this->table() . "(" . $this->name_1c() . ") с id=" . $row_to_1c->id;
            }
        }


        $sync_res_info[] = "Конец синхронизации таблицы " . $this->table() . "(" . $this->name_1c() . ")";

        return [
            "is_error" => count($sync_res_errors) > 0 ? true : false,
            "errors" => $sync_res_errors,
            "info" => $sync_res_info,
            // "inserted_count_db" => $inserted_count_db,
            // "updated_count_db" => $updated_count_db,
            // "inserted_count_1c" => $inserted_count_1c,
            // "updated_count_1c" => $updated_count_1c,
        ];
    }
}