<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Notifications\Sync1CNotification;


class LoadDataFromOldDB implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // логи работы скрипта
    private $logs = [];
    // таймаут - время на исполнение скрипта 20 мин
    public $timeout = 1200;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // ini_set('max_execution_time', '0');
        // set_time_limit(0);

        $old_db = DB::connection('moydodyr');
        $new_db = DB::connection('db1');

        $this->log("Начало импорта данных");

        // очистка таблиц и дефолтные значения
        $truncate_tables = [
            "sklad_register", "sklad_receives", "sklad_receive_items", "sklad_moves", "kontragents",
            "sklad_move_items", "productions", "production_replaces", "production_items", "production_components",
            "manufacturers", "nomenklatura", "recipes", "recipe_items", "sotrudniks", "firm_positions", "sklads",
            "tags", "table_tags", "files", "file_lists"
        ];
        $dont_add_default_tables = [
            "sklad_register"
        ];
        foreach ($truncate_tables as $truncate_table) {
            // очистка
            $new_db->table($truncate_table)->truncate();
            // создание дефолтных значений "Не задано"
            if (!in_array($truncate_table, $dont_add_default_tables)) {
                $default_data = [
                    "id" => 1,
                    "name" => "Не выбрано",
                    "is_protected" => 1
                ];
                switch ($truncate_table) {
                    case "files": {
                            $default_data += [
                                "table_type" => "",
                                "table_id" => 1,
                                "filename" => ""
                            ];
                        }
                        break;
                    case "file_lists":
                    case "table_tags": {
                            $default_data += [
                                "table_type" => "",
                                "table_id" => 1
                            ];
                        }
                        break;
                    case "sklad_moves": {
                            $default_data += [
                                "transitable_type" => "",
                                "transitable_id" => 1
                            ];
                        }
                        break;
                }
                $new_db->table($truncate_table)->insert($default_data);
            }
        }
        $this->log("Таблицы очищены");

        $receives = [
            ["name" => "Ввод начальных остатков (материалы)", "IsSostav" => 0, "IsManufactured" => 0],
            ["name" => "Ввод начальных остатков (закупка ГИ)", "IsSostav" => 1, "IsManufactured" => 0],
            ["name" => "Ввод начальных остатков (произведенные)", "IsSostav" => 1, "IsManufactured" => 1],
        ];

        $move_array = [
            "edism" => [39 => 1],
        ];


        // соответствие модулей старой базы моделям новой
        $modules = [
            3 => [
                "table" => "nomenklatura",
                "old_table" => "nomenklatura",
                "model" => "App\Nomenklatura"
            ],
            5 => [
                "table" => "sotrudniks",
                "old_table" => "sotrudnik",
                "model" => "App\Sotrudnik"
            ],
            12 => [
                "table" => "sklads",
                "old_table" => "sklad",
                "model" => "App\Sklad"
            ],
            13 => [
                "table" => "kontragents",
                "old_table" => "kontragents",
                "model" => "App\Kontragent"
            ],
            17 => [
                "table" => "Инструменты",
                "old_table" => "instrument",
                "model" => NULL
            ],
            27 => [
                "table" => "Файлы прикрепляемые",
                "old_table" => "",
                "model" => NULL
            ],
            64 => [
                "table" => "manufacturers",
                "old_table" => "manufacturers",
                "model" => "App\Manufacturer"
            ],
        ];
        $modules_array = collect($modules)->filter(function ($module) {
            return !is_null($module["model"]);
        })->keys()->toArray();


        // перенос справочника номенклатур
        // 0. переносим единицы измерения
        // все старые единицы измерения
        $edisms = $old_db->table('edism')->where('Deleted', 0)->get();
        foreach ($edisms as $edism) {
            // OKEI -> okei
            // ID -> id
            // ShortName -> name
            // Name -> comment
            $move_fields = [
                "OKEI" => "okei",
                "ShortName" => "name",
                "Name" => "comment",
                "ID" => "old_id"
            ];

            if (!isset($move_array["edism"][$edism->ID])) {
                if ($edism->OKEI) {
                    $normalize_data = [];
                    $insert_data = [];
                    foreach ($move_fields as $field_old => $field_new) {
                        $val = $this->normalize_text($edism->$field_old);
                        $normalize_data[$field_old] = $val;
                        $insert_data[$field_new] = $val;
                    }

                    $find_edism = $new_db->table('ed_ism')->where('okei', $normalize_data["OKEI"])->first();
                    if ($find_edism) {
                        $move_array["edism"][$edism->ID] = $find_edism->id;
                    } else {
                        $new_id = $new_db->table('ed_ism')->insertGetId($insert_data);
                        if ($new_id) {
                            $move_array["edism"][$edism->ID] = $new_id;
                        } else {
                            $this->log("can't insert into [ed_ism] data", $insert_data);
                        }
                    }
                }
            }
        }
        $this->log("Единицы измерения перенесены");

        // 1. переносим производителей
        // все старые производители
        $manufacturers = $old_db->table('manufacturers')->get();
        foreach ($manufacturers as $manufacturer) {
            $move_fields = [
                "Name" => "name",
                "ID" => "old_id"
            ];

            if (!isset($move_array["manufacturers"][$manufacturer->ID])) {
                $normalize_data = [];
                $insert_data = [];
                foreach ($move_fields as $field_old => $field_new) {
                    $val = $this->normalize_text($manufacturer->$field_old);
                    $normalize_data[$field_old] = $val;
                    $insert_data[$field_new] = $val;
                }
                if ($manufacturer->Deleted == 1) $insert_data["deleted_at"] = "2001-01-01";

                $find_manufacturer = $new_db->table('manufacturers')->where('name', $normalize_data["Name"])->first();
                if ($find_manufacturer) {
                    $move_array["manufacturers"][$manufacturer->ID] = $find_manufacturer->id;
                } else {
                    $new_id = $new_db->table('manufacturers')->insertGetId($insert_data);
                    if ($new_id) {
                        $move_array["manufacturers"][$manufacturer->ID] = $new_id;
                    } else {
                        $this->log("can't insert into [manufacturers] data", $insert_data);
                    }
                }
            }
        }
        $this->log("Производители перенесены");

        // 2. перенос номенклатур
        // $nomenklaturas = $old_db->table('nomenklatura')->where('Deleted', 0)->get();
        // foreach ($nomenklaturas as $nomenklatura) {
        $old_db->table('nomenklatura')->orderBy('id')->chunk(10, function ($nomenklaturas) use (&$move_array, $new_db, $old_db) {
            foreach ($nomenklaturas as $nomenklatura) {
                $move_fields = [
                    "Artikul" => "artikul",
                    "Name" => NULL,
                    "Descr" => NULL,
                    // "Name"+"Descr" => "name"
                    "Characs" => "description",
                    "Comment" => "comment",
                    "CatNum" => "part_num",
                    "ID" => "old_id"
                ];
                if (!isset($move_array["nomenklatura"][$nomenklatura->ID])) {
                    $normalize_data = [];
                    $insert_data = [];
                    foreach ($move_fields as $field_old => $field_new) {
                        if (is_null($nomenklatura->$field_old)) {
                            $val = NULL;
                        } else {
                            $val = $this->normalize_text($nomenklatura->$field_old);
                        }
                        $normalize_data[$field_old] = $val;
                        if (!is_null($field_new)) $insert_data[$field_new] = $val;
                    }

                    $find_nomenklatura = $new_db->table('nomenklatura')->where('artikul', $normalize_data["Artikul"])->first();
                    if ($find_nomenklatura) {
                        $move_array["nomenklatura"][$nomenklatura->ID] = $find_nomenklatura->id;
                    } else {
                        // предопределенные поля
                        $insert_data["name"] = $normalize_data["Name"] . (is_null($normalize_data["Descr"]) ? '' : " " . $normalize_data["Descr"]);
                        if ($nomenklatura->IsSostav == 1) {
                            $insert_data["doc_type_id"] = 61;
                        } else {
                            $insert_data["doc_type_id"] = 5;
                        }
                        $insert_data["ed_ism_id"] = isset($move_array["edism"][$nomenklatura->EdIsm]) ? $move_array["edism"][$nomenklatura->EdIsm] : 2;
                        $insert_data["is_usluga"] = 0;
                        $insert_data["nds_id"] = 8;
                        if ($nomenklatura->Deleted == 1) $insert_data["deleted_at"] = "2001-01-01";
                        // добавляем данные
                        $new_id = $new_db->table('nomenklatura')->insertGetId($insert_data);
                        if ($new_id) {
                            $move_array["nomenklatura"][$nomenklatura->ID] = $new_id;
                        } else {
                            $this->log("can't insert into [nomenklatura] data", $insert_data);
                        }
                    }
                } else {
                    $new_id = $move_array["nomenklatura"][$nomenklatura->ID];
                }
            }
            // sleep(3);
        });
        $this->log("Номенклатуры перенесены");

        // 2.2 добавим рецептуры
        $old_db->table('nomenklatura')->where('Deleted', 0)->where("IsSostav", 1)->orderBy('id')->chunk(10, function ($nomenklaturas) use (&$move_array, $new_db, $old_db) {
            foreach ($nomenklaturas as $nomenklatura) {
                $recept_items = $old_db->table('recepts')->where('Deleted', 0)->where("NomenklaturaID", $nomenklatura->ID)->get();
                if ($recept_items) {
                    if (isset($move_array["nomenklatura"][$nomenklatura->ID])) {
                        $nomenklatura_id = $move_array["nomenklatura"][$nomenklatura->ID];
                        $default_recipe_name = "Основная";
                        // проверим, есть ли уже созданная основная рецептура
                        $new_recipe = $new_db->table('recipes')->where("nomenklatura_id", $nomenklatura_id)->where("name", $default_recipe_name)->first();
                        if ($new_recipe) {
                            $recipe_id = $new_recipe->id;
                        } else {
                            // создадим основную рецептуру
                            $recipe_data = [
                                "name" => $default_recipe_name,
                                "nomenklatura_id" => $nomenklatura_id
                            ];
                            $recipe_id = $new_db->table('recipes')->insertGetId($recipe_data);
                            if (!$recipe_id) {
                                $this->log("can't insert into [recipes] data", $recipe_data);
                            }
                        }
                        if (isset($recipe_id)) {
                            // добавляем состав рецептуры
                            foreach ($recept_items as $recept_item) {

                                if (!isset($move_array["recipe_items"][$recept_item->ID])) {
                                    if (isset($move_array["nomenklatura"][$recept_item->ComponentID])) {
                                        $nomenklatura_old_id = $move_array["nomenklatura"][$recept_item->ComponentID];
                                        // ищем такую позицию в рецептуре
                                        $recipe_item = $new_db->table('recipe_items')->where("recipe_id", $recipe_id)->where("nomenklatura_id", $nomenklatura_old_id)->first();
                                        if (!$recipe_item) {
                                            $recipe_item_data = [
                                                "recipe_id" => $recipe_id,
                                                "nomenklatura_id" => $nomenklatura_old_id,
                                                "kolvo" => $recept_item->Kolvo,
                                                "old_id" => $recept_item->ID,
                                            ];
                                            $recipe_item_id = $new_db->table('recipe_items')->insertGetId($recipe_item_data);
                                            $move_array["recipe_items"][$recept_item->ID] = $recipe_item_id;
                                            if (!$recipe_item_id) {
                                                $this->log("can't insert into [recipe_items] data", $recipe_item_data);
                                            }
                                        }
                                    } else {
                                        $this->log("Не могу найти номенклатуру с ID " . $recept_item->ComponentID);
                                    }
                                }
                            }
                        }
                    } else {
                        $this->log("Не могу найти номенклатуру с ID=" . $nomenklatura->ID);
                    }
                }
            }
        });
        $this->log("Рецептуры перенесены");

        // добавим сотрудников
        $sotrudniks = $old_db->table('sotrudnik')->where('ID', '>', 1)->get();
        foreach ($sotrudniks as $sotrudnik) {
            $move_fields = [
                "FstName" => "sure_name",
                "Name" => "first_name",
                "FatName" => "patronymic",
                "BirthDay" => NULL,
                "Dolzh" => NULL,
                "ID" => "old_id"
            ];

            if (!isset($move_array["sotrudnik"][$sotrudnik->ID])) {
                $normalize_data = [];
                $insert_data = [];
                foreach ($move_fields as $field_old => $field_new) {
                    $val = $this->normalize_text($sotrudnik->$field_old);
                    $normalize_data[$field_old] = $val;
                    if (!is_null($field_new)) $insert_data[$field_new] = $val;
                }
                // если указано день рождения
                if (!is_null($sotrudnik->BirthDay) && $sotrudnik->BirthDay != "0000-00-00") $insert_data["birthday"] = $sotrudnik->BirthDay;
                if ($sotrudnik->Deleted == 1) $insert_data["deleted_at"] = "2001-01-01";
                // выберем поле name
                $insert_data["name"] = $normalize_data["FstName"];

                // проверим, что сотрудник уже добавлен
                $find_sotrudnik = $new_db->table('sotrudniks')->where('name', $insert_data["name"])->first();
                if ($find_sotrudnik) {
                    $move_array["sotrudnik"][$sotrudnik->ID] = $find_sotrudnik->id;
                } else {
                    // проверим должность
                    if (!is_null($sotrudnik->Dolzh)) {
                        // если такая должность уже есть - используем ее id
                        if (isset($move_array["firm_positions"][$sotrudnik->Dolzh])) {
                            $insert_data["firm_position_id"] = $move_array["firm_positions"][$sotrudnik->Dolzh];
                        } else {
                            // проверим, может уже вставлена запись с такой должностью
                            $find_firm_position = $new_db->table('firm_positions')->where('name', $normalize_data["Dolzh"])->first();
                            if ($find_firm_position) {
                                $move_array["firm_positions"][$sotrudnik->Dolzh] = $find_firm_position->id;
                            } else {
                                $insert_firm_position_data = [
                                    "name" => $normalize_data["Dolzh"]
                                ];
                                $new_firm_position_id = $new_db->table('firm_positions')->insertGetId($insert_firm_position_data);
                                if ($new_firm_position_id) {
                                    $move_array["firm_positions"][$sotrudnik->Dolzh] = $new_firm_position_id;
                                } else {
                                    $this->log("can't insert into [firm_positions] data", $insert_firm_position_data);
                                }
                            }
                        }
                    }
                    if (isset($move_array["firm_positions"][$sotrudnik->Dolzh])) {
                        $insert_data["firm_position_id"] = $move_array["firm_positions"][$sotrudnik->Dolzh];
                    } else {
                        $insert_data["firm_position_id"] = 1;
                    }
                    // вносим запись
                    $new_id = $new_db->table('sotrudniks')->insertGetId($insert_data);
                    if ($new_id) {
                        $move_array["sotrudnik"][$sotrudnik->ID] = $new_id;
                    } else {
                        $this->log("can't insert into [sotrudniks] data", $insert_data);
                    }
                }
            }
        }
        $this->log("Сотрудники перенесены");


        // переносим склады
        $sklads = $old_db->table('sklad')->get();
        foreach ($sklads as $sklad) {
            $move_fields = [
                "Name" => "name",
                "ID" => "old_id"
                // "Keeper" => "keeper_id", //складарь
                // "KomCh1" => "commission_member1",
                // "KomCh2" => "commission_member2",
                // "KomPred" => "commission_chairman"
            ];

            if (!isset($move_array["sklad"][$sklad->ID])) {
                $normalize_data = [];
                $insert_data = [];
                foreach ($move_fields as $field_old => $field_new) {
                    $val = $this->normalize_text($sklad->$field_old);
                    $normalize_data[$field_old] = $val;
                    $insert_data[$field_new] = $val;
                }
                if ($sklad->Deleted == 1) $insert_data["deleted_at"] = "2001-01-01";
                // селекты
                if ($sklad->Keeper > 0 && isset($move_array["sotrudnik"][$sklad->Keeper])) {
                    $insert_data["keeper_id"] = $move_array["sotrudnik"][$sklad->Keeper];
                }
                if ($sklad->KomCh1 > 0 && isset($move_array["sotrudnik"][$sklad->KomCh1])) {
                    $insert_data["commission_member1"] = $move_array["sotrudnik"][$sklad->KomCh1];
                }
                if ($sklad->KomCh2 > 0 && isset($move_array["sotrudnik"][$sklad->KomCh2])) {
                    $insert_data["commission_member2"] = $move_array["sotrudnik"][$sklad->KomCh2];
                }
                if ($sklad->KomPred > 0 && isset($move_array["sotrudnik"][$sklad->KomPred])) {
                    $insert_data["commission_chairman"] = $move_array["sotrudnik"][$sklad->KomPred];
                }

                $find_sklad = $new_db->table('sklads')->where('name', $normalize_data["Name"])->first();
                if ($find_sklad) {
                    $move_array["sklad"][$sklad->ID] = $find_sklad->id;
                } else {
                    $new_id = $new_db->table('sklads')->insertGetId($insert_data);
                    if ($new_id) {
                        $move_array["sklad"][$sklad->ID] = $new_id;
                    } else {
                        $this->log("can't insert into [sklads] data", $insert_data);
                    }
                }
            }
        }
        $this->log("Склады перенесены");

        // 3. Перенесем группы
        // все старые группы
        $groups = $old_db->table('groups')->whereIn("ModuleID", $modules_array)->get();
        foreach ($groups as $group) {
            $move_fields = [
                "Name" => "name",
                "ID" => "old_id"
            ];

            if (!isset($move_array["groups"][$group->ID])) {
                $normalize_data = [];
                $insert_data = [];
                foreach ($move_fields as $field_old => $field_new) {
                    $val = $this->normalize_text($group->$field_old);
                    $normalize_data[$field_old] = $val;
                    $insert_data[$field_new] = $val;
                }
                if ($group->Deleted == 1) $insert_data["deleted_at"] = "2001-01-01";

                $find_tag = $new_db->table('tags')->where('name', $normalize_data["Name"])->first();
                if ($find_tag) {
                    $move_array["groups"][$group->ID] = $find_tag->id;
                } else {
                    $new_id = $new_db->table('tags')->insertGetId($insert_data);
                    if ($new_id) {
                        $move_array["groups"][$group->ID] = $new_id;
                    } else {
                        $this->log("can't insert into [tags] data", $insert_data);
                    }
                }
            }
        }
        // внесем полиморфные связи
        $groups_items = $old_db->table('groups_items')->whereIn("ModuleID", $modules_array)->get();
        foreach ($groups_items as $groups_item) {
            $module_id = $groups_item->ModuleID;
            $model = $modules[$module_id]["model"];
            $old_table_name = $modules[$module_id]["old_table"];

            if (!isset($move_array["groups_items"][$groups_item->ID])) {
                if (isset($move_array["groups"][$groups_item->GroupID])) {
                    unset($new_field_id);
                    if (isset($move_array[$old_table_name][$groups_item->FieldID])) {
                        $new_field_id = $move_array[$old_table_name][$groups_item->FieldID];
                    } else {
                        // $this->log("При вставке группы не удалось найти номенклатуру с ID " . $groups_item->FieldID);
                    }
                    if (isset($new_field_id)) {
                        $tag_id = $move_array["groups"][$groups_item->GroupID];
                        // может уже вставлена такая группа
                        $find_table_tag = $new_db->table('table_tags')->where('tag_id', $tag_id)
                            ->where("table_type", $model)->where("table_id", $new_field_id)->first();
                        if ($find_table_tag) {
                            $move_array["groups_items"][$groups_item->ID] = $find_table_tag->id;
                        } else {
                            $insert_data = [
                                "tag_id" => $tag_id,
                                "table_type" => $model,
                                "table_id" => $new_field_id
                            ];
                            // $this->log($insert_data);
                            $new_id = $new_db->table('table_tags')->insertGetId($insert_data);
                            if ($new_id) {
                                $move_array["groups_items"][$groups_item->ID] = $new_id;
                            } else {
                                $this->log("can't insert into [table_tags] data", $insert_data);
                            }
                        }
                    }
                } else {
                    $this->log("Не удалось найти группу с ID " . $groups_item->GroupID);
                }
            }
        }
        $this->log("Группы перенесены");

        // 4. Перенесем файлы
        // копируем все файлы в корень стораджа
        // /abp/www/db/repository/www/files и /abp/www/db/repository/www/docfiles

        // сольем файлы и картинки в единую таблицу
        // 4.1 картинки
        $pictures = $old_db->table('pictures')->whereIn("ModuleID", $modules_array)->get();
        foreach ($pictures as $picture) {
            $module_id = $picture->ModuleID;
            $model = $modules[$module_id]["model"];
            $old_table_name = $modules[$module_id]["old_table"];

            unset($new_field_id);
            if (!isset($move_array["pictures"][$picture->ID])) {
                // может уже добавлен такой файл в новую БД
                $find_file = $new_db->table('files')->where('filename', $picture->Picture)->first();
                if ($find_file) {
                    $move_array["pictures"][$picture->ID] = $find_file->id;
                } else {
                    if (isset($move_array[$old_table_name][$picture->FieldID])) {
                        $new_field_id = $move_array[$old_table_name][$picture->FieldID];
                    } else {
                        $this->log("При вставке изображения не удалось найти номенклатуру с ID " . $picture->FieldID);
                    }
                    if (isset($new_field_id)) {
                        $file_with_ext = explode(".", $picture->Picture);
                        $ext = end($file_with_ext);

                        $insert_data = [
                            "name" => "Картинка " . $picture->ID,
                            "folder" => "image",
                            "filename" => $picture->Picture,
                            "file_type_id" => 1, // image
                            "file_driver_id" => 3, // local
                            "uid" => "image/" . $picture->Picture,
                            "extension" => $ext,
                            "is_main" => $picture->MainImage,
                            "table_type" => $model,
                            "table_id" => $new_field_id
                        ];

                        $new_id = $new_db->table('files')->insertGetId($insert_data);
                        if ($new_id) {
                            $move_array["pictures"][$picture->ID] = $new_id;
                        } else {
                            $this->log("can't insert into [files] data", $insert_data);
                        }
                    }
                }
            }
        }
        $this->log("Картинки перенесены");

        // 4.2 файлы (в старой базе только список)
        $files = $old_db->table('files')->get();
        foreach ($files as $file) {
            if (!isset($move_array["files"][$file->ID])) {
                // имя файла без папки
                $file_name = substr($file->FileName, 9);
                // может уже добавлен такой файл в новую БД
                $find_file = $new_db->table('files')->where('filename', $file_name)->first();
                if ($find_file) {
                    $move_array["files"][$file->ID] = $find_file->id;
                } else {
                    $file_with_ext = explode(".", $file_name);
                    $ext = end($file_with_ext);

                    $insert_data = [
                        "name" => $file->Name,
                        "folder" => "document",
                        "filename" => $file_name,
                        "file_type_id" => 3, // list
                        "file_driver_id" => 3, // local
                        "uid" => "document/" . $file_name,
                        "extension" => $ext,
                        "is_main" => 0,
                        "table_type" => "",
                        "table_id" => 1
                    ];

                    $new_id = $new_db->table('files')->insertGetId($insert_data);
                    if ($new_id) {
                        $move_array["files"][$file->ID] = $new_id;
                    } else {
                        $this->log("can't insert into [files] data", $insert_data);
                    }
                }
            }
        }
        // полиморфные связи на файлы
        $file_items = $old_db->table('file_items')->whereIn("ModuleID", $modules_array)->get();
        foreach ($file_items as $file_item) {
            $module_id = $file_item->ModuleID;
            $model = $modules[$module_id]["model"];
            $old_table_name = $modules[$module_id]["old_table"];

            if (!isset($move_array["file_items"][$file_item->ID])) {
                if (isset($move_array["files"][$file_item->FileID])) {
                    unset($new_field_id);

                    if (isset($move_array[$old_table_name][$file_item->FieldID])) {
                        $new_field_id = $move_array[$old_table_name][$file_item->FieldID];
                    } else {
                        $this->log("При вставке файла не удалось найти номенклатуру с ID " . $file_item->FieldID);
                    }
                    if (isset($new_field_id)) {
                        $file_id = $move_array["files"][$file_item->FileID];
                        // может уже вставлена такая группа
                        $find_file_list = $new_db->table('file_lists')->where('file_id', $file_id)
                            ->where("table_type", $model)->where("table_id", $new_field_id)->first();
                        if ($find_file_list) {
                            $move_array["file_items"][$file_item->ID] = $find_file_list->id;
                        } else {
                            $insert_data = [
                                "file_id" => $file_id,
                                "table_type" => $model,
                                "table_id" => $new_field_id
                            ];
                            $new_id = $new_db->table('file_lists')->insertGetId($insert_data);
                            if ($new_id) {
                                $move_array["file_items"][$file_item->ID] = $new_id;
                            } else {
                                $this->log("can't insert into [file_lists] data", $insert_data);
                            }
                        }
                    }
                } else {
                    $this->log("Не удалось найти файл с ID " . $groups_item->GroupID);
                }
            }
        }
        $this->log("Списки файлов перенесены");

        // получим перечень складов, на которых есть остатки
        $sklads = $old_db->table('_sklad_remains')->select('SkladID')->distinct()->get();
        // № накладной
        $doc_num = 1;
        foreach ($sklads as $sklad) {
            $sklad_id = $sklad->SkladID;

            // для каждого типа поступлений
            foreach ($receives as $receive) {

                // получим все соответствующие критериям номенклатуры
                $data = $old_db->table('_sklad_remains')
                    ->leftJoin('_sklad_receive_items', '_sklad_remains.SkladReceiveItemsID', '=', '_sklad_receive_items.id')
                    ->leftJoin('sklad', '_sklad_remains.SkladID', '=', 'sklad.id')
                    ->leftJoin('nomenklatura', '_sklad_receive_items.NomenklaturaID', '=', 'nomenklatura.id')
                    ->where('_sklad_receive_items.IsSostav', $receive["IsSostav"])->where('_sklad_receive_items.IsManufactured', $receive["IsManufactured"])
                    ->where('_sklad_remains.SkladID', $sklad_id)
                    ->select('sklad.name', '_sklad_remains.SkladID', '_sklad_receive_items.NomenklaturaID', 'nomenklatura.Name', '_sklad_remains.Kolvo', '_sklad_receive_items.UnitPrice');

                // если есть остатки
                if ($data->count() > 0) {

                    // переменные
                    $firm_id = 6;
                    $valuta_id = 6;
                    $nds_id = 8;
                    $nds_stavka = 0.2;

                    // заносим позиции
                    $data->orderBy('_sklad_remains.id')->chunk(300, function ($sklad_receive_items) use (&$doc_num, $firm_id, $sklad_id, $valuta_id, $move_array, $receive, $new_db, $nds_id, $nds_stavka) {
                        $next_doc_num = $doc_num++;
                        // создаем поступление несборной номенклатуры (материалы)
                        $receive_data = [
                            "comment" => $receive["name"],
                            "doc_num" => $next_doc_num,
                            "doc_date" => "2020-01-01",
                            "is_active" => 0,
                            "firm_id" => $firm_id,
                            "sklad_id" => isset($move_array["sklad"][$sklad_id]) ? $move_array["sklad"][$sklad_id] : 1,
                            "kontragent_id" => 1,
                            "dogovor_id" => 1,
                            "valuta_id" => $valuta_id,
                            "in_doc_num" => $next_doc_num,
                            "in_doc_date" => "2020-01-01",
                            "kontragent_otpravitel_id" => 1,
                            "firm_poluchatel_id" => $firm_id,
                            "price_include_nds" => 0,
                            "sum_include_nds" => 0,
                            "summa" => 0
                        ];
                        $receive_id = $new_db->table('sklad_receives')->insertGetId($receive_data);
                        $receive_sum = 0;

                        foreach ($sklad_receive_items as $sri) {
                            $sum_item = $sri->Kolvo * $sri->UnitPrice;
                            $receive_sum += $sum_item;
                            $sri_data = [
                                "sklad_receive_id" => $receive_id,
                                "nomenklatura_id" => isset($move_array["nomenklatura"][$sri->NomenklaturaID]) ? $move_array["nomenklatura"][$sri->NomenklaturaID] : 1,
                                "nomenklatura_name" => $sri->Name,
                                "kolvo" => $sri->Kolvo,
                                "price" => $sri->UnitPrice,
                                "summa" => $sum_item,
                                "summa_nds" => 0,
                                "nds_id" => $nds_id,
                                "stavka_nds" => $nds_stavka
                            ];
                            // сохраняем
                            $new_db->table('sklad_receive_items')->insertGetId($sri_data);
                        }
                        // сохраняем сумму накладной
                        $new_db->table('sklad_receives')->whereId($receive_id)->update(["summa" => $receive_sum]);
                    });
                }
            }
        }
        $this->log("Остатки перенесены");
        // отправляем сообщение с результатом
        $this->log("Импорт данных завершен " . date('d.m.Y в H:i:s') . " (UTC)");
        $admins = User::where('is_admin', 1)->get();
        foreach ($admins as $email_user) {
            $email_user->notify((new Sync1CNotification($this->logs)));
        }
    }

    public function normalize_text($text)
    {
        $res = "" . $text;
        $res = str_replace("___PlUs___", "+", $res);
        $res = str_replace("___FlSh___", "#", $res);
        $res = str_replace("___AmP___", "&", $res);
        $res = str_replace("___QsT___", "?", $res);
        return trim(html_entity_decode($res));
    }

    public function log($str, $arr_data = null)
    {
        $log = $str . ($arr_data ? ' ' . implode(",", $arr_data) : '');
        $this->logs[] = $log;
    }
}