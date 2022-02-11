<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Faker\Generator as Faker;

use Tests\TestCase;
use App\Nomenklatura;
use App\User;
use App\EdIsm;
use App\Tag;
use App\DocType;
use App\Manufacturer;
use App\NDS;
use Illuminate\Support\Str;
use App\TableTag;
use PhpParser\Node\Stmt\TryCatch;

class ApiTest extends TestCase
{

    private $api_pref = '/api/v1/';
    // префикс наименования тестируемых данных
    private static $name_pref = '___abp-';
    private static $comment = '___abp-';
    // добавляем тестовые номенклатуры
    private $nomenklatura_count = 20;
    // добавляем тестовые производители
    private $manufacturers_count = 2;
    // количество тестовых групп
    private $groups_count = 3;
    // связные таблицы
    private $doc_types;
    private $manufacturers;
    private $nds;
    private $ed_ism;
    // users
    private $admin_user;
    // faker
    private $faker;

    // setUp
    protected function setUp(): void
    {
        parent::setUp();
        $this->doc_types = DocType::select('id')->get()->pluck('id');
        $this->manufacturers = Manufacturer::select('id')->get()->pluck('id');
        $this->nds = NDS::where('name', 'БезНДС')->orWhere('name', 'НДС20')->get()->pluck('id');
        $this->ed_ism = EdIsm::select('id')->get()->pluck('id');
        $this->faker = new Faker();
        // админ
        $this->admin_user = User::where('email', 'garikgsi@yandex.ru')->first();
    }

    // функция очистки бд
    public static function cleanDb()
    {
        // получим все вставленные группы
        $groups = Tag::where("name", 'like', self::$name_pref . '%');
        // массив id всех вставленных групп
        $groups_ids = $groups->get()->pluck('id')->all();
        TableTag::whereIn('tag_id', $groups_ids)->forceDelete();
        $groups->forceDelete();

        $tables = [Nomenklatura::class, Manufacturer::class];
        foreach ($tables as $table) {
            $table::where("comment", self::$comment)->forceDelete();
        }
    }

