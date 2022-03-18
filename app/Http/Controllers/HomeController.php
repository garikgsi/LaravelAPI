<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DocType;
use App\Production;
use App\Nomenklatura;
use App\SkladReceive;
use App\SkladReceiveItem;
use App\SkladMove;
use App\Valuta;
use App\File;
use App\Common\ABPStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Act;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('auth:web');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        // $n = Nomenklatura::stock_balance(15);

        // dd($n->get()->toArray());

        // $acts = Act::where('is_active', 1)->first();
        // dd($acts->export_to_1c());


        $user = Auth::user();

        // // перенос на другую ФС
        // $disk = new ABPStorage('local');
        // dd($disk);
        // $f = File::find(35);
        // $f->file_driver_id = 2;
        // $f->save();
        // dd($f->toArray());


        // $res = $disk->replace_to($f, 'google');
        // dd($res);



        // $disk = new ABPStorage('google');
        // $f = File::find(33);
        // dd($f->toArray());
        // $res = $disk->copy_file($f);
        // dd($res);

        // // $sri = new SkladReceiveItem;
        // // $source = $sri->find(10064);
        // $sr = new Production;
        // $source = $sr->find(6);
        // $sm = new SkladMove;
        // $source = $sm->find(2);
        // $source = Nomenklatura::find(141);
        // // // копируем модель
        // // $target = $source->replicate();
        // // // заменяем переданными значениями
        // // $target->uuid = NULL;
        // // // ...
        // // // сохраняем чтобы получить id
        // // $target->push();
        // // // опции копирования
        // // $copy_options = json_decode('{"ext_groups":true,"ext_images":true,"ext_documents":true,"ext_file_list":true,"sub_table_recipes":true}', true);
        // // $copy_options = json_decode('{"sub_table_production_items":true, "sub_table_production_replaces":true}', true);
        // $copy_options = json_decode('{"ext_groups":true,"ext_images":true,"ext_documents":true,"ext_file_list":true}', true);
        // // // обработаем опции
        // // $relations = [];
        // // // расширения
        // // $extensions = [];
        // // // подчиненные таблицы
        // // $sub_tables = [];
        // // // обработаем в соответствии с принятыми в API алгоритмами:
        // // // расширения имеют префикс ext_
        // // // подчиненные таблицы - префикс sub_table_
        // // foreach ($copy_options as $relation => $to_copy) {
        // //     if ($to_copy) {
        // //         // если расширение
        // //         preg_match('/ext_(.+)/', $relation, $matches);
        // //         if (count($matches) > 1) {
        // //             $ext_name = $matches[1];
        // //             if (method_exists($source, $ext_name)) $extensions[] = $ext_name;
        // //         }
        // //         // если подчиненная таблица
        // //         preg_match('/sub_table_(.+)/', $relation, $matches);
        // //         if (count($matches) > 1) {
        // //             // загрузим список подчиненных таблиц, если его еще нет
        // //             if (!isset($model_sub_tables)) $model_sub_tables = collect($source->sub_tables());
        // //             // имя подчиненной таблицы в описании
        // //             $sub_table_name = $matches[1];
        // //             // ищем в коллекции подчиненных таблиц
        // //             $sub_table = $model_sub_tables->first(function ($st) use ($sub_table_name) {
        // //                 return $st["table"] == $sub_table_name;
        // //             });
        // //             // возвращаем метод или название подчиненной таблицы, если  метод не указан
        // //             if (isset($sub_table)) {
        // //                 $method = isset($sub_table["method"]) ? $sub_table["method"] : $sub_table_name;
        // //                 if (method_exists($source, $method)) $sub_tables[] = $method;
        // //             }
        // //         }
        // //     }
        // // }
        // // // загрузим в модель все найденные связи
        // // $source->load(array_merge($sub_tables, $extensions));
        // // // получим все связи
        // // $relations = $source->getRelations();
        // // // dd($source->toArray());
        // // // обраб=атываем каждую связь отдельно
        // // foreach ($relations as $relation => $items) {
        // //     // dd($relation);
        // //     // получаем экземпляр модели связной таблицы
        // //     foreach ($items as $relationRecord) {
        // //         // скопируем модель связной таблицы
        // //         $newRelationship = $relationRecord->replicate();
        // //         // изменяем свойства новой модели
        // //         $newRelationship->uuid = NULL;
        // //         // ассоциируем новый объект с новым родителем
        // //         $target->{$relation}()->save($newRelationship);
        // //         // копируем все дочерние свойства для дочернего объекта
        // //         dd($target->id, $newRelationship->toArray());
        // //     }
        // // }

        // $res = $source->copy_recursive(false, $copy_options);
        // // $res = $source->copy_recursive(true);
        // dd($res);
        // // $target->load(array_merge($extensions, $sub_tables));
        // // dd($target["data"]);

        // // $res = $n->find(313)->export_to_1c();
        // // dd($res);
        // // dd(Nomenklatura::find(310)->export_to_1c(310));



        // $p = new Production;
        // $res = $p->find(6)->export_to_1c();
        // dd($res);


        // $sr = new SkladReceive;
        // $sr1039 = $sr->where('id', 1034)->first();
        // // dd($sr1039->toArray());
        // $res = $sr1039->export_to_1c();
        // // $res = $sr1039->ac_uuid('20.01', 'default');
        // dd($res);


        //         ini_set('max_execution_time', 300);
        //         // очистка таблиц
        //         /*
        // TRUNCATE `db1`.`manufacturers`;
        // INSERT INTO `db1`.`manufacturers` (id,name,is_protected) values (1,'Не выбрано',1);
        // TRUNCATE `db1`.`nomenklatura`;
        // INSERT INTO `db1`.`nomenklatura` (id,name,is_protected) values (1,'Не выбрано',1);
        // TRUNCATE `db1`.`recipes`;
        // INSERT INTO `db1`.`recipes` (id,name,is_protected) values (1,'Не выбрано',1);
        // TRUNCATE `db1`.`recipe_items`;
        // INSERT INTO `db1`.`recipe_items` (id,name,is_protected) values (1,'Не выбрано',1);
        // TRUNCATE `db1`.`sotrudniks`;
        // INSERT INTO `db1`.`sotrudniks` (id,name,is_protected) values (1,'Не выбрано',1);
        // TRUNCATE `db1`.`firm_positions`;
        // INSERT INTO `db1`.`firm_positions` (id,name,is_protected) values (1,'Не выбрано',1);
        // DELETE FROM `db1`.`sklads` WHERE ID>2;
        // ALTER TABLE `db1`.`sklads` AUTO_INCREMENT = 3;
        // TRUNCATE `db1`.`tags`;
        // INSERT INTO `db1`.`tags` (id,name,is_protected) values (1,'Не выбрано',1);
        // TRUNCATE `db1`.`table_tags`;
        // INSERT INTO `db1`.`table_tags` (id,name,is_protected) values (1,'Не выбрано',1);
        // TRUNCATE `db1`.`files`;
        // INSERT INTO `db1`.`files` (id,name,is_protected,table_type,table_id) values (1,'Не выбрано',1,'',1);
        // TRUNCATE `db1`.`file_lists`;
        // INSERT INTO `db1`.`file_lists` (id,name,is_protected,table_type,table_id) values (1,'Не выбрано',1,'',1);

        //  */

        //         $old_db = DB::connection('moydodyr');
        //         $new_db = DB::connection('db1');
        //         // соотношения старого и нового id
        //         // $move_array = [
        //         // "edism" => [
        //         //     "ID_1" => "id_1",
        //         //     "ID_2" => "id_2",
        //         //      ...
        //         //     "ID_N" => "id_N"
        //         // ],
        //         // ];
        //         $move_array = [
        //             "edism" => [39 => 1],
        //         ];


        //         // соответствие модулей старой базы моделям новой
        //         $modules = [
        //             3 => [
        //                 "table" => "nomenklatura",
        //                 "old_table" => "nomenklatura",
        //                 "model" => "App\Nomenklatura"
        //             ],
        //             5 => [
        //                 "table" => "sotrudniks",
        //                 "old_table" => "sotrudnik",
        //                 "model" => "App\Sotrudnik"
        //             ],
        //             12 => [
        //                 "table" => "sklads",
        //                 "old_table" => "sklad",
        //                 "model" => "App\Sklad"
        //             ],
        //             13 => [
        //                 "table" => "kontragents",
        //                 "old_table" => "kontragents",
        //                 "model" => "App\Kontragent"
        //             ],
        //             17 => [
        //                 "table" => "Инструменты",
        //                 "old_table" => "instrument",
        //                 "model" => NULL
        //             ],
        //             27 => [
        //                 "table" => "Файлы прикрепляемые",
        //                 "old_table" => "",
        //                 "model" => NULL
        //             ],
        //             64 => [
        //                 "table" => "manufacturers",
        //                 "old_table" => "manufacturers",
        //                 "model" => "App\Manufacturer"
        //             ],
        //         ];
        //         $modules_array = collect($modules)->filter(function ($module) {
        //             return !is_null($module["model"]);
        //         })->keys()->toArray();


        //         // перенос справочника номенклатур
        //         // 0. переносим единицы измерения
        //         // все старые единицы измерения
        //         $edisms = $old_db->table('edism')->where('Deleted', 0)->get();
        //         foreach ($edisms as $edism) {
        //             // OKEI -> okei
        //             // ID -> id
        //             // ShortName -> name
        //             // Name -> comment
        //             $move_fields = [
        //                 "OKEI" => "okei",
        //                 "ShortName" => "name",
        //                 "Name" => "comment"
        //             ];

        //             if (!isset($move_array["edism"][$edism->ID])) {
        //                 if ($edism->OKEI) {
        //                     $normalize_data = [];
        //                     $insert_data = [];
        //                     foreach ($move_fields as $field_old => $field_new) {
        //                         $val = $this->normalize_text($edism->$field_old);
        //                         $normalize_data[$field_old] = $val;
        //                         $insert_data[$field_new] = $val;
        //                     }

        //                     $find_edism = $new_db->table('ed_ism')->where('okei', $normalize_data["OKEI"])->first();
        //                     if ($find_edism) {
        //                         $move_array["edism"][$edism->ID] = $find_edism->id;
        //                     } else {
        //                         $new_id = $new_db->table('ed_ism')->insertGetId($insert_data);
        //                         if ($new_id) {
        //                             $move_array["edism"][$edism->ID] = $new_id;
        //                         } else {
        //                             dd("can't insert into [ed_ism] data", $insert_data);
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //         // 1. переносим производителей
        //         // все старые производители
        //         $manufacturers = $old_db->table('manufacturers')->get();
        //         foreach ($manufacturers as $manufacturer) {
        //             $move_fields = [
        //                 "Name" => "name",
        //             ];

        //             if (!isset($move_array["manufacturers"][$manufacturer->ID])) {
        //                 $normalize_data = [];
        //                 $insert_data = [];
        //                 foreach ($move_fields as $field_old => $field_new) {
        //                     $val = $this->normalize_text($manufacturer->$field_old);
        //                     $normalize_data[$field_old] = $val;
        //                     $insert_data[$field_new] = $val;
        //                 }
        //                 if ($manufacturer->Deleted == 1) $insert_data["deleted_at"] = "2001-01-01";

        //                 $find_manufacturer = $new_db->table('manufacturers')->where('name', $normalize_data["Name"])->first();
        //                 if ($find_manufacturer) {
        //                     $move_array["manufacturers"][$manufacturer->ID] = $find_manufacturer->id;
        //                 } else {
        //                     $new_id = $new_db->table('manufacturers')->insertGetId($insert_data);
        //                     if ($new_id) {
        //                         $move_array["manufacturers"][$manufacturer->ID] = $new_id;
        //                     } else {
        //                         dd("can't insert into [manufacturers] data", $insert_data);
        //                     }
        //                 }
        //             }
        //         }

        //         // 2. перенос номенклатур
        //         // $nomenklaturas = $old_db->table('nomenklatura')->where('Deleted', 0)->get();
        //         // foreach ($nomenklaturas as $nomenklatura) {
        //         $old_db->table('nomenklatura')->orderBy('id')->chunk(10, function ($nomenklaturas) use (&$move_array, $new_db, $old_db) {
        //             foreach ($nomenklaturas as $nomenklatura) {
        //                 $move_fields = [
        //                     "Artikul" => "artikul",
        //                     "Name" => NULL,
        //                     "Descr" => NULL,
        //                     // "Name"+"Descr" => "name"
        //                     "Characs" => "description",
        //                     "Comment" => "comment",
        //                     "CatNum" => "part_num"
        //                 ];
        //                 if (!isset($move_array["nomenklatura"][$nomenklatura->ID])) {
        //                     $normalize_data = [];
        //                     $insert_data = [];
        //                     foreach ($move_fields as $field_old => $field_new) {
        //                         if (is_null($nomenklatura->$field_old)) {
        //                             $val = NULL;
        //                         } else {
        //                             $val = $this->normalize_text($nomenklatura->$field_old);
        //                         }
        //                         $normalize_data[$field_old] = $val;
        //                         if (!is_null($field_new)) $insert_data[$field_new] = $val;
        //                     }

        //                     $find_nomenklatura = $new_db->table('nomenklatura')->where('artikul', $normalize_data["Artikul"])->first();
        //                     if ($find_nomenklatura) {
        //                         $move_array["nomenklatura"][$nomenklatura->ID] = $find_nomenklatura->id;
        //                     } else {
        //                         // предопределенные поля
        //                         $insert_data["name"] = $normalize_data["Name"] . (is_null($normalize_data["Descr"]) ? '' : " " . $normalize_data["Descr"]);
        //                         if ($nomenklatura->IsSostav == 1) {
        //                             $insert_data["doc_type_id"] = 61;
        //                         } else {
        //                             $insert_data["doc_type_id"] = 5;
        //                         }
        //                         $insert_data["ed_ism_id"] = isset($move_array["edism"][$nomenklatura->EdIsm]) ? $move_array["edism"][$nomenklatura->EdIsm] : 2;
        //                         $insert_data["is_usluga"] = 0;
        //                         $insert_data["nds_id"] = 8;
        //                         if ($nomenklatura->Deleted == 1) $insert_data["deleted_at"] = "2001-01-01";
        //                         // добавляем данные
        //                         $new_id = $new_db->table('nomenklatura')->insertGetId($insert_data);
        //                         if ($new_id) {
        //                             $move_array["nomenklatura"][$nomenklatura->ID] = $new_id;
        //                         } else {
        //                             dd("can't insert into [nomenklatura] data", $insert_data);
        //                         }
        //                     }
        //                 } else {
        //                     $new_id = $move_array["nomenklatura"][$nomenklatura->ID];
        //                 }
        //             }
        //             // sleep(3);
        //         });

        //         // 2.2 добавим рецептуры
        //         $old_db->table('nomenklatura')->where('Deleted', 0)->where("IsSostav", 1)->orderBy('id')->chunk(10, function ($nomenklaturas) use (&$move_array, $new_db, $old_db) {
        //             foreach ($nomenklaturas as $nomenklatura) {
        //                 $recept_items = $old_db->table('recepts')->where('Deleted', 0)->where("NomenklaturaID", $nomenklatura->ID)->get();
        //                 if ($recept_items) {
        //                     if (isset($move_array["nomenklatura"][$nomenklatura->ID])) {
        //                         $nomenklatura_id = $move_array["nomenklatura"][$nomenklatura->ID];
        //                         $default_recipe_name = "Основная";
        //                         // проверим, есть ли уже созданная основная рецептура
        //                         $new_recipe = $new_db->table('recipes')->where("nomenklatura_id", $nomenklatura_id)->where("name", $default_recipe_name)->first();
        //                         if ($new_recipe) {
        //                             $recipe_id = $new_recipe->id;
        //                         } else {
        //                             // создадим основную рецептуру
        //                             $recipe_data = [
        //                                 "name" => $default_recipe_name,
        //                                 "nomenklatura_id" => $nomenklatura_id
        //                             ];
        //                             $recipe_id = $new_db->table('recipes')->insertGetId($recipe_data);
        //                             if (!$recipe_id) {
        //                                 dd("can't insert into [recipes] data", $recipe_data);
        //                             }
        //                         }
        //                         if (isset($recipe_id)) {
        //                             // добавляем состав рецептуры
        //                             foreach ($recept_items as $recept_item) {

        //                                 if (!isset($move_array["recipe_items"][$recept_item->ID])) {
        //                                     if (isset($move_array["nomenklatura"][$recept_item->ComponentID])) {
        //                                         $nomenklatura_old_id = $move_array["nomenklatura"][$recept_item->ComponentID];
        //                                         // ищем такую позицию в рецептуре
        //                                         $recipe_item = $new_db->table('recipe_items')->where("recipe_id", $recipe_id)->where("nomenklatura_id", $nomenklatura_old_id)->first();
        //                                         if (!$recipe_item) {
        //                                             $recipe_item_data = [
        //                                                 "recipe_id" => $recipe_id,
        //                                                 "nomenklatura_id" => $nomenklatura_old_id,
        //                                                 "kolvo" => $recept_item->Kolvo
        //                                             ];
        //                                             $recipe_item_id = $new_db->table('recipe_items')->insertGetId($recipe_item_data);
        //                                             $move_array["recipe_items"][$recept_item->ID] = $recipe_item_id;
        //                                             if (!$recipe_item_id) {
        //                                                 dd("can't insert into [recipe_items] data", $recipe_item_data);
        //                                             }
        //                                         }
        //                                     } else {
        //                                         echo ("Не могу найти номенклатуру с ID " . $recept_item->ComponentID);
        //                                     }
        //                                 }
        //                             }
        //                         }
        //                     } else {
        //                         echo ("Не могу найти номенклатуру с ID=" . $nomenklatura->ID);
        //                     }
        //                 }
        //             }
        //         });

        //         // добавим сотрудников
        //         $sotrudniks = $old_db->table('sotrudnik')->where('ID', '>', 1)->get();
        //         foreach ($sotrudniks as $sotrudnik) {
        //             $move_fields = [
        //                 "FstName" => "sure_name",
        //                 "Name" => "first_name",
        //                 "FatName" => "patronymic",
        //                 "BirthDay" => NULL,
        //                 "Dolzh" => NULL
        //             ];

        //             if (!isset($move_array["sotrudnik"][$sotrudnik->ID])) {
        //                 $normalize_data = [];
        //                 $insert_data = [];
        //                 foreach ($move_fields as $field_old => $field_new) {
        //                     $val = $this->normalize_text($sotrudnik->$field_old);
        //                     $normalize_data[$field_old] = $val;
        //                     if (!is_null($field_new)) $insert_data[$field_new] = $val;
        //                 }
        //                 // если указано день рождения
        //                 if (!is_null($sotrudnik->BirthDay) && $sotrudnik->BirthDay != "0000-00-00") $insert_data["birthday"] = $sotrudnik->BirthDay;
        //                 if ($sotrudnik->Deleted == 1) $insert_data["deleted_at"] = "2001-01-01";
        //                 // выберем поле name
        //                 $insert_data["name"] = $normalize_data["FstName"];

        //                 // проверим, что сотрудник уже добавлен
        //                 $find_sotrudnik = $new_db->table('sotrudniks')->where('name', $insert_data["name"])->first();
        //                 if ($find_sotrudnik) {
        //                     $move_array["sotrudnik"][$sotrudnik->ID] = $find_sotrudnik->id;
        //                 } else {
        //                     // проверим должность
        //                     if (!is_null($sotrudnik->Dolzh)) {
        //                         // если такая должность уже есть - используем ее id
        //                         if (isset($move_array["firm_positions"][$sotrudnik->Dolzh])) {
        //                             $insert_data["firm_position_id"] = $move_array["firm_positions"][$sotrudnik->Dolzh];
        //                         } else {
        //                             // проверим, может уже вставлена запись с такой должностью
        //                             $find_firm_position = $new_db->table('firm_positions')->where('name', $normalize_data["Dolzh"])->first();
        //                             if ($find_firm_position) {
        //                                 $move_array["firm_positions"][$sotrudnik->Dolzh] = $find_firm_position->id;
        //                             } else {
        //                                 $insert_firm_position_data = [
        //                                     "name" => $normalize_data["Dolzh"]
        //                                 ];
        //                                 $new_firm_position_id = $new_db->table('firm_positions')->insertGetId($insert_firm_position_data);
        //                                 if ($new_firm_position_id) {
        //                                     $move_array["firm_positions"][$sotrudnik->Dolzh] = $new_firm_position_id;
        //                                 } else {
        //                                     dd("can't insert into [firm_positions] data", $insert_firm_position_data);
        //                                 }
        //                             }
        //                         }
        //                     }
        //                     if (isset($move_array["firm_positions"][$sotrudnik->Dolzh])) {
        //                         $insert_data["firm_position_id"] = $move_array["firm_positions"][$sotrudnik->Dolzh];
        //                     } else {
        //                         $insert_data["firm_position_id"] = 1;
        //                     }
        //                     // вносим запись
        //                     $new_id = $new_db->table('sotrudniks')->insertGetId($insert_data);
        //                     if ($new_id) {
        //                         $move_array["sotrudnik"][$sotrudnik->ID] = $new_id;
        //                     } else {
        //                         dd("can't insert into [sotrudniks] data", $insert_data);
        //                     }
        //                 }
        //             }
        //         }


        //         // переносим склады
        //         $sklads = $old_db->table('sklad')->get();
        //         foreach ($sklads as $sklad) {
        //             $move_fields = [
        //                 "Name" => "name",
        //                 // "Keeper" => "keeper_id", //складарь
        //                 // "KomCh1" => "commission_member1",
        //                 // "KomCh2" => "commission_member2",
        //                 // "KomPred" => "commission_chairman"
        //             ];

        //             if (!isset($move_array["sklad"][$sklad->ID])) {
        //                 $normalize_data = [];
        //                 $insert_data = [];
        //                 foreach ($move_fields as $field_old => $field_new) {
        //                     $val = $this->normalize_text($sklad->$field_old);
        //                     $normalize_data[$field_old] = $val;
        //                     $insert_data[$field_new] = $val;
        //                 }
        //                 if ($sklad->Deleted == 1) $insert_data["deleted_at"] = "2001-01-01";
        //                 // селекты
        //                 if ($sklad->Keeper > 0 && isset($move_array["sotrudnik"][$sklad->Keeper])) {
        //                     $insert_data["keeper_id"] = $move_array["sotrudnik"][$sklad->Keeper];
        //                 }
        //                 if ($sklad->KomCh1 > 0 && isset($move_array["sotrudnik"][$sklad->KomCh1])) {
        //                     $insert_data["commission_member1"] = $move_array["sotrudnik"][$sklad->KomCh1];
        //                 }
        //                 if ($sklad->KomCh2 > 0 && isset($move_array["sotrudnik"][$sklad->KomCh2])) {
        //                     $insert_data["commission_member2"] = $move_array["sotrudnik"][$sklad->KomCh2];
        //                 }
        //                 if ($sklad->KomPred > 0 && isset($move_array["sotrudnik"][$sklad->KomPred])) {
        //                     $insert_data["commission_chairman"] = $move_array["sotrudnik"][$sklad->KomPred];
        //                 }

        //                 $find_sklad = $new_db->table('sklads')->where('name', $normalize_data["Name"])->first();
        //                 if ($find_sklad) {
        //                     $move_array["sklad"][$manufacturer->ID] = $find_sklad->id;
        //                 } else {
        //                     $new_id = $new_db->table('sklads')->insertGetId($insert_data);
        //                     if ($new_id) {
        //                         $move_array["sklad"][$manufacturer->ID] = $new_id;
        //                     } else {
        //                         dd("can't insert into [sklads] data", $insert_data);
        //                     }
        //                 }
        //             }
        //         }



        //         // 3. Перенесем группы
        //         // все старые группы
        //         $groups = $old_db->table('groups')->whereIn("ModuleID", $modules_array)->get();
        //         foreach ($groups as $group) {
        //             $move_fields = [
        //                 "Name" => "name",
        //             ];

        //             if (!isset($move_array["groups"][$group->ID])) {
        //                 $normalize_data = [];
        //                 $insert_data = [];
        //                 foreach ($move_fields as $field_old => $field_new) {
        //                     $val = $this->normalize_text($group->$field_old);
        //                     $normalize_data[$field_old] = $val;
        //                     $insert_data[$field_new] = $val;
        //                 }
        //                 if ($group->Deleted == 1) $insert_data["deleted_at"] = "2001-01-01";

        //                 $find_tag = $new_db->table('tags')->where('name', $normalize_data["Name"])->first();
        //                 if ($find_tag) {
        //                     $move_array["groups"][$group->ID] = $find_tag->id;
        //                 } else {
        //                     $new_id = $new_db->table('tags')->insertGetId($insert_data);
        //                     if ($new_id) {
        //                         $move_array["groups"][$group->ID] = $new_id;
        //                     } else {
        //                         dd("can't insert into [tags] data", $insert_data);
        //                     }
        //                 }
        //             }
        //         }
        //         // внесем полиморфные связи
        //         $groups_items = $old_db->table('groups_items')->whereIn("ModuleID", $modules_array)->get();
        //         foreach ($groups_items as $groups_item) {
        //             $module_id = $groups_item->ModuleID;
        //             $model = $modules[$module_id]["model"];
        //             $old_table_name = $modules[$module_id]["old_table"];

        //             if (!isset($move_array["groups_items"][$groups_item->ID])) {
        //                 if (isset($move_array["groups"][$groups_item->GroupID])) {
        //                     unset($new_field_id);
        //                     if (isset($move_array[$old_table_name][$groups_item->FieldID])) {
        //                         $new_field_id = $move_array[$old_table_name][$groups_item->FieldID];
        //                     } else {
        //                         // dd("При вставке группы не удалось найти номенклатуру с ID " . $groups_item->FieldID);
        //                     }
        //                     if (isset($new_field_id)) {
        //                         $tag_id = $move_array["groups"][$groups_item->GroupID];
        //                         // может уже вставлена такая группа
        //                         $find_table_tag = $new_db->table('table_tags')->where('tag_id', $tag_id)
        //                             ->where("table_type", $model)->where("table_id", $new_field_id)->first();
        //                         if ($find_table_tag) {
        //                             $move_array["groups_items"][$groups_item->ID] = $find_table_tag->id;
        //                         } else {
        //                             $insert_data = [
        //                                 "tag_id" => $tag_id,
        //                                 "table_type" => $model,
        //                                 "table_id" => $new_field_id
        //                             ];
        //                             // dd($insert_data);
        //                             $new_id = $new_db->table('table_tags')->insertGetId($insert_data);
        //                             if ($new_id) {
        //                                 $move_array["groups_items"][$groups_item->ID] = $new_id;
        //                             } else {
        //                                 dd("can't insert into [table_tags] data", $insert_data);
        //                             }
        //                         }
        //                     }
        //                 } else {
        //                     echo ("Не удалось найти группу с ID " . $groups_item->GroupID);
        //                 }
        //             }
        //         }

        //         // 4. Перенесем файлы
        //         // копируем все файлы в корень стораджа
        //         // /abp/www/db/repository/www/files и /abp/www/db/repository/www/docfiles

        //         // сольем файлы и картинки в единую таблицу
        //         // 4.1 картинки
        //         $pictures = $old_db->table('pictures')->whereIn("ModuleID", $modules_array)->get();
        //         foreach ($pictures as $picture) {
        //             $module_id = $picture->ModuleID;
        //             $model = $modules[$module_id]["model"];
        //             $old_table_name = $modules[$module_id]["old_table"];

        //             unset($new_field_id);
        //             if (!isset($move_array["pictures"][$picture->ID])) {
        //                 // может уже добавлен такой файл в новую БД
        //                 $find_file = $new_db->table('files')->where('filename', $picture->Picture)->first();
        //                 if ($find_file) {
        //                     $move_array["pictures"][$picture->ID] = $find_file->id;
        //                 } else {
        //                     if (isset($move_array[$old_table_name][$picture->FieldID])) {
        //                         $new_field_id = $move_array[$old_table_name][$picture->FieldID];
        //                     } else {
        //                         echo ("При вставке изображения не удалось найти номенклатуру с ID " . $picture->FieldID);
        //                     }
        //                     if (isset($new_field_id)) {
        //                         $file_with_ext = explode(".", $picture->Picture);
        //                         $ext = end($file_with_ext);

        //                         $insert_data = [
        //                             "name" => "Картинка " . $picture->ID,
        //                             "folder" => "image",
        //                             "filename" => $picture->Picture,
        //                             "file_type_id" => 1, // image
        //                             "file_driver_id" => 3, // local
        //                             "uid" => "image/" . $picture->Picture,
        //                             "extension" => $ext,
        //                             "is_main" => $picture->MainImage,
        //                             "table_type" => $model,
        //                             "table_id" => $new_field_id
        //                         ];

        //                         $new_id = $new_db->table('files')->insertGetId($insert_data);
        //                         if ($new_id) {
        //                             $move_array["pictures"][$picture->ID] = $new_id;
        //                         } else {
        //                             dd("can't insert into [files] data", $insert_data);
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //         // 4.2 файлы (в старой базе только список)
        //         $files = $old_db->table('files')->get();
        //         foreach ($files as $file) {
        //             if (!isset($move_array["files"][$file->ID])) {
        //                 // имя файла без папки
        //                 $file_name = substr($file->FileName, 9);
        //                 // может уже добавлен такой файл в новую БД
        //                 $find_file = $new_db->table('files')->where('filename', $file_name)->first();
        //                 if ($find_file) {
        //                     $move_array["files"][$file->ID] = $find_file->id;
        //                 } else {
        //                     $file_with_ext = explode(".", $file_name);
        //                     $ext = end($file_with_ext);

        //                     $insert_data = [
        //                         "name" => $file->Name,
        //                         "folder" => "document",
        //                         "filename" => $file_name,
        //                         "file_type_id" => 3, // list
        //                         "file_driver_id" => 3, // local
        //                         "uid" => "document/" . $file_name,
        //                         "extension" => $ext,
        //                         "is_main" => 0,
        //                         "table_type" => "",
        //                         "table_id" => 1
        //                     ];

        //                     $new_id = $new_db->table('files')->insertGetId($insert_data);
        //                     if ($new_id) {
        //                         $move_array["files"][$file->ID] = $new_id;
        //                     } else {
        //                         dd("can't insert into [files] data", $insert_data);
        //                     }
        //                 }
        //             }
        //         }
        //         // полиморфные связи на файлы
        //         $file_items = $old_db->table('file_items')->whereIn("ModuleID", $modules_array)->get();
        //         foreach ($file_items as $file_item) {
        //             $module_id = $file_item->ModuleID;
        //             $model = $modules[$module_id]["model"];
        //             $old_table_name = $modules[$module_id]["old_table"];

        //             if (!isset($move_array["file_items"][$file_item->ID])) {
        //                 if (isset($move_array["files"][$file_item->FileID])) {
        //                     unset($new_field_id);

        //                     if (isset($move_array[$old_table_name][$file_item->FieldID])) {
        //                         $new_field_id = $move_array[$old_table_name][$file_item->FieldID];
        //                     } else {
        //                         echo ("При вставке файла не удалось найти номенклатуру с ID " . $file_item->FieldID);
        //                     }
        //                     if (isset($new_field_id)) {
        //                         $file_id = $move_array["files"][$file_item->FileID];
        //                         // может уже вставлена такая группа
        //                         $find_file_list = $new_db->table('file_lists')->where('file_id', $file_id)
        //                             ->where("table_type", $model)->where("table_id", $new_field_id)->first();
        //                         if ($find_file_list) {
        //                             $move_array["file_items"][$file_item->ID] = $find_file_list->id;
        //                         } else {
        //                             $insert_data = [
        //                                 "file_id" => $file_id,
        //                                 "table_type" => $model,
        //                                 "table_id" => $new_field_id
        //                             ];
        //                             $new_id = $new_db->table('file_lists')->insertGetId($insert_data);
        //                             if ($new_id) {
        //                                 $move_array["file_items"][$file_item->ID] = $new_id;
        //                             } else {
        //                                 dd("can't insert into [file_lists] data", $insert_data);
        //                             }
        //                         }
        //                     }
        //                 } else {
        //                     echo ("Не удалось найти файл с ID " . $groups_item->GroupID);
        //                 }
        //             }
        //         }






        // xdebug_info();

        //         dd($move_array);
        return view('home', ["user" => $user]);

    }

    //     public function normalize_text($text)
    //     {
    //         $res = "" . $text;
    //         $res = str_replace("___PlUs___", "+", $res);
    //         $res = str_replace("___FlSh___", "#", $res);
    //         $res = str_replace("___AmP___", "&", $res);
    //         $res = str_replace("___QsT___", "?", $res);
    //         return trim(html_entity_decode($res));
    //     }
}
