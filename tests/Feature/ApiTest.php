<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Faker\Generator as Faker;

use Tests\TestCase;
use App\Nomenklatura;
use App\User;
use App\UserInfo;
use App\Sotrudnik;
use App\EdIsm;
use App\Tag;
use App\DocType;
use App\Manufacturer;
use App\NDS;
use App\Sklad;
use App\SkladReceive;
use App\SkladReceiveItem;
use App\SkladMove;
use App\SkladMoveItem;
use App\Production;
use App\ProductionItem;
use App\ProductionComponent;
use App\ProductionReplace;
use App\Firm;
use App\Kontragent;
use Illuminate\Support\Str;
use App\TableTag;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApiTest extends TestCase
{

    private $api_pref = '/api/v1/';
    // префикс наименования тестируемых данных
    private static $name_pref = '___abp-';
    private static $comment = '___abp-';
    // добавляем тестовые номенклатуры
    private $nomenklatura_count = 20;
    // добавляем тестовые производители (дб больше 4)
    private $manufacturers_count = 5;
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
    // кол-во записей на страницу по умолчанию
    private $default_api_limit = 10;
    // кол-во складов/кладовщиков
    private $keepers_count = 3;
    // кол-во приходных накладных
    private $receive_count = 5;

    // setUp
    protected function setUp(): void
    {
        parent::setUp();

        // $user_id = 1084;
        // $sklad_id = 1757;
        // $user = User::find($user_id);
        // $user_info = $user ? $user->info : null;
        // // сотрудник
        // $sotrudnik = $user_info ? $user_info->sotrudnik() : null;

        // пользователь == кладовщик
        // $is_keeper = $sotrudnik && $sotrudnik->is_keeper($sklad_id);
        // dd($user->toArray(), $user_info->toArray(), $sotrudnik->toArray(),  $is_keeper);

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

        // удалим стандартные таблицы
        $tables = [
            Nomenklatura::class,
            Manufacturer::class, UserInfo::class,
            SkladReceive::class, SkladReceiveItem::class, Sklad::class,
            Sotrudnik::class, SkladMove::class, SkladMoveItem::class
        ];
        foreach ($tables as $table) {
            $table::where("comment", self::$comment)->forceDelete();
        }

        // удалим кладовщиков
        try {
            DB::connection('db1')->table('users')->where("name", 'like', self::$name_pref . '%')->delete();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    // после всех тестов
    public static function tearDownAfterClass(): void
    {
        // parent::tearDownAfterClass();
        self::cleanDb();
    }

    // тест добавления номенклатуры
    //          Route::post('/groups/{table}/{id}', 'APIController@add_group')->middleware('auth:api'); // вставить новую группу и присвоить ее id table-id
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
    //          Route::post('/groups/{table}/{id}', 'APIController@add_group')->middleware('auth:api'); // вставить новую группу и присвоить ее id table-id
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
    //          Route::put('/{table}/{id}', 'APIController@update')->middleware('auth:api');;
    //          Route::patch('/{table}/{id}', 'APIController@update')->middleware('auth:api');;
    public function testUpdateNomenklatura()
    {
        $nomenklatura = Nomenklatura::where('comment', self::$comment)->orderBy('id')->get();
        // изменим производителя у номенклатуры
        $manufacturers = Manufacturer::where('comment', self::$comment)->orderBy('id')->get();
        // dd($manufacturers->toArray());
        $manufacturer1 = $manufacturers->take(1)->first();
        // var_dump($manufacturer1->toArray());
        // проверка изменения
        // изменяем производителя у 2 и 3 номенклатуры на первого вставленного производителя
        $nomenklatura_manufacturers = $nomenklatura->skip(1)->take(2);
        $i = 0;
        foreach ($nomenklatura_manufacturers as $nm) {
            $nm->manufacturer_id = $manufacturer1->id;
            if ($i % 2 == 0) {
                $data = $nm->toArray();
                $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $this->api_pref . 'nomenklatura/' . $nm->id, $data)->assertStatus(202)
                    ->assertJson([
                        "is_error" => false,
                        "count" => 1,
                        'data' => [
                            'updated_by' => $this->admin_user->id
                        ]
                    ]);
            } else {
                $data = ["manufacturer_id" => $manufacturer1->id];
                $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $this->api_pref . 'nomenklatura/' . $nm->id, $data)->assertStatus(202)
                    ->assertJson([
                        "is_error" => false,
                        "count" => 1,
                        'data' => [
                            'updated_by' => $this->admin_user->id
                        ]
                    ]);
            }

            // значения в БД должны совпадать
            $updated_nomenklatura = Nomenklatura::where('comment', self::$comment)->where('manufacturer_id', $manufacturer1->id)->where('id', $nm->id);
            $this->assertTrue($updated_nomenklatura->count() == 1);
            $i++;
        }
    }

    // тест удаления
    //      Route::delete('/{table}/{id}', 'APIController@destroy')->middleware('auth:api');;
    public function testDeleteManufacturer()
    {
        $delete_count = 2;
        $manufacturers_before = Manufacturer::where('comment', self::$comment)->get();
        $manufacturers = Manufacturer::where('comment', self::$comment)->orderBy('id', 'DESC')->get();
        $delete_manufacturers = $manufacturers->take($delete_count);
        $deleted_ids = [];
        foreach ($delete_manufacturers as $m) {
            $deleted_ids[] = $m->id;
            $response = $this->actingAs($this->admin_user, 'api')->json('delete', $this->api_pref . 'manufacturers/' . $m->id)->assertStatus(200)
                ->assertJson([
                    "is_error" => false,
                    "count" => 1,
                ]);
        }
        // проверим содержимое БД
        $manufacturers_after = Manufacturer::where('comment', self::$comment)->get();
        $this->assertSame($manufacturers_before->count() - $manufacturers_after->count(), $delete_count);
        // проверки удаления
        $this->assertSame(Manufacturer::whereIn('id', $deleted_ids)->withTrashed()->count(), $delete_count);
        // все удаляемые записи не должны выдаваться моделью
        $this->assertSame(Manufacturer::whereIn('id', $deleted_ids)->count(), 0);
        // проверка что пользователь удаливший запись установлен
        $this->assertSame(Manufacturer::whereIn('id', $deleted_ids)->withTrashed()->where('deleted_by', $this->admin_user->id)->count(), 2, 'user wan not setted when delete row');
    }

    // тест чтения групп для таблицы
    //      Route::get('/groups/{table}', 'APIController@get_groups')->middleware('auth:api'); // получить все возможные группы для таблицы table
    public function testReadTableGroups()
    {

        $nomenklatura_tags = TableTag::where('table_type', Nomenklatura::class)->select('tag_id')->distinct()->get()->sortBy('tag')->values()->toArray();
        $response = $this->actingAs($this->admin_user, 'api')->json('GET', $this->api_pref . 'groups/nomenklatura')->assertStatus(200)
            ->assertJson([
                "is_error" => false,
                "count" => count($nomenklatura_tags),
                'data' => $nomenklatura_tags
            ]);
    }

    // тест чтения единственной записи
    //      Route::get('/{table}/{id}', 'APIController@show')->middleware('auth:api');;
    public function testRead1Row()
    {
        $nomenklatura = Nomenklatura::where('comment', self::$comment)->orderBy('id')->take(1)->first();
        $response = $this->actingAs($this->admin_user, 'api')->json('GET', $this->api_pref . 'nomenklatura/' . $nomenklatura->id)->assertStatus(200)
            ->assertJson([
                "is_error" => false,
                "count" => 1,
                'data' => $nomenklatura->toArray()
            ]);
    }

    // тест поиска, сортировки, лимита, смещения
    //      Route::get('/{table}?fields=name,id&order=name,desc&search={test}&limit={limit}&offset={offset}', 'APIController@show')->middleware('auth:api');;
    public function testSearch()
    {
        $limit = 15;
        $sort = ['name', 'desc'];
        $offset = 2;

        $url = $this->api_pref . "nomenklatura?order=$sort[0],$sort[1]&search=" . self::$name_pref . "&limit=$limit&offset=$offset";

        $data = Nomenklatura::where('name', 'like', self::$name_pref . '%')->orderBy($sort[0], $sort[1]);
        $data_count = $data->count();
        $data = $data->skip($offset)->take($limit)->get()->pluck('id')->toArray();

        $response = $this->actingAs($this->admin_user, 'api')->json('GET', $url)->assertStatus(200)
            ->assertJson([
                "is_error" => false,
                "count" => $data_count,
            ]);

        $response_data = $this->response_data($response)->pluck('id')->toArray();
        $this->assertSame($data, $response_data);
    }

    // тест получения различных форматов данных
    //      Route::get('/{table}?odata={odata}', 'APIController@show')->middleware('auth:api');;
    public function testGetFormats()
    {
        $formats = [NULL, 'full', 'data', 'model', 'count', 'list'];
        foreach ($formats as $odata) {
            $url = $this->api_pref . "nomenklatura?order=name" . (is_null($odata) ?: "&odata=$odata");
            if (is_null($odata)) $odata = 'data';
            $response = $this->actingAs($this->admin_user, 'api')->json('GET', $url)->assertStatus(200);

            $data = Nomenklatura::where('id', '>', 1)->orderBy('name');


            $response->assertJson([
                "is_error" => false,
                "count" => $data->count()
            ]);
            // если проверяем модель
            if (in_array($odata, ['full', 'model'])) {
                $nomenklatura_model = new Nomenklatura;
                $model = $nomenklatura_model->model();
                $extensions = $nomenklatura_model->get_extensions();
                $response_model = json_decode($response->getContent(), true);
                $response_model = json_decode($response->getContent(), true)['model'];
                $this->assertSame($model, $response_model['fields']);
                $this->assertSame($extensions, $response_model['extensions']);
                // $this->assertSame($url, array_keys($response_model));
            }
            // если проверяем данные
            if (in_array($odata, ['full', 'data'])) {
                $data = $data->take($this->default_api_limit)->get()->pluck('id')->toArray();
                $response_data = $this->response_data($response)->pluck('id')->toArray();
                $this->assertSame($data, $response_data);
            }
            // если проверяем список
            if ($odata == 'list') {
                $data = $data->take($this->default_api_limit)->get()->pluck('select_list_title')->toArray();
                $response_data = $this->response_data($response)->pluck('select_list_title')->toArray();
                $this->assertSame($data, $response_data);
            }
        }
    }

    // проверка выдачи удаленных записей
    //      Route::get('/{table}?trashed=1', 'APIController@show')->middleware('auth:api');;
    public function testGetTrashed()
    {
        $trashed = [NULL, true, false, 1, 0];
        foreach ($trashed as $withTrashed) {
            $url = $this->api_pref . "manufacturers?order=name&search=" . self::$name_pref . "&limit=$this->manufacturers_count" . (is_null($withTrashed) ? '' : "&trashed=$withTrashed");
            $manufacturers = Manufacturer::where('name', 'like', self::$name_pref . '%')->orderBy('name');
            if ($withTrashed === true || $withTrashed === 1) $manufacturers = $manufacturers->withTrashed();
            $manufacturers = $manufacturers->take($this->manufacturers_count)->get();
            try {
                $response = $this->actingAs($this->admin_user, 'api')->json('GET', $url)->assertStatus(200);
            } catch (\Throwable $th) {
                dd($url);
            }
            $response->assertJson([
                "is_error" => false,
                "count" => $manufacturers->count()
            ]);

            $data = $manufacturers->pluck('id')->toArray();
            $response_data = $this->response_data($response)->pluck('id')->toArray();
            $this->assertSame($data, $response_data);
        }
    }

    // проверка сортировки по группам
    //      Route::get('/{table}?tags=tag1,tag2', 'APIController@show')->middleware('auth:api');;
    public function testGetGroups()
    {
        // группы которые назначены номенклатурам
        $groups = Tag::where("name", 'like', self::$name_pref . '%')->take(2)->get();
        $groups_ids = $groups->pluck('id')->toArray();

        $nomenklatura = Nomenklatura::where('comment', self::$comment)->orderBy('name')->whereHas('groups', function ($query) use ($groups_ids) {
            $query->whereIn('tag_id', $groups_ids);
        })->take($this->default_api_limit)->get()->pluck('id')->toArray();

        $url = $this->api_pref . "nomenklatura?order=name&tags=" . implode(",", $groups_ids);
        $response = $this->actingAs($this->admin_user, 'api')->json('GET', $url)->assertStatus(200);

        $response_data = $this->response_data($response)->pluck('id')->toArray();
        $this->assertSame($nomenklatura, $response_data);
    }

    // создаем сотрудников
    public function testCreateEmployees()
    {
        for ($i = 0; $i < $this->keepers_count; $i++) {
            // создаем сотрудника
            $employ_data = [
                "name" => self::$name_pref . Str::random(10),
                "comment" => self::$comment,
                'sure_name' => Str::random(10),
                'first_name' => Str::random(10),
                'patronymic' => Str::random(10),
            ];
            $employ_url = $this->api_pref . 'sotrudniks';
            $employ_response = $this->actingAs($this->admin_user, 'api')->json('POST', $employ_url, $employ_data);
            $employ_response->assertStatus(200)
                ->assertJson([
                    "is_error" => false,
                    "count" => 1,
                    'data' => $employ_data
                ]);
        }
    }

    // привязываем сотрудников к пользователям
    public function testUpdateUserInfo()
    {
        $keepers_count = $this->keepers_count;
        $keepers_before = User::where('name', 'like', self::$name_pref . '%')->get();
        if ($keepers_before->count() < $keepers_count) {
            // создаем 3 пользователя
            for ($i = 0; $i < $keepers_count; $i++) {
                factory(User::class)->create([
                    'name' => self::$name_pref . Str::random(10)
                ]);
            }
        }
        $keepers = User::where('name', 'like', self::$name_pref . '%')->get();
        $this->assertTrue($keepers->count() >= $keepers_count);
        $keepers = User::where('name', 'like', self::$name_pref . '%')->take($this->keepers_count)->get();
        $emloyers = Sotrudnik::where('comment', self::$comment)->get();
        $i = 0;
        foreach ($keepers as $keeper) {
            // получим сотрудника
            $employer = $emloyers->skip($i)->take(1)->first();

            // обновим user_info
            $user_info_data = [
                "name" => self::$name_pref . Str::random(10),
                "comment" => self::$comment,
                'userable_type' => Sotrudnik::class,
                'userable_id' => $employer->id,
                'user_id' => $keeper->id,
            ];
            $user_info_url = $this->api_pref . 'user_info';
            $user_info_response = $this->actingAs($this->admin_user, 'api')->json('POST', $user_info_url, $user_info_data);
            $user_info_response->assertStatus(200)
                ->assertJson([
                    "is_error" => false,
                    "count" => 1,
                    'data' => $user_info_data
                ]);
            $i++;
        }
    }

    // тест создания складов, пользователей-кладовщиков
    public function testCreateSkladInfrastructure()
    {
        $emloyers = Sotrudnik::where('comment', self::$comment)->get();
        foreach ($emloyers as $employer) {
            // создаем склад с кладовщиком на борту
            $data = [
                "name" => self::$name_pref . Str::random(10),
                "comment" => self::$comment,
                'keeper_id' => $employer->id
            ];

            $url = $this->api_pref . 'sklads';
            $response = $this->actingAs($this->admin_user, 'api')->json('POST', $url, $data);
            $response->assertStatus(200)
                ->assertJson([
                    "is_error" => false,
                    "count" => 1,
                    'data' => $data
                ]);
            // проверим, что сотрудник стал кладовщиком
            $response_data = $this->response_data($response)->toArray();

            $this->assertTrue($employer->is_keeper($response_data['id']));
        }
    }


    // проверка добавления документов
    public function testCreateReceiveDocuments()
    {
        // вся номенклатура
        $nomenklatura = Nomenklatura::where('comment', self::$comment)->get();
        // все склады
        $sklads = Sklad::where('comment', self::$comment)->get();
        // все фирмы
        $firms = Firm::where('id', '>', 1)->get();
        // все ставки НДС
        $nds = NDS::where('id', '>', 1)->get();
        // все контрагенты
        $kontragents = Kontragent::where('id', '>', 1)->get();
        // создаем приходы
        for ($i = 0; $i < $this->receive_count; $i++) {
            // генерим склад
            $sklad = $sklads->random();
            // данные накладной
            $data = [
                'comment' => self::$comment,
                'sklad_id' => $sklad->id,
                'kontragent_id' => $kontragents->random()->id,
                'firm_id' => $firms->random()->id,
                'in_doc_date' => now()->format('Y-m-d'),
                'in_doc_num' => Str::random(3),
                'doc_date' => Carbon::today()->subDays(rand(0, 365))->format('Y-m-d'),
                'items' => []
            ];
            // позиции по накладной
            $nomenklatura_items = [];
            $items_data = [];
            $sum_receive = 0;
            $npp = 0;
            // пока не наткнемся на уже вставленную номенклатуру
            do {
                // генерим строку накладной
                $nom = $nomenklatura->random()->id;
                $kolvo = $this->faker->randomFloat(0, 1, 200);
                $price = $this->faker->randomFloat(2, 10, 1000000);
                $summa = $kolvo * $price;

                if (in_array($nom, $nomenklatura_items)) {
                    break;
                } else {
                    $nomenklatura_items[] = $nom;
                    $sum_receive += $summa;
                    $items_data[] = [
                        'nomenklatura_id' => $nom,
                        'kolvo' => $kolvo,
                        'price' => $price,
                        'summa' => $summa,
                        'nds_id' => $nds->random()->id,
                        'comment' => self::$comment,
                    ];
                }
                $npp++;
            } while (true);
            // добавляем позииции
            $data['items'] = $items_data;
            // вставляем запись
            $url = $this->api_pref . 'sklad_receives';
            $response = $this->actingAs($this->admin_user, 'api')->json('POST', $url, $data);
            $response->assertStatus(200);
            $response->assertJson([
                "is_error" => false,
                "count" => 1
            ]);
            // данные проверим отдельно
            $response_data = $this->response_data($response)->toArray();
            // после создания сумма накладной должна автоматически рассчитаться
            $this->assertSame(round($sum_receive, 2), round($response_data['summa'], 2), "summa php=$sum_receive");
            // должен присвоится номер документа
            $this->assertTrue(!is_null($response_data['doc_num']));
            // грузоотправитель - равен контрагенту
            $this->assertSame($response_data['kontragent_id'], $response_data['kontragent_otpravitel_id']);
            // грузополучатель - равен фирме
            $this->assertSame($response_data['firm_id'], $response_data['firm_poluchatel_id']);
            // проверяем позиции накладной
            $items = $response_data['items'];
            $this->assertTrue(count($items) == $npp);
            foreach ($items as $item) {
                // наименование должно быть заполнено
                $nomenklatura_item = Nomenklatura::find($item->nomenklatura_id);
                $this->assertSame($nomenklatura_item->doc_title, $item->nomenklatura_name, "nomenklatura_title must be set to [$nomenklatura_item->doc_title], but set to [$item->nomenklatura_name]. check trigger SkladReceiveItemObserver!");
                // должна быть рассчитана сумма НДС
                $stavka_nds = $nds->find($item->nds_id)->stavka;
                $this->assertSame($stavka_nds, $item->stavka_nds, "stavka_nds must be set to [$stavka_nds], but set to [$item->stavka_nds]. check trigger SkladReceiveItemObserver!");
                $this->assertSame(round($item->summa * $stavka_nds, 2), round($item->summa_nds, 2));
            }
        }
    }

    // проверка нулевых остатков по всем складам - до проведения документов
    //     Route::get('/{table}?scope=stock_balance.9, - добавить в запрос scope. Параметры передаются через точки, скопы разделяются запятыми
    public function testCheckNullRemains()
    {
        // все склады
        $sklads = Sklad::where('comment', self::$comment)->get();
        foreach ($sklads as $sklad) {
            $url = $this->api_pref . "nomenklatura?scope=stock_balance.$sklad->id";

            $response = $this->actingAs($this->admin_user, 'api')->json('GET', $url)->assertStatus(200)
                ->assertJson([
                    "is_error" => false,
                    "count" => 0,
                    'data' => []
                ]);
        }
    }

    // проверяем проведение документа пользователем не обладающим правами
    public function testCheckPermissionsToSetActiveDocument()
    {
        // поступления
        $receive1 = SkladReceive::where('comment', self::$comment)->first();
        // склад
        $sklad = Sklad::find($receive1->sklad_id);
        // складарь
        $keeper = Sotrudnik::find($sklad->keeper_id)->user();
        // не складарь и не админ
        $user = User::where('name', 'like', self::$name_pref . '%')->whereNotIn('id', [$this->admin_user->id, $keeper->id])->take(1)->first();
        // url
        $url = $this->api_pref . "sklad_receives/$receive1->id/post";
        // данные
        $data = ["is_active" => 1];
        $response = $this->actingAs($user, 'api')->json('PATCH', $url, $data)->assertStatus(421)
            ->assertJson([
                "is_error" => true,
            ]);
        $this->assertTrue($this->response_has_error($response, ['Приходовать накладные может только кладовщик или администратор']));
    }

    // проводим от имени кладовщика
    public function testKeeperSetActiveDocuments()
    {
        // поступления
        $receive1 = SkladReceive::where('comment', self::$comment)->first();
        // склад
        $sklad = Sklad::find($receive1->sklad_id);
        // складарь
        $keeper = Sotrudnik::find($sklad->keeper_id)->user();
        // url
        $url = $this->api_pref . "sklad_receives/$receive1->id/post";
        // данные
        $data = ["is_active" => 1];
        $response = $this->actingAs($keeper, 'api')->json('PATCH', $url, $data)->assertStatus(202)
            ->assertJson([
                "is_error" => false,
                "count" => 1,
                'data' => $data
            ]);
    }

    // проводим документы от имени администратора
    // PATCH /api/v1/{table_name}/{N}/post - проводим документ с id=N в таблице table_name. В запросе необходимо передать массив полей для проведения (в моделе должны быть отмечены признаком "post"=>true). В ответе count сервер вернет измененную запись
    public function testSetActiveDocuments()
    {
        $remains = collect([]);
        // поступления
        $receives = SkladReceive::where('comment', self::$comment)->where('is_active', '<>', 1)->get();
        // для каждого поступления
        foreach ($receives as $receive) {
            // url
            $url = $this->api_pref . "sklad_receives/$receive->id/post";
            // данные
            $data = ["is_active" => 1];
            $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $url, $data)->assertStatus(202)
                ->assertJson([
                    "is_error" => false,
                    "count" => 1,
                    'data' => $data
                ]);
        }
    }

    // проверяем остатки после прихода (дата не важна)
    public function testCheckRemainsAfterReceive()
    {
        // получим остатки расчетной модели
        $ostatki_by_sklad = $this->calcRemains();

        // получаем остатки
        foreach ($ostatki_by_sklad as $sklad_id => $ostatki_nomenklatur) {
            $url = $this->api_pref . "nomenklatura?limit=-1&scope=stock_balance.$sklad_id";

            $response = $this->actingAs($this->admin_user, 'api')->json('GET', $url)->assertStatus(200);
            // данные остатков
            $response_data = $this->response_data($response);
            $response_remains = $response_data->sortBy('id')->mapWithKeys(function ($response_data) {
                return [$response_data->id => round($response_data->stock_balance)];
            })->all();
            // сверяем массивы
            $this->assertSame($ostatki_nomenklatur, $response_remains, "remains os sklad $sklad_id & url=$url not matched to data=" . $response_data->count());
        }
    }

    // создадим перемещение по складам
    public function testCreateSkladMove()
    {
        // текущие остатки
        $ostatki_by_sklad = $this->calcRemains();

        // получаем остатки
        foreach ($ostatki_by_sklad as $sklad_out => $ostatki_nomenklatur) {
            // все фирмы
            $firms = Firm::where('id', '>', 1)->get();
            // все склады
            $sklads = Sklad::where('comment', self::$comment)->where('id', '<>', $sklad_out)->get();
            // генерим склад получения
            $sklad_in = $sklads->random();
            // все сотрудники
            $emloyers = Sotrudnik::where('comment', self::$comment)->get();
            // данные накладной
            $data = [
                'comment' => self::$comment,
                'sklad_out_id' => $sklad_out,
                'sklad_in_id' => $sklad_out,
                'firm_id' => $firms->random()->id,
                'doc_date' => Carbon::today()->subDays(rand(0, 365))->format('Y-m-d'),
                'transitable_type' => Sotrudnik::class,
                'transitable_id' => $emloyers->random()->id,
                'items' => []
            ];

            // перенесем только номенклатуры только с четными id и самую первую
            // для самой первой укажем кол-во на 21 больше, чем есть в остатке
            $N = $this->faker->randomFloat(0, 1, 100);
            foreach ($ostatki_nomenklatur as $nomenklatura_id => $ostatok) {
                $is_first = count($data['items']) == 0;
                if ($is_first || $nomenklatura_id % 2 == 0) {
                    $kolvo = $is_first ? $ostatok + $N : $this->faker->randomFloat(0, 1, $ostatok);
                    $data['items'][] = [
                        'nomenklatura_id' => $nomenklatura_id,
                        'kolvo' => $kolvo,
                        'comment' => self::$comment,
                        'name' => $is_first ? $N : NULL // кол-во превышенного остатка
                    ];
                }
            }

            // вставляем запись
            $url = $this->api_pref . 'sklad_moves';
            $response = $this->actingAs($this->admin_user, 'api')->json('POST', $url, $data);
            $response->assertStatus(421)
                ->assertJson([
                    "is_error" => true,
                ]);
            $this->assertTrue($this->response_has_error($response, ['Склад отправления равен складу назначения']), "Error from response: $this->get_response_error");

            // меняем склад на нужный и еще раз выполняем запрос
            $data['sklad_in_id'] = $sklad_in->id;
            $response = $this->actingAs($this->admin_user, 'api')->json('POST', $url, $data);
            try {
                $response->assertStatus(200);
            } catch (\Throwable $th) {
                dd($url, $data, $response);
            }
            $response->assertJson([
                "is_error" => false,
                "count" => 1,
            ]);
        }
    }

    // проверка проведения перемещения ни кладовщиком ни админом
    public function testPermissionsSetActiveSkladMove()
    {
        // выбираем первое непроведенное перемещение
        $move = SkladMove::where('comment', self::$comment)->where('is_active', 0)->first();
        // склады
        $sklad_in = Sklad::find($move->sklad_in_id);
        $sklad_out = Sklad::find($move->sklad_out_id);
        // получим складарей складов отправителя и получателя
        $keeper_sklad_in = Sotrudnik::find($sklad_in->keeper_id)->user();
        $keeper_sklad_out = Sotrudnik::find($sklad_out->keeper_id)->user();
        // пользователь - не складарь и не админ
        $user = User::where('name', 'like', self::$name_pref . '%')->whereNotIn('id', [$this->admin_user->id, $keeper_sklad_in->id, $keeper_sklad_out->id])->take(1)->first();
        // url
        $url = $this->api_pref . "sklad_moves/$move->id/post";
        // данные
        $data = ["is_out" => 1];
        $response = $this->actingAs($user, 'api')->json('PATCH', $url, $data)->assertStatus(421)
            ->assertJson([
                "is_error" => true,
            ]);
        $this->assertTrue($this->response_has_error($response, ['Отправлять(проводить) со склада отправления может только кладовщик склада отправления или администратор']));
    }

    // проверка полного проведения перемещения ни кладовщиком отправления
    public function testPermissionsFullSetActiveSkladMoveByKeeper()
    {
        // выбираем первое непроведенное перемещение
        $move = SkladMove::where('comment', self::$comment)->where('is_active', 0)->first();
        // склады
        $sklad_out = Sklad::find($move->sklad_out_id);
        // получим складарей складов отправителя и получателя
        $keeper_sklad_out = Sotrudnik::find($sklad_out->keeper_id)->user();
        // url
        $url = $this->api_pref . "sklad_moves/$move->id/post";
        // данные
        $data = ["is_active" => 1];
        $response = $this->actingAs($keeper_sklad_out, 'api')->json('PATCH', $url, $data)->assertStatus(421)
            ->assertJson([
                "is_error" => true,
            ]);
        $this->assertTrue($this->response_has_error($response, ['Проводить и распроводить перемещение целиком может только администратор']));
    }

    // попытка оприходования на склад получения без проведения со склада отправления
    public function testPermissionsSetInWithoutOut()
    {
        // выбираем первое непроведенное перемещение
        $move = SkladMove::where('comment', self::$comment)->where('is_active', 0)->first();
        // склады
        $sklad_in = Sklad::find($move->sklad_in_id);
        // получим складарей складов отправителя и получателя
        $keeper_sklad_in = Sotrudnik::find($sklad_in->keeper_id)->user();
        // url
        $url = $this->api_pref . "sklad_moves/$move->id/post";
        // данные
        $data = ["is_in" => 1];
        $response = $this->actingAs($keeper_sklad_in, 'api')->json('PATCH', $url, $data)->assertStatus(421)
            ->assertJson([
                "is_error" => true,
            ]);
        $this->assertTrue($this->response_has_error($response, ['Принимать(проводить) на склад получения можно только после отправки(проведения) со склада отправления']));
    }

    // TODO SkladMove!!!
    // Принимать(проводить) на склад получения может только кладовщик склада получения или администратор


    // вычисляем остатки на установленную дату согласно внутренним регистрам (расчетная модель)
    private function calcRemains($ltDate = null)
    {
        // // если не указана дата - считаем по состоянию на сегодня
        // $date = $ltDate ? Carbon::createFromFormat('Y-m-d', $ltDate) : now();

        // регистр остатков
        $remains = collect([]);

        // заполняем регистр
        // все проведенные поступления
        $receives = SkladReceive::where('comment', self::$comment)->where('is_active', '=', 1)->get();
        foreach ($receives as $receive) {
            // добавим остатки в массив-регистр
            foreach ($receive->items as $item) {
                $remains->push([
                    'document_type' => SkladReceive::class,
                    'document_id' => $receive->id,
                    'document_date' => $receive->doc_date,
                    'sklad_id' => $receive->sklad_id,
                    'saldo' => 1,
                    'nomenklatura_id' => $item->nomenklatura_id,
                    'kolvo' => $item->kolvo,
                    'price' => $item->price,
                    'summa' => $item->summa
                ]);
            }
        }
        // все отправленные перемещения
        $moves_out = SkladMove::where('comment', self::$comment)->where('is_out', '=', 1)->get();
        foreach ($moves_out as $move_out) {
            // списываем
            foreach ($move_out->items as $item) {
                $remains->push([
                    'document_type' => SkladMove::class,
                    'document_id' => $move_out->id,
                    'document_date' => $move_out->doc_date,
                    'sklad_id' => $move_out->sklad_out_id,
                    'saldo' => 0,
                    'nomenklatura_id' => $item->nomenklatura_id,
                    'kolvo' => -$item->kolvo,
                    'price' => $item->price,
                    'summa' => -$item->summa
                ]);
            }
        }
        // все полученные перемещения
        $moves_out = SkladMove::where('comment', self::$comment)->where('is_in', '=', 1)->get();
        foreach ($moves_out as $move_out) {
            // приходуем
            foreach ($move_out->items as $item) {
                $remains->push([
                    'document_type' => SkladMove::class,
                    'document_id' => $move_out->id,
                    'document_date' => $move_out->doc_date,
                    'sklad_id' => $move_out->sklad_out_id,
                    'saldo' => 1,
                    'nomenklatura_id' => $item->nomenklatura_id,
                    'kolvo' => $item->kolvo,
                    'price' => $item->price,
                    'summa' => $item->summa
                ]);
            }
        }

        // проверяем остатки
        $nomenklatura_by_sklad = $remains
            // ->filter(function ($item) use ($date) {
            //     return Carbon::createFromFormat('Y-m-d', $item['document_date'])->lte($date);
            // })
            ->groupBy(['sklad_id', function ($item) {
                return $item['nomenklatura_id'];
            }]);
        $ostatki_by_sklad = $nomenklatura_by_sklad->map(function ($ostatki_by_sklad) {
            return $ostatki_by_sklad->map(function ($ostatki_by_nomenklatura) {
                return $ostatki_by_nomenklatura->sum('kolvo');
            })->sortKeys();
        })->toArray();
        return $ostatki_by_sklad;
    }

    // функция получения данных запроса
    // return collection of data
    private function response_data($response)
    {
        return collect(json_decode($response->getContent())->data);
    }

    // функция проверяет наличие строк в ошибке запроса
    private function response_has_error($response, $text_array)
    {
        $err = $this->get_response_error($response);
        foreach ($text_array as $needle) {
            if (mb_strpos($err, $needle) === false) return false;
        }
        return true;
    }

    private function get_response_error($response)
    {
        return json_decode($response->getContent())->error[0];
    }


    // TODO
    //         &extensions=ext1,ext2,...,extN - добавить в ответ расширения для записи из возможных [files,images,groups,file_list,main_image,select_list_title]
    //         & filters!!!!


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
    //                 morphin => принадлежит массиву и соответствует полиморфной связи
    //                 morphni => принадлежит массиву и соответствует полиморфной связи
    //                 morph => равняется id и соответствует полиморфной связи
    //             !! к операнду like значение обрамляется %% с обеих сторон
    //             доступные условия:
    //                 or => ИЛИ
    //                 and => И
    //             !!невозможно указывать условия, обрамленные в скобки
    //             !! для полиморфных условий необходимо соблюсти синтаксис filterValue: ["App\\Kontragent"].[734,755,743,327] - второй параметр
    //                 точечной нотации должен содержать массив в любом случае, даже если указан операнд morph и/или единственное значение. Пример:
    //                 /contracts?filter=contractable morphin ["App\\Kontragent"].[734]
    //          ----
    //      в качестве fieldName можно указывать связи таблиц,разделенные точками, например, acts?filter=order_.contract_.contract_type_id in [2,5,7]
    //      в примере из модели соответствующей таблице acts будет выбрана связь order_, далее из модели Order выбирается связь contract_,
    //      в котором уже ищется поле contract_type_id, которое и фильтруется в соответствии с операндами.
    //      если необходимо отфильтровать по группам - в качестве последней связи необходимо указывать значение groups, например, следующий пример
    //      выберет из актов только те, позиции которых содержат заданные группы номенклатур: acts?filter=items.nomenklatura_.groups in [36,2]
    //      здесь сначала вызываеся метод items, получающий позиции накладной, потом применяется связь nomenklatura_ из модели ActItem, затем
    //      номенклатура фильтруется по группам. !Группы применяются к последнему параметру, перед ключевым параметром 'groups'!
    //      Для фильтрации полиморфных полей необходимо использовать в качестве последнего параметра точечной нотации значение morph-поля, например
    //      acts?filter=order_.contract_.contractable morphin ["App\\Kontragent"].[734,755] В результате примера получаем все накладные, которые указаны
    //      в качестве contractable-поля модели Contract, как контрагенты с id == [734,755]
    //          ----
    //         &search=text - поиск по всем возможным полям
    //         &tags=id1,id2,...,idN - дополнительный фильтр по тегам (выбор должен содержать строки имеющий хотя бы 1 тег)
    //         &extensions=ext1,ext2,...,extN - добавить в ответ расширения для записи из возможных [files,images,groups,file_list,main_image,select_list_title]
    //         &scope=stock_balance.9, - добавить в запрос scope. Параметры передаются через точки, скопы разделяются запятыми
    //         &offset - смещение относительно 0-го элемента выдачи, отсортированного согласно правилам сортировки (только совместно с limit)
    //         &limit - количество выдаваемых значений выдачи (-1 для отсутствия лимитов)
    //         &trashed=1 - выдавать помеченные на удаление записи


}