    // после всех тестов
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::cleanDb();
    }

    // тест добавления номенклатуры
    public function testCreateNomenklatura()
    {
        // номенклатура до момента вставки записи
        $nomenklatura_before = Nomenklatura::where('comment', self::$comment)->get();

        // проверка добавления данных в справочники
        for ($i = 0; $i < $this->nomenklatura_count; $i++) {
            $data = [
                "name" => self::$name_pref . Str::random(10),
                "doc_type_id" => $this->doc_types->random(), //"Вид номенклатуры"
                "ed_ism_id" => $this->ed_ism->random(), //"Единица измерения"
                "description" => Str::random(10), //"Описание"
                "part_num" => Str::random(10), //"Part №"
                "manufacturer_id" => $this->manufacturers->random(), //"Производитель"
                "artikul" => Str::random(5), //"Артикул"
                "price" => $this->faker->randomFloat(2), // "Цена без НДС"
                "nds_id" => $this->nds->random(), //"Ставка НДС"
                "is_usluga" => 0, //"Услуга"
                "comment" => self::$comment
            ];
            $response = $this->actingAs($this->admin_user, 'api')->json('POST', $this->api_pref . 'nomenklatura', $data);
            $response->assertStatus(200)
                ->assertJson([
                    "is_error" => false,
                    "count" => 1,
                ]);
        }
        // проверим, что создана номенклатура
        $nomenklatura_after = Nomenklatura::where('comment', self::$comment)->get();
        $this->assertTrue($nomenklatura_after->count() - $nomenklatura_before->count() == $this->nomenklatura_count);
    }

    // тест добавления производителей
    public function testCreateManufacturers()
    {
        // производители до момента вставки записи
        $manufacturers_before = Manufacturer::where('comment', self::$comment)->get();

        for ($i = 0; $i < $this->manufacturers_count; $i++) {
            $data = [
                "name" => self::$name_pref . Str::random(10),
                "comment" => self::$comment
            ];
            $url = $this->api_pref . 'manufacturers';
            $response1 = $this->actingAs($this->admin_user, 'api')->json('POST', $url, $data);
            $response1->assertStatus(200)
                ->assertJson([
                    "is_error" => false,
                    "count" => 1,
                ]);
        }
        // проверим, что созданы производители
        $manufacturers_after = Manufacturer::where('comment', self::$comment)->get();
        $this->assertTrue($manufacturers_after->count() - $manufacturers_before->count() == $this->manufacturers_count);
    }

    // тест добавления групп
    public function testCreateGroups()
    {
        // ГРУППЫ -------
        // все группы до вставки
        $groups_before = Tag::where("name", 'like', self::$name_pref . '%')->get();
        // добавляем группу в первую вставленную номенклатуру
        $nomenklatura = Nomenklatura::where('comment', self::$comment)->get();
        // Route::post('/groups/{table}/{id}', 'APIController@add_group')->middleware('auth:api'); // вставить новую группу и присвоить ее id table-id
        $data = [];
        for ($i = 0; $i < $this->groups_count; $i++) {
            $data[] = self::$name_pref . Str::random(10);
        }
        $response = $this->actingAs($this->admin_user, 'api')->json('POST', $this->api_pref . 'groups/nomenklatura/' . $nomenklatura->first()->id, ["data" => $data]);
        // dd($response);
        $response->assertStatus(201);
        // получим все вставленные группы
        $groups_after = Tag::where("name", 'like', self::$name_pref . '%')->get();
        // // проверим кол-во вставленных групп
        $this->assertTrue($this->groups_count == $groups_after->count() - $groups_before->count());
        // добавим первую группу 3 вставленным номенклатурам, начиная со второй, а вторую следующим 4
        $group1 = $groups_after->take(1)->first();
        $nomenklatura_group1 = $nomenklatura->skip(1)->take(3);
        $data = [];
        foreach ($nomenklatura_group1 as $n) {
            $data[] = [
                'tag_id' => $group1->id,
                'tag' => $group1->name
            ];
            $url =  $this->api_pref . 'groups/nomenklatura/' . $n->id;
            $response = $this->actingAs($this->admin_user, 'api')->json('POST', $url, ['data' => $data]);
            // dd($response);
            $response->assertStatus(201);
        }
        $group2 = $groups_after->skip(1)->take(1)->first();
        $nomenklatura_group2 = $nomenklatura->skip(4)->take(4);
        $data = [];
        foreach ($nomenklatura_group2 as $n) {
            $data[] = [
                'tag_id' => $group2->id,
                'tag' => $group2->name
            ];
            $response = $this->actingAs($this->admin_user, 'api')->json('POST', $this->api_pref . 'groups/nomenklatura/' . $n->id, ['data' => $data])->assertStatus(201);
        }
    }

    // тест изменения
    public function testUpdateNomenklatura()
    {
        $nomenklatura = Nomenklatura::where('comment', self::$comment)->get();
        // изменим производителя у номенклатуры
        $manufacturers = Manufacturer::where('comment', self::$comment)->get();
        // dd($manufacturers->toArray());
        $manufacturer1 = $manufacturers->take(1)->first();
        // var_dump($manufacturer1->toArray());
        // проверка изменения
        // Route::put('/{table}/{id}', 'APIController@update')->middleware('auth:api');;
        // изменяем производителя у 2 и 3 номенклатуры на первого вставленного производителя
        $nomenklatura_manufacturers = $nomenklatura->skip(1)->take(2);
        foreach ($nomenklatura_manufacturers as $nm) {
            $nm->manufacturer_id = $manufacturer1->id;
            $data = $nm->toArray();
            $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $this->api_pref . 'nomenklatura/' . $nm->id, $data)->assertStatus(202)
                ->assertJson([
                    "is_error" => false,
                    "count" => 1,
                ]);

            // значения в БД должны совпадать
            $this->assertTrue(Nomenklatura::find($nm->id)->manufacturer_id == $manufacturer1->id);
        }
    }





    // /**
    //  * A basic feature test example.
    //  *
    //  * @return void
    //  */
    // public function testComplex()
    // {
    //     // // права доступа
    //     // Route::get('/roles/{id?}', 'PermissionsController@roles_index')->middleware('auth:api');
    //     // Route::get('/users', 'UserController@index')->middleware('auth:api');
    //     // Route::get('/users/{id}', 'UserController@show')->middleware('auth:api');
    //     // Route::get('/user_roles/{id?}', 'PermissionsController@user_roles')->middleware('auth:api');
    //     // Route::post('/user_roles/{id?}', 'PermissionsController@set_user_roles')->middleware('auth:api');
    //     // // файлы
    //     // Route::get('/files/{table}/{id}/{type}', 'APIController@get_files')->middleware('auth:api'); // выдать все файлы типа type для table-id
    //     // Route::post('/files/{table}/{id}', 'APIController@store_file')->middleware('auth:api'); // добавить новый файл для table-id
    //     // Route::patch('/files/{table}/{id}/{file_id}', 'APIController@edit_file')->middleware('auth:api'); // изменить существующий файл (table-id для проверки прав доступа к записи)
    //     // Route::delete('/files/{table}/{id}/{file_id}', 'APIController@delete_file')->middleware('auth:api'); // удалить существующий файл (table-id для проверки прав доступа к записи)
    //     // // список файлов
    //     // Route::get('/file_list/{table}', 'APIController@get_file_list')->middleware('auth:api'); // выдать все возможные файлы для добавление в список для table
    //     // Route::post('/file_list/{table}/{id}', 'APIController@sync_file_list')->middleware('auth:api'); // синхронизируем список файлов с переданной датой в запросе
    //     // // группы
    //     //          Route::get('/groups/{table}', 'APIController@get_groups')->middleware('auth:api'); // получить все возможные группы для таблицы table
    //     //          Route::post('/groups/{table}/{id}', 'APIController@add_group')->middleware('auth:api'); // вставить новую группу и присвоить ее id table-id
    //     // // серийные номера
    //     // Route::get('/serials/{table}/{id}', 'APIController@get_serials')->middleware('auth:api'); // получить все серийники для записи таблицы
    //     // Route::put('/serials/{table}/{id}', 'APIController@set_serials')->middleware('auth:api'); // обновить список серийников для записи таблицы
    //     // Route::get('/serials_list/{table}/{id}', 'APIController@get_serials_list')->middleware('auth:api'); // получить все возможные серийники для записи таблицы (для выбора - по сути склад и номенклатура)
    //     // // отчеты
    //     // Route::get('/report/{table}', 'APIController@report')->middleware('auth:api'); // получить данные отчета
    //     // // базовое API
    //     // Route::get('/{table}', 'APIController@index')->middleware('auth:api');;
    //     // Route::get('/{table}/{id}', 'APIController@show')->middleware('auth:api');;
    //     //          Route::post('/{table}', 'APIController@store')->middleware('auth:api');;
    //     //          Route::put('/{table}/{id}', 'APIController@update')->middleware('auth:api');;
    //     // Route::patch('/{table}/{id}/post', 'APIController@post')->middleware('auth:api');;
    //     // Route::patch('/{table}/{id}', 'APIController@update')->middleware('auth:api');;
    //     // Route::delete('/{table}/{id}', 'APIController@destroy')->middleware('auth:api');;
    //     // // формы
    //     // Route::get('/forms/{table}/{id}/{view?}', 'FormController@get')->middleware('auth:api');


    //     // // префикс api
    //     // $api_pref = '/api/v1/';
    //     // // префикс наименования тестируемых данных
    //     // $name_pref = '___abp-';
    //     // // добавляем тестовые номенклатуры
    //     // $nomenklatura_count = 20;
    //     // // количество тестовых групп
    //     // $groups_count = 3;
    //     // списки справочников
    //     $doc_types = DocType::select('id')->get()->pluck('id');
    //     $manufacturers = Manufacturer::select('id')->get()->pluck('id');
    //     $nds = NDS::where('name', 'БезНДС')->orWhere('name', 'НДС20')->get()->pluck('id');
    //     $ed_ism = EdIsm::select('id')->get()->pluck('id');
    //     $faker = new Faker();

    //     // админ
    //     $admin_user = User::where('email', 'garikgsi@yandex.ru')->first();

    //     // // проверка добавления данных в справочники
    //     // for ($i = 0; $i < $this->nomenklatura_count; $i++) {
    //     //     $data = [
    //     //         "name" => self::$name_pref . Str::random(10),
    //     //         "doc_type_id" => $doc_types->random(), //"Вид номенклатуры"
    //     //         "ed_ism_id" => $ed_ism->random(), //"Единица измерения"
    //     //         "description" => Str::random(10), //"Описание"
    //     //         "part_num" => Str::random(10), //"Part №"
    //     //         "manufacturer_id" => $manufacturers->random(), //"Производитель"
    //     //         "artikul" => Str::random(5), //"Артикул"
    //     //         "price" => $faker->randomFloat(2), // "Цена без НДС"
    //     //         "nds_id" => $nds->random(), //"Ставка НДС"
    //     //         "is_usluga" => 0, //"Услуга"
    //     //         "uuid" => 777
    //     //     ];
    //     //     $response = $this->actingAs($admin_user, 'api')->json('POST', $this->api_pref . 'nomenklatura', $data)->assertStatus(200)
    //     //         ->assertJson([
    //     //             "is_error" => false,
    //     //             "count" => 1,
    //     //         ]);
    //     // }

    //     // // вся добавленная номенклатура
    //     // $nomenklatura = Nomenklatura::where("name", 'like', self::$name_pref . '%')->get();
    //     // // массив id всех вставленных номенклатур
    //     // $nomenklatura_ids = $nomenklatura->pluck('id')->all();
    //     // // // проверим кол-во вставленной номенклатуры
    //     // // $this->assertTrue($nomenklatura->count() == $this->nomenklatura_count);

    //     // // ГРУППЫ -------
    //     // // добавляем группу в первую вставленную номенклатуру
    //     // // Route::post('/groups/{table}/{id}', 'APIController@add_group')->middleware('auth:api'); // вставить новую группу и присвоить ее id table-id
    //     // $data = [];
    //     // for ($i = 0; $i < $this->groups_count; $i++) {
    //     //     $data[] = self::$name_pref . Str::random(10);
    //     // }
    //     // $response = $this->actingAs($admin_user, 'api')->json('POST', $this->api_pref . 'groups/nomenklatura/' . $nomenklatura->first()->id, ["data" => $data]);
    //     // // dd($response);
    //     // $response->assertStatus(201);
    //     // // получим все вставленные группы
    //     // $groups = Tag::where("name", 'like', self::$name_pref . '%')->get();
    //     // // массив id всех вставленных групп
    //     // $groups_ids = $groups->pluck('id')->all();
    //     // // // проверим кол-во вставленных групп
    //     // // $this->assertTrue($this->groups_count == $groups->count());
    //     // // добавим первую группу 3 вставленным номенклатурам, начиная со второй, а вторую следующим 4
    //     // $group1 = $groups->take(1)->first();
    //     // $nomenklatura_group1 = $nomenklatura->skip(1)->take(3);
    //     // $data = [];
    //     // foreach ($nomenklatura_group1 as $n) {
    //     //     $data[] = [
    //     //         'tag_id' => $group1->id,
    //     //         'tag' => $group1->name
    //     //     ];
    //     //     $url =  $this->api_pref . 'groups/nomenklatura/' . $n->id;
    //     //     $response = $this->actingAs($admin_user, 'api')->json('POST', $url, ['data' => $data]);
    //     //     // dd($response);
    //     //     $response->assertStatus(201);
    //     // }
    //     // $group2 = $groups->skip(1)->take(1)->first();
    //     // $nomenklatura_group2 = $nomenklatura->skip(4)->take(4);
    //     // $data = [];
    //     // foreach ($nomenklatura_group2 as $n) {
    //     //     $data[] = [
    //     //         'tag_id' => $group2->id,
    //     //         'tag' => $group2->name
    //     //     ];
    //     //     $response = $this->actingAs($admin_user, 'api')->json('POST', $this->api_pref . 'groups/nomenklatura/' . $n->id, ['data' => $data])->assertStatus(201);
    //     // }
    //     // // получим список групп для таблицы
    //     // // Route::get('/groups/{table}', 'APIController@get_groups')->middleware('auth:api'); // получить все возможные группы для таблицы table
    //     // // эталон
    //     // $nomenklatura_tags = TableTag::where('table_type', Nomenklatura::class)->select('tag_id')->distinct()->get()->sortBy('tag')->values()->toArray();
    //     // // проверяем что отдает API
    //     // $response = $this->actingAs($admin_user, 'api')->json('GET', $this->api_pref . 'groups/nomenklatura')->assertStatus(200)
    //     //     ->assertJson(['data' => $nomenklatura_tags]);

    //     // добавляем производителя
    //     // for ($i = 0; $i < $this->manufacturers_count; $i++) {
    //     //     $data = [
    //     //         "name" => self::$name_pref . Str::random(10),
    //     //     ];
    //     //     $url = $this->api_pref . 'manufacturers';
    //     //     $response1 = $this->actingAs($admin_user, 'api')->json('POST', $url, $data);
    //     //     dd($url, $response1);
    //     //     $response1->assertStatus(200)
    //     //         ->assertJson([
    //     //             "is_error" => false,
    //     //             "count" => 1,
    //     //         ]);
    //     // }
    //     // $manufacturers = Manufacturer::where('name', 'like', self::$name_pref . '%')->get();
    //     // dd($manufacturers->toArray());
    //     // $manufacturer1 = $manufacturers->take(1);
    //     // // проверка изменения
    //     // // Route::put('/{table}/{id}', 'APIController@update')->middleware('auth:api');;
    //     // // изменяем производителя у 2 и 3 номенклатуры на первого вставленного производителя
    //     // $nomenklatura_manufacturers = $nomenklatura->skip(1)->take(2);
    //     // foreach ($nomenklatura_manufacturers as $nm) {
    //     //     $nm->manufacturer_id = $manufacturer1->id;
    //     //     $data = $nm->toArray();
    //     //     $response = $this->actingAs($admin_user, 'api')->json('PUT', $this->api_pref . 'nomenklatura/' . $nm->id, $data)->assertStatus(202)
    //     //         ->assertJson([
    //     //             "is_error" => false,
    //     //             "count" => 1,
    //     //         ]);
    //     // }




    //     /*
    //     //  --ПРОВЕРКА ВЫБОРКИ
    //     //         &fields=fieldName1,fieldName2,...,fieldNameN - вывод только перечисленных столбцов таблицы
    //     //         &order=id,[desc|asc] - сортировка выдачи: поле,порядок сортировки
    //     //         &filter=fieldName1[lt|gt|eq|ne|ge|le|like]filterValue1 [or|and] fieldName2[lt|gt|eq|ne|ge|le|like]filterValue1 -
    //     //             доступные операнды:
    //     //                 lt => меньше
    //     //                 gt => больше
    //     //                 eq => равно
    //     //                 ne => не равно
    //     //                 ge => больше или равно
    //     //                 le => меньше или равно
    //     //                 like => like
    //     //                 in => входит в массив (IN)
    //     //                 ni => не входит в массив (NOT IN)
    //     //                 morphin => принадлежит массиву и соответствует полиморфной связи
    //     //                 morphni => принадлежит массиву и соответствует полиморфной связи
    //     //                 morph => равняется id и соответствует полиморфной связи
    //     //             !! к операнду like значение обрамляется %% с обеих сторон
    //     //             доступные условия:
    //     //                 or => ИЛИ
    //     //                 and => И
    //     //             !!невозможно указывать условия, обрамленные в скобки
    //     //             !! для полиморфных условий необходимо соблюсти синтаксис filterValue: ["App\\Kontragent"].[734,755,743,327] - второй параметр
    //     //                 точечной нотации должен содержать массив в любом случае, даже если указан операнд morph и/или единственное значение. Пример:
    //     //                 /contracts?filter=contractable morphin ["App\\Kontragent"].[734]
    //     //          ----
    //     //      в качестве fieldName можно указывать связи таблиц,разделенные точками, например, acts?filter=order_.contract_.contract_type_id in [2,5,7]
    //     //      в примере из модели соответствующей таблице acts будет выбрана связь order_, далее из модели Order выбирается связь contract_,
    //     //      в котором уже ищется поле contract_type_id, которое и фильтруется в соответствии с операндами.
    //     //      если необходимо отфильтровать по группам - в качестве последней связи необходимо указывать значение groups, например, следующий пример
    //     //      выберет из актов только те, позиции которых содержат заданные группы номенклатур: acts?filter=items.nomenklatura_.groups in [36,2]
    //     //      здесь сначала вызываеся метод items, получающий позиции накладной, потом применяется связь nomenklatura_ из модели ActItem, затем
    //     //      номенклатура фильтруется по группам. !Группы применяются к последнему параметру, перед ключевым параметром 'groups'!
    //     //      Для фильтрации полиморфных полей необходимо использовать в качестве последнего параметра точечной нотации значение morph-поля, например
    //     //      acts?filter=order_.contract_.contractable morphin ["App\\Kontragent"].[734,755] В результате примера получаем все накладные, которые указаны
    //     //      в качестве contractable-поля модели Contract, как контрагенты с id == [734,755]
    //     //          ----
    //     //         &search=text - поиск по всем возможным полям
    //     //         &tags=id1,id2,...,idN - дополнительный фильтр по тегам (выбор должен содержать строки имеющий хотя бы 1 тег)
    //     //         &extensions=ext1,ext2,...,extN - добавить в ответ расширения для записи из возможных [files,images,groups,file_list,main_image,select_list_title]
    //     //         &scope=stock_balance.9, - добавить в запрос scope. Параметры передаются через точки, скопы разделяются запятыми
    //     //         &offset - смещение относительно 0-го элемента выдачи, отсортированного согласно правилам сортировки (только совместно с limit)
    //     //         &limit - количество выдаваемых значений выдачи (-1 для отсутствия лимитов)
    //     //         &trashed=1 - выдавать помеченные на удаление записи
    //     */

    //     $checks = [
    //         // фильтрация по группам
    //         ["url" => 'nomenklatura?filter=groups in ' . json_encode($groups_ids), "prot" => 'GET', "assert" => ['count' => 8], "status" => 200],
    //         // только номенклатуры 2-й группы
    //         ["url" => 'nomenklatura?filter=groups eq ' . $group2->id, "prot" => 'GET', "assert" => ['count' => 5], "status" => 200],
    //         // только вставленные номенклатуры без групп
    //         ["url" => 'nomenklatura?filter=id in ' . json_encode($nomenklatura_ids) . ' and groups ne ' . json_encode($groups_ids), "prot" => 'GET', "assert" => ['count' => $this->nomenklatura_count - 8], "status" => 200],
    //     ];

    //     foreach ($checks as $check) {
    //         try {
    //             //code...
    //             $url = $this->api_pref . $check['url'];
    //             $response = $this->actingAs($admin_user, 'api')->json($check['prot'], $url);
    //             if (isset($check["status"])) $response->assertStatus($check["status"]);
    //             if (isset($check["assert"])) $response->assertJson($check["assert"]);
    //         } catch (\Throwable $th) {
    //             echo ("Fail with url " . $url);
    //         }
    //     }


    //     // // очистка
    //     // // dd($groups_ids);
    //     // TableTag::whereIn('tag_id', $groups_ids)->forceDelete();

    //     // $tables = [Nomenklatura::class, Tag::class];
    //     // foreach ($tables as $table) {
    //     //     $table::where("name", 'like', $name_pref . '%')->forceDelete();
    //     // }



    //     // // группа
    //     // $group->forceDelete();
    //     // // номенклатура
    //     // $nomenklatura->forceDelete();



    //     // // получение списка номенклатур
    //     // $response = $this->actingAs($admin_user, 'api')->get('/api/v1/nomenklatura');
    //     // // ответ =200
    //     // $response->assertStatus(200);

    //     // $user = factory(\App\User::class)->make();
    //     // dd($user->toArray());

    //     // $sklad = factory(\App\Sklad::class)->make();
    //     // dd($sklad->toArray());

    //     // генерим 10 номенклатур
    //     // $nomenklatura = factory(\App\Nomenklatura::class)->make([
    //     //     'name' => 'test',
    //     //     'deleted_by' => null
    //     // ]);
    //     // dd($nomenklatura->toArray());
    //     // $ei = factory(EdIsm::class)->make();
    //     // dd($ei->toArray());
    // }
}