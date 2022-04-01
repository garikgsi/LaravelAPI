<?php

//
// use phpunit for testing!
// возможно возникновения ошибки ["is_error":true,"error":["#PO. Недостаточно...] при выполнении теста [set active production with replaces]
// из-за некорректного алгоритма проведения производства с множественными заменами
//

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
use App\Recipe;
use App\RecipeItem;
use App\RecipeItemReplace;
use App\Firm;
use App\Kontragent;
use Illuminate\Support\Str;
use App\TableTag;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ApiTest extends TestCase
{

    private $api_pref = '/api/v1/';
    // префикс наименования тестируемых данных
    private static $name_pref = '___abp-';
    private static $comment = '___abp-';
    // добавляем тестовые номенклатуры
    private $nomenklatura_count = 50;
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

    /**
     * режим отладки - вывод в лог переменных проваленных тестов
     *
     * @var bool
     */
    private $debug = true;


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

        // удалим стандартные таблицы
        $tables = [
            Nomenklatura::class,
            Manufacturer::class, UserInfo::class,
            SkladReceive::class, SkladReceiveItem::class, Sklad::class,
            Sotrudnik::class, SkladMove::class, SkladMoveItem::class,
            Production::class, ProductionItem::class, ProductionComponent::class,
            Recipe::class, RecipeItem::class, RecipeItemReplace::class
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

        return true;
    }

    // после всех тестов
    public static function tearDownAfterClass(): void
    {
        // parent::tearDownAfterClass();
        self::cleanDb();
    }

    /**
     * тест очистки БД перед началом тестов
     *
     * @return void
     */
    public function testCleanDb() {
        $this->assertTrue(self::cleanDb());
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
                "is_usluga" => $this->faker->numberBetween(0, 3) == 0 ? 1 : 0, //"Услуга" 25%
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

    /**
     * тест изменения
     * Route::put('/{table}/{id}', 'APIController@update')->middleware('auth:api');;
     * Route::patch('/{table}/{id}', 'APIController@update')->middleware('auth:api');;
     *
     * @return void
     */
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

    /**
     * тест удаления
     * Route::delete('/{table}/{id}', 'APIController@destroy')->middleware('auth:api');;
     *
     * @return void
     */
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

    /**
     * тест чтения групп для таблицы
     * Route::get('/groups/{table}', 'APIController@get_groups')->middleware('auth:api'); // получить все возможные группы для таблицы table
     *
     * @return void
     */
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

    /**
     * тест чтения единственной записи
     * Route::get('/{table}/{id}', 'APIController@show')->middleware('auth:api');;
     *
     * @return void
     */
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

    /**
     * тест поиска, сортировки, лимита, смещения
     * Route::get('/{table}?fields=name,id&order=name,desc&search={test}&limit={limit}&offset={offset}', 'APIController@show')->middleware('auth:api');;
     *
     * @return void
     */
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

    /**
     * test тест получения различных форматов данных
     * Route::get('/{table}?odata={odata}', 'APIController@show')->middleware('auth:api');;
     *
     * @return void
     */
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

    /**
     * test проверка выдачи удаленных записей
     * Route::get('/{table}?trashed=1', 'APIController@show')->middleware('auth:api');;
     *
     * @return void
     */
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

    /**
     * test проверка сортировки по группам
     * Route::get('/{table}?tags=tag1,tag2', 'APIController@show')->middleware('auth:api');;
     *
     * @return void
     */
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

    /**
     * test создаем сотрудников
     *
     * @return void
     */
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

    /**
     * test привязываем сотрудников к пользователям
     *
     * @return void
     */
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
        // проверим назначение одного сотрудника двум разным пользователям
        $first_user = $keepers->first();
        $user_id = $first_user->id;
        $user_info_data = [
            "name" => self::$name_pref . Str::random(10),
            "comment" => self::$comment,
            'userable_type' => Sotrudnik::class,
            'userable_id' => $employer->id,
            'user_id' => $user_id,
        ];
        $user_info = UserInfo::where('user_id', $first_user->id)->first();
        $current_user = UserInfo::where('userable_id', $employer->id)->where('userable_type', Sotrudnik::class)->first()->user_;
        $user_info_url = $this->api_pref . 'user_info/' . $user_info->id;
        $user_info_response = $this->actingAs($this->admin_user, 'api')->json('PUT', $user_info_url, $user_info_data);
        $user_info_response->assertStatus(421)
            ->assertJson([
                "is_error" => true,
            ]);
        $this->assertTrue($this->response_has_error($user_info_response, ['уже закреплен за пользователем', $current_user->email]), "diff error: we want $current_user->email, server said " . $this->get_response_error($user_info_response));
    }

    /**
     * test тест создания складов, пользователей-кладовщиков
     *
     * @return void
     */
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


    /**
     * test проверка добавления документов
     *
     * @return void
     */
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

    /**
     * test проверка нулевых остатков по всем складам - до проведения документов
     * Route::get('/{table}?scope=stock_balance.9, - добавить в запрос scope. Параметры передаются через точки, скопы разделяются запятыми
     *
     * @return void
     */
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

    /**
     * test проверяем проведение документа пользователем не обладающим правами
     *
     * @return void
     */
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

    /**
     * test проводим от имени кладовщика
     *
     * @return void
     */
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

    /**
     * test проводим документы от имени администратора
     * PATCH /api/v1/{table_name}/{N}/post - проводим документ с id=N в таблице table_name. В запросе необходимо передать массив полей для проведения (в моделе должны быть отмечены признаком "post"=>true). В ответе count сервер вернет измененную запись
     *
     * @return void
     */
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

    /**
     * test проверяем остатки после прихода (дата не важна)
     *
     * @return void
     */
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
            $check_remains = $this->checkRemains($sklad_id);
            $this->assertTrue($check_remains);
            // $this->assertSame($ostatki_nomenklatur, $response_remains, "remains os sklad $sklad_id & url=$url not matched " . collect($ostatki_nomenklatur)->toJson() . " to data=" . collect($response_remains)->toJson());
        }
    }

    /**
     * test тест создания перемещения с одинаковыми складами отправлени и получения
     *
     * @return void
     */
    public function testCreateEqualInOutSkladMove()
    {
        // все фирмы
        $firms = Firm::where('id', '>', 1)->get();
        // все склады
        $sklads = Sklad::where('comment', self::$comment)->get();
        // вся номенклатура
        $nomenklatura = Nomenklatura::where('comment', self::$comment)->get();
        // генерим склад получения
        $sklad_id = $sklads->random()->id;
        // все сотрудники
        $emloyers = Sotrudnik::where('comment', self::$comment)->get();
        // данные накладной
        $data = [
            'comment' => self::$comment,
            'sklad_out_id' => $sklad_id,
            'sklad_in_id' => $sklad_id,
            'firm_id' => $firms->random()->id,
            'doc_date' => Carbon::today()->subDays(rand(0, 365))->format('Y-m-d'),
            'transitable_type' => Sotrudnik::class,
            'transitable_id' => $emloyers->random()->id,
            'items' => [
                [
                    'nomenklatura_id' => $nomenklatura->random()->id,
                    'kolvo' => $this->faker->randomFloat(0, 1, 100),
                    'comment' => self::$comment,
                ]
            ]
        ];
        // вставляем запись
        $url = $this->api_pref . 'sklad_moves';
        $response = $this->actingAs($this->admin_user, 'api')->json('POST', $url, $data);
        $response->assertStatus(421);
        $response->assertJson([
            "is_error" => true,
        ]);
        $this->assertTrue($this->response_has_error($response, ['Склад отправления равен складу назначения']));
        // try {
        // $response->assertStatus(421);
        // $response->assertJson([
        //     "is_error" => true,
        // ]);
        // $this->assertTrue($this->response_has_error($response, ['1Склад отправления равен складу назначения']));
        // } catch (\Throwable $th) {
        //     dd($url, $data, $response);
        // }
    }

    /**
     * test создадим перемещение по складам
     *
     * @return int id перемещения для дальнейших тестов распроведения прихода
     */
    public function testCreateSkladMove():int
    {
        // текущие остатки
        $ostatki_by_sklad = $this->calcRemains();

        // получаем остатки
        $i = 0;
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
                'sklad_in_id' => $sklad_in->id,
                'firm_id' => $firms->random()->id,
                'doc_date' => Carbon::today()->subDays(rand(0, 365))->format('Y-m-d'),
                'transitable_type' => Sotrudnik::class,
                'transitable_id' => $emloyers->random()->id,
                'items' => []
            ];

            // перенесем только номенклатуры только с четными id и самую первую
            // для самой первой укажем кол-во на N больше, чем есть в остатке
            $N = $this->faker->randomFloat(0, 1, 100);
            foreach ($ostatki_nomenklatur as $nomenklatura_id => $ostatok) {
                $is_first = $i == 0 && count($data['items']) == 0;
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
            try {
                $response->assertStatus(200);
            } catch (\Throwable $th) {
                dd($url, $data, $response);
            }
            $response->assertJson([
                "is_error" => false,
                "count" => 1,
            ]);
            // данные проверим отдельно
            $response_data = $this->response_data($response)->toArray();
            // должен присвоится номер документа
            $this->assertTrue(!is_null($response_data['doc_num']));
            if (!isset($move_id)) $move_id = $response_data['id'];
            $i++;
        }
        return $move_id;
    }

    /**
     * test проверка проведения перемещения ни кладовщиком ни админом
     *
     * @return void
     */
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

    /**
     * test проверка полного проведения перемещения ни кладовщиком отправления
     *
     * @return void
     */
    public function testPermissionsFullSetActiveSkladMoveByKeeper()
    {
        // выбираем первое непроведенное перемещение
        $move = SkladMove::where('comment', self::$comment)->where('is_active', 0)->first();
        // склады
        $sklad_out = Sklad::find($move->sklad_out_id);
        // получим складарей складов отправителя
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

    /**
     * test попытка оприходования на склад получения без проведения со склада отправления
     *
     * @return void
     */
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

    /**
     * test Отправлять(проводить) со склада отправления может только кладовщик склада отправления или администратор
     *
     * @return void
     */
    public function testCheckSendFromSkladOutOnlyKeeperOrAdmin()
    {
        // выбираем первое непроведенное перемещение
        $move = SkladMove::where('comment', self::$comment)->where('is_active', 0)->first();
        // склады
        $sklad_out = Sklad::find($move->sklad_out_id);
        // получим складарей складов отправителя и получателя
        $keeper_sklad_out = Sotrudnik::find($sklad_out->keeper_id)->user();
        // пользователь без прав
        $user = User::where('name', 'like', self::$name_pref . '%')->whereNotIn('id', [$this->admin_user->id, $keeper_sklad_out->id])->take(1)->first();
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

    /**
     * test Принимать(проводить) на склад получения может только кладовщик склада получения или администратор
     *
     * @return void
     */
    public function testCheckReceiveToSkladInOnlyKeeperOrAdmin()
    {
        // выбираем первое непроведенное перемещение
        $move = SkladMove::where('comment', self::$comment)->where('is_active', 0)->first();
        // склады
        $sklad_in = Sklad::find($move->sklad_in_id);
        // получим складарей складов отправителя и получателя
        $keeper_sklad_in = Sotrudnik::find($sklad_in->keeper_id)->user();
        // пользователь без прав
        $user = User::where('name', 'like', self::$name_pref . '%')->whereNotIn('id', [$this->admin_user->id, $keeper_sklad_in->id])->take(1)->first();
        // url
        $url = $this->api_pref . "sklad_moves/$move->id/post";
        // данные
        $data = ["is_in" => 1];
        $response = $this->actingAs($user, 'api')->json('PATCH', $url, $data)->assertStatus(421)
            ->assertJson([
                "is_error" => true,
            ]);
        $this->assertTrue($this->response_has_error($response, ['Принимать(проводить) на склад получения может только кладовщик склада получения или администратор']));
    }

    /**
     * test SkladMove отправляем со склада c количеством, превышающим остатки
     *
     * @return void
     */
    public function testSendSkladMoveWithWrongKolvo()
    {
        // выбираем первое непроведенное перемещение cодержащее запись с ошибочным кол-вом
        $moves = SkladMove::where('comment', self::$comment)->where('is_out', 0)->whereHas('items', function ($query) {
            $query->where('name','>',0);
        })->get();
        $move = $moves->first();
        // позиция, которой недостаточно
        $def_item = $move->items->where('name','>',0)->first();
        // url
        $url = $this->api_pref . "sklad_moves/$move->id/post";
        // данные
        $data = ["is_out" => 1];
        $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $url, $data)->assertStatus(421)
            ->assertJson([
                "is_error" => true,
            ]);
        $this->assertTrue(
            $this->response_has_error(
                $response,
                ['Недостаточно', $def_item->nomenklatura, $move->sklad_out, $def_item->name]
            ),
            "text error. we want [$def_item->nomenklatura], [$move->sklad_out], [$def_item->name], server sent " . $this->get_response_error($response)
        );
        // удаляем строку, содержащую неверную запись
        SkladMoveItem::where('comment', self::$comment)->where('name','>',0)->delete();
    }

    /**
     * test отправляем все перемещения
     *
     * @return void
     */
    public function testSendSkladMove()
    {
        // отправим все, кроме первого
        $moves = SkladMove::where('comment', self::$comment)->where('is_out', 0)->orderBy('id')->get()->skip(1);
        foreach ($moves as $move) {
            // url
            $url = $this->api_pref . "sklad_moves/$move->id/post";
            // данные
            $data = ["is_out" => 1];
            $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $url, $data);
            $response->assertStatus(202);
            // try {
            //     $response->assertStatus(202);
            // } catch (\Throwable $th) {
            //     dd($response);
            // }
            $response->assertJson([
                "is_error" => false,
                "data" => [
                    "is_out" => 1
                ]
            ]);
        }
        //  проверим остатки
        $this->testCheckRemainsAfterReceive();
    }

    /**
     * test примем все перемещения
     *
     * @return void
     */
    public function testReceiveSkladMove()
    {
        // отправим все, кроме первого
        $moves = SkladMove::where('comment', self::$comment)->where('is_in', 0)->get();
        foreach ($moves as $move) {
            // url
            $url = $this->api_pref . "sklad_moves/$move->id/post";
            // данные
            if ($move->is_out == 0) {
                $data = ["is_active" => 1];
            } else {
                $data = ["is_in" => 1];
            }
            $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $url, $data);
            $response->assertStatus(202);
            $response->assertJson([
                "is_error" => false,
                "data" => [
                    "is_out" => 1,
                    "is_active" => 1,
                    "is_in" => 1
                ]
            ]);
        }
        //  проверим остатки
        $this->testCheckRemainsAfterReceive();
    }

    /**
     * тест изменения позиции проведнной приходной накладной на уменьшение кол-ва
     * при имеющимся перемещении товаров на другой склад (без ошибки)
     *
     */
    public function testSubReceiveItem() {
        $all_remains = $this->calcRemains();
        // $move = SkladMove::find($move_id);
        $changed = false;
        $moves = SkladMove::where('comment', self::$comment)->get();
        foreach ($moves as $move) {
            if ($changed) break;
            $moved_nomenkltura_ids = $move->items()->get()->pluck('nomenklatura_id')->all();
            $receives = SkladReceive::where('comment', self::$comment)
                ->where('sklad_id',$move->sklad_out_id)
                ->whereHas('items', function($query) use ($all_remains, $move){
                    $query->whereIn('nomenklatura_id',array_keys($all_remains[$move->sklad_out_id]));
                })
                // ->dd();
                ->get();
            $data = [];
            foreach($receives as $moved_receive) {
                $moved_receive_items = $moved_receive->items()->get();
                $items = [];
                foreach($moved_receive_items as $item) {
                    $current_item = $item->toArray();
                    if (!$changed && in_array($item->nomenklatura_id, $moved_nomenkltura_ids)) {
                        $changed_sklad = $moved_receive->sklad_id;
                        $changed_nomenklatura = $item->nomenklatura_id;
                        $prev_kolvo = isset($all_remains[$moved_receive->sklad_id][$item->nomenklatura_id]) ? $all_remains[$moved_receive->sklad_id][$item->nomenklatura_id] : 0;
                        $orig_kolvo = $item->kolvo;
                        $changed_kolvo = $orig_kolvo > $prev_kolvo ? rand($orig_kolvo-$prev_kolvo, $orig_kolvo) : $orig_kolvo;
                        $current_item['kolvo'] = $changed_kolvo;
                        $changed = true;
                    }
                    $items[] = $current_item;
                }
                if ($changed) {
                    $data = $moved_receive->toArray();
                    $data['items'] = $items;
                    break;
                }
            }
        }

        $this->assertTrue($changed);

        $url = $this->api_pref . "sklad_receives/$moved_receive->id";
        $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $url, $data);
        try {
            $response->assertStatus(202);
        } catch (\Throwable $th) {
            if ($this->debug) Log::info("_ testSubReceiveItem _", [
                'url' => $url,
                'data' => $data,
                'remains_before' => $all_remains[$moved_receive->sklad_id],
                'changed_sklad' => $changed_sklad,
                'changed_nomenklatura' => $changed_nomenklatura,
                'orig_kolvo' => $orig_kolvo,
                'changed_kolvo'=> $changed_kolvo,
                'response' => $response,
            ]);
            dd('testSubReceiveItem',$url);
        }
        $response->assertJson([
            "is_error" => false,
        ]);
        $new_all_remains = $this->calcRemains();
        $delta = $prev_kolvo - $changed_kolvo;

        try {
            $this->assertSame($prev_kolvo-$orig_kolvo+$changed_kolvo, $new_all_remains[$changed_sklad][$changed_nomenklatura]);
        } catch(\Throwable $th) {
            if ($this->debug) Log::info("_ testSubReceiveItem _", [
                'prev_kolvo' => $prev_kolvo,
                'delta' => $delta,
                'remains_before' => $all_remains[$moved_receive->sklad_id],
                'changed_sklad' => $changed_sklad,
                'changed_nomenklatura' => $changed_nomenklatura,
                'orig_kolvo' => $orig_kolvo,
                'changed_kolvo'=> $changed_kolvo,
                'new_all_remains' => $new_all_remains[$changed_sklad],
            ]);
        }
    }

    /**
     * тест распроведения приходной накладной
     * при имеющимся перемещении товаров на другой склад
     *
     * @depends testCreateSkladMove
     * @param int move_id - id перемещения, где может быть дефицит товаров при распроведении прихода
     */
    public function testUnactiveMovedReceive($move_id)
    {
        $all_remains = $this->calcRemains();
        $move = SkladMove::find($move_id);
        $moved_nomenkltura_ids = $move->items()->get()->pluck('nomenklatura_id')->all();
        $receives = SkladReceive::where('comment', self::$comment)
            ->where('sklad_id',$move->sklad_out_id)
            ->whereHas('items', function($query) use ($moved_nomenkltura_ids){
                $query->whereIn('nomenklatura_id',$moved_nomenkltura_ids);
            })
            // ->dd();
            ->get();
        foreach($receives as $receive) {
            if (isset($receive_id)) {
                break;
            } else {
                $deficit = [];
                $receive_items = $receive->items()->get();
                foreach($receive_items as $item) {
                    $n = Nomenklatura::find($item->nomenklatura_id);
                    if ($n->is_usluga == 0) {
                        $nomenklatura_remains = isset($all_remains[$receive->sklad_id][$item->nomenklatura_id]) ? $all_remains[$receive->sklad_id][$item->nomenklatura_id] : 0;
                        if ($nomenklatura_remains < $item->kolvo) {
                            $receive_id = $receive->id;
                            $deficit[$item->nomenklatura_id] = abs($nomenklatura_remains - $item->kolvo);
                            // break;
                        }
                    }
                }
            }
        }
        $this->assertTrue(isset($receive_id));

        $url = $this->api_pref . "sklad_receives/$receive_id/post";
        $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $url, ['is_active'=>0]);
        try {
            $response->assertStatus(421);
        } catch (\Throwable $th) {
            if ($this->debug) Log::info("_ testUnactiveMovedReceive FAIL _", [
                'url' => $url,
                'response' => $response,
                'deficit' => $deficit,
                'all_remains' => $all_remains
            ]);
            dd('testUnactiveMovedReceive',$url);
        }
        $response->assertJson([
            "is_error" => true,
        ]);

        // ошибки
        $remains_errors = ["При распроведении на складе ".Sklad::find($receive->sklad_id)->select_list_title." будет нехватать:"];
        foreach($deficit as $nomenklatura_id=>$kolvo) {
            $n = Nomenklatura::find($nomenklatura_id);
            if ($n->is_usluga != 1) {
                $remains_errors[] = "$n->doc_title в количестве $kolvo $n->edIsm";
            }
        }
        $check_response = $this->response_has_error(
            $response,
            $remains_errors
        );
        try {
            $this->assertTrue($check_response);
        } catch(\Throwable $th) {
            if ($this->debug) Log::info("_ testUnactiveMovedReceive FAIL REMAINS _", [
                'url' => $url,
                'response' => $response,
                'deficit' => $deficit,
                'all_remains' => $all_remains
            ]);
            dd('testUnactiveMovedReceive', $url);
        }
    }

    /**
     * test создание рецептур производства
     *
     * @return void
     */
    public function testCreateRecepie()
    {
        $recipes_count = 6;
        $total_items_count = 0;
        Recipe::where('comment', self::$comment)->forceDelete();
        RecipeItem::where('comment', self::$comment)->forceDelete();
        for($r=0;$r<$recipes_count;$r++) {
            // номенклатура для рецепта
            $nomenklatura_for_recepie = Nomenklatura::where('comment', self::$comment)->get()->random();
            // рецептура
            $data = [
                'comment' => self::$comment,
                'name' => self::$name_pref . Str::random(10),
                'nomenklatura_id' => $nomenklatura_for_recepie->id,
            ];
            // наполняем рецептуру
            $nomenklatura_for_recepie_items = Nomenklatura::where('comment', self::$comment)->whereNotIn('id', [$nomenklatura_for_recepie->id])->get();
            $recepie_items_count = rand(3, round($nomenklatura_for_recepie_items->count() / 3));
            for ($i = 0; $i < $recepie_items_count; $i++) {
                $items[] = [
                    'comment' => self::$comment,
                    'nomenklatura_id' => $nomenklatura_for_recepie_items->random()->id,
                    'kolvo' => $this->faker->randomFloat(0, 1, 20)
                ];
            }
            if ($r==0) {
                $data['items'] = $items;
                $url = $this->api_pref . "recipes";
                $response = $this->actingAs($this->admin_user, 'api')->json('POST', $url, $data);
                $response->assertStatus(200);
                $response->assertJson([
                    "is_error" => false,
                    "count" => 1,
                ]);
                // данные проверим отдельно
                $response_data = $this->response_data($response)->toArray();
                $this->assertSame(count($data['items']), count($response_data['items']));
                $total_items_count += count($data['items']);
            } else {
                $recept = new Recipe();
                $recept->fill($data)->save();
                foreach($items as $item) {
                    $recipe_item = new RecipeItem();
                    $item['recipe_id'] = $recept->id;
                    $recipe_item->fill($item)->save();
                    $total_items_count++;
                }
            }
        }
        $total_recipes = Recipe::where('comment', self::$comment)->get();
        $total_items = RecipeItem::where('comment', self::$comment)->get();
        $this->assertSame($recipes_count, $total_recipes->count(),"Количество созданных рецептур [$recipes_count] не совпадает с количеством рецептур в БД ".$total_recipes->count());
        $this->assertSame($total_items_count, $total_items->count(),"Количество позиций рецептур [$total_items_count] не совпадает с количеством в БД ".$total_items->count());
    }

    /**
     * test создание производства
     *
     * @return int production_id
     */
    public function testCreateProductions():int
    {
        $all_ostatki_by_sklad = $this->calcRemains();
        $production_id = 1;
        while ($production_id == 1) {
            // рецептура, которую будем собирать
            $recipe = Recipe::where('comment', self::$comment)->get()->random();
            $orig_components = $recipe->items()->get()->mapWithKeys(function ($item) {
                return [$item['nomeklatura_id'] => $item['kolvo']];
            })->all();
            // склад
            $sklad = Sklad::where('comment', self::$comment)->get()->random();
            // все фирмы
            $firms = Firm::where('id', '>', 1)->get();
            // кол-во выпускаемой продукции
            $kolvo = rand(1, 10);
            // данные производства
            $data = [
                'comment' => self::$comment,
                'name' => self::$name_pref . Str::random(10),
                'firm_id' => $firms->random()->id,
                'sklad_id' => $sklad->id,
                'recipe_id' => $recipe->id,
                'kolvo' => $kolvo
            ];
            $url = $this->api_pref . "productions";
            $response = $this->actingAs($this->admin_user, 'api')->json('POST', $url, $data);
            $response->assertStatus(200);
            $response->assertJson([
                "is_error" => false,
                "count" => 1,
            ]);
            // данные проверим отдельно
            $response_data = $this->response_data($response)->toArray();
            // созданное производство
            $production = Production::find($response_data['id']);
            // проверим что заполнены члены комиссии
            $same_fields = ['commission_member1', 'commission_member2', 'commission_chairman'];
            $orig_arr = [];
            $test_arr = [];
            foreach ($same_fields as $field) {
                $orig_arr[$field] = $sklad->{$field};
                $test_arr[$field] = $sklad->{$field};
            }
            $this->assertSame($orig_arr, $test_arr, "Члены комиссии не сохранены в производстве");
            // номер документа
            $this->assertTrue(!is_null($production->doc_num));
            // проверим, что созданы изделия
            $production_items = $production->items();
            $this->assertSame($kolvo, $production_items->count(), "Не создались [$kolvo] изделия");
            // проверим, что присвоились серийные номера и компоненты
            foreach ($production_items as $production_item) {
                // серийники
                $this->assertTrue(!is_null($production_item->serial));
                // компоненты
                $test_components = $production_item->components()->get()->mapWithKeys(function ($item) {
                    return [$item['nomeklatura_id'] => $item['kolvo']];
                })->all();
                $this->assertSame($orig_components, $test_components, "компоненты не созданы согласно рецептуре");
            }
            // проверяем наличие производства, необходимого для дальнейшего тестирования
            if (isset($all_ostatki_by_sklad[$production->sklad_id])) {
                $ostatki_na_sklade = $all_ostatki_by_sklad[$production->sklad_id];
                // производимые изделия
                $items = $production->items()->get();
                // компоненты изделий (из рецептур, поэтому у всех одинаковые)
                $components = $items->first()->components()->whereHas('component', function ($query) {
                    $query->where('is_usluga', 0);
                })->get();
                // состав компонентов производства после замен
                $production_components = [];
                // остатки
                $remains_after_active = $ostatki_na_sklade;
                // посчитаем реальные остатки после проведения производства
                foreach ($components as $component) {
                    // проверим, чтобы не было услуг здесь
                    $nomenklatura = Nomenklatura::find($component->nomenklatura_id);
                    if ($nomenklatura->is_usluga == 0) {
                        // необходимое кол-во на партию
                        $production_component_kolvo = $production->kolvo * $component->kolvo;
                        if (isset($remains_after_active[$component->nomenklatura_id])) {
                            $remains_kolvo = abs($remains_after_active[$component->nomenklatura_id]);
                            // кол-во в наличии пойдет на изготовление (в приоритете)
                            $production_components[] = [
                                'nomenklatura_id' => $component->nomenklatura_id,
                                'kolvo' => $remains_kolvo > $production_component_kolvo ? $production_component_kolvo : $remains_after_active[$component->nomenklatura_id],
                                'replaced' => false
                            ];
                            // уменьшаем остатки
                            $remains_after_active[$component->nomenklatura_id] -= $production_component_kolvo;
                        } else {
                            $remains_after_active[$component->nomenklatura_id] = -$production_component_kolvo;
                        }
                    }
                }
                // все отрицательные остатки нужно раскидать по положительным остаткам
                // дефицит
                $deficit = collect($remains_after_active)->filter(function ($value, $key) {
                    return $value < 0;
                })->map(function ($value) {
                    return abs($value);
                });
                // кол-во (штук) недостающих товаров
                $sum_kolvo_deficit = $deficit->values()->sum();
                // положительные остатки после проведения
                $ostatki = collect($remains_after_active)->filter(function ($value, $key) {
                    return $value > 10;
                });
                // кол-во (штук) остатков товаров
                $sum_kolvo_ostatki = $ostatki->values()->sum();
                // логи
                // Log::info("-START-", [
                //     'ostatki' => $ostatki,
                //     'deficit' => $deficit,
                //     'sum_kolvo_ostatki' => $sum_kolvo_ostatki,
                //     'sum_kolvo_deficit' => $sum_kolvo_deficit,
                // ]);

                // остатков должно быть больше 3 наименований, должен быть дефицит и кол-во шт в остатках дб больше кол-ва штук в дефиците
                if ($ostatki->count() > 5 && $deficit->count() > 3 && $sum_kolvo_ostatki > $sum_kolvo_deficit && $production->kolvo>2) {
                    $production_id = $production->id;
                    break;
                }
            }
        }
        return $production_id;
    }

    /**
     * Уменьшение количества готовых изделий в партии невозможно
     *
     * @return void
     */
    public function testSubKolvoProductionItems()
    {
        $prod = Production::where('comment', self::$comment)->where('is_active', 0)->get()->where('kolvo', '>', 1)->random();
        $data = $prod->only(['id', 'kolvo', 'firm_id', 'recipe_id', 'sklad_id', 'is_active', 'doc_date', 'doc_num']);
        $data['kolvo'] = $prod->kolvo - 1;
        $url = $this->api_pref . "productions/" . $prod->id;
        $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $url, $data);
        $response->assertStatus(421);
        $response->assertJson([
            "is_error" => true,
        ]);
        $this->assertTrue(
            $this->response_has_error(
                $response,
                ['Уменьшение количества готовых изделий в партии невозможно']
            )
        );
    }

    /**
     * Проводить можно только кладовщику или администратору
     *
     * @return void
     */
    public function testSetActiveProductionCanOnlyKeeperOrAdmin()
    {
        // выбираем первое непроведенное перемещение
        $production = Production::where('comment', self::$comment)->where('is_active', 0)->first();
        // склады
        $sklad = Sklad::find($production->sklad_id);
        // получим складарей складов отправителя и получателя
        $keeper = Sotrudnik::find($sklad->keeper_id)->user();
        // пользователь без прав
        $user = User::where('name', 'like', self::$name_pref . '%')->whereNotIn('id', [$this->admin_user->id, $keeper->id])->take(1)->first();
        // url
        $url = $this->api_pref . "productions/$production->id/post";
        // данные
        $data = ["is_active" => 1];
        $response = $this->actingAs($user, 'api')->json('PATCH', $url, $data)->assertStatus(421)
            ->assertJson([
                "is_error" => true,
            ]);
        $this->assertTrue($this->response_has_error($response, ['Проводить можно только кладовщику или администратору']));
    }

    /**
     * тест недостаточного количества при проведении производства
     * @depends testCreateProductions
     * @param int production_id
     */
    public function testNotEnoughtWhenSetActiveProduction($production_id)
    {
        // текущие остатки
        $all_ostatki_by_sklad = $this->calcRemains();
        // foreach ($all_ostatki_by_sklad as $ostatki_sklad_id => $ostatki_by_sklad) {
        $production = Production::find($production_id);
        // получаем остатки по складу производства
        $ostatki_by_sklad = $all_ostatki_by_sklad[$production->sklad_id];

        // производимые изделия
        $items = $production->items()->get();
        // компоненты изделий (из рецептур, поэтому у всех одинаковые)
        $components = $items->first()->components()->get();
        // не достаточно на складе
        $not_enough = [];
        $need_kolvo = [];
        // свернем массив компонент производства
        foreach ($components as $component) {
            if ($component->component->is_usluga == 0) {
                // необходимое кол-во на партию
                $production_component_kolvo = $production->kolvo * $component->kolvo;
                if (isset($need_kolvo[$component->nomenklatura_id])) {
                    $need_kolvo[$component->nomenklatura_id] += $production_component_kolvo;
                } else {
                    $need_kolvo[$component->nomenklatura_id] = $production_component_kolvo;
                }
            }
        }
        // проверяем остатки
        foreach ($need_kolvo as $component_nomenklatura_id => $component_kolvo) {
            if (isset($ostatki_by_sklad[$component_nomenklatura_id])) {
                if ($ostatki_by_sklad[$component_nomenklatura_id] < $component_kolvo) {
                    $not_enough[$component_nomenklatura_id] = $component_kolvo - $ostatki_by_sklad[$component_nomenklatura_id];
                }
            } else {
                $not_enough[$component_nomenklatura_id] = $component_kolvo;
            }
        }
        // если недостаточно для производства
        if (count($not_enough) > 0) {
            $remains_errors = ['Недостаточно'];
            // ошибки
            foreach ($not_enough as $not_enough_nomeklatura => $not_enough_kolvo) {
                $n = Nomenklatura::find($not_enough_nomeklatura);
                // $remains_errors[] = "(" . $n->id . ",u=" . $n->is_usluga . ")" . $n->short_title . " в количестве " . $not_enough_kolvo . " " . $n->edIsm;
                $remains_errors[] = $n->short_title . " в количестве " . $not_enough_kolvo . " " . $n->edIsm;
            }
            // пытаемся провести и сравниваем кол-во недостающих номенклатур
            $url = $this->api_pref . "productions/" . $production->id . "/post";
            $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $url, ['is_active' => 1]);
            $response->assertStatus(421);
            $response->assertJson([
                "is_error" => true,
            ]);
            $check_response = $this->response_has_error(
                $response,
                $remains_errors
            );
            // проверки прошли
            if ($check_response) {
                // Log::info("SUCCESS", [
                //     'url' => $url,
                //     'ответ сервера' => $this->get_response_error($response),
                //     'расчетные остатки' => $ostatki_by_sklad,
                //     'свертка кол-ва' => $need_kolvo,
                //     'недостаточное кол-во' => $not_enough,
                //     'ожидаемая ошибка' => implode(",", $remains_errors),
                //     'производство' => $production->only('id', 'kolvo', 'sklad_id'),
                // ]);
            } else {
                // остатки в регистрах
                $reg_ost = Sklad::find($production->sklad_id)->sklad_register()->get();
                $ost = $reg_ost->mapWithKeys(function ($item, $key) {
                    return [$item['nomenklatura_id'] => $item['ou_kolvo']];
                })->all();
                $recepi = RecipeItem::where('recipe_id', $production->recipe_id)->get();
                $recept = $recepi->mapWithKeys(function ($item, $key) {
                    return [$item['nomenklatura_id'] => $item['kolvo']];
                })->all();

                if ($this->debug) Log::info("FAIL", [
                    'url' => $url,
                    'ответ сервера' => $this->get_response_error($response),
                    'расчетные остатки' => $ostatki_by_sklad,
                    'свертка кол-ва' => $need_kolvo,
                    'недостаточное кол-во' => $not_enough,
                    'ожидаемая ошибка' => implode(",", $remains_errors),
                    'производство' => $production->only('id', 'kolvo', 'sklad_id'),
                    'рецептура' => $recept,
                    'остатки в регистрах' => $ost
                ]);
                // dd(1);
            }
            $this->assertTrue(
                $check_response,
                "wrong error from request. we want [" . implode(",", $remains_errors) . "] but got [" . $this->get_response_error($response) . "]"
            );
        }
    }

    /**
     * тест создания замен, дополнений в производство и проведение с контролем количества
     *
     * возвращаем id производства для проведения
     * @depends testCreateProductions
     * @param int production_id
     * @return int
     */
    public function testCreateReplacesForProduction($production_id): int
    {
        // // производство
        // $production = Production::find($production_id);
        // текущие остатки
        $all_ostatki_by_sklad = $this->calcRemains();
        // все производства
        $production = Production::find($production_id);
            // остаток на складе
            if (isset($all_ostatki_by_sklad[$production->sklad_id])) {

                $ostatki_na_sklade = $all_ostatki_by_sklad[$production->sklad_id];
                // производимые изделия
                $items = $production->items()->get();
                // компоненты изделий (из рецептур, поэтому у всех одинаковые)
                $components = $items->first()->components()->whereHas('component', function ($query) {
                    $query->where('is_usluga', 0);
                })->get();
                // состав компонентов производства после замен
                $production_components = [];
                // остатки
                $remains_after_active = $ostatki_na_sklade;
                // посчитаем реальные остатки после проведения производства
                foreach ($components as $component) {
                    // проверим, чтобы не было услуг здесь
                    $nomenklatura = Nomenklatura::find($component->nomenklatura_id);
                    if ($nomenklatura->is_usluga == 0) {
                        // необходимое кол-во на партию
                        $production_component_kolvo = $production->kolvo * $component->kolvo;
                        if (isset($remains_after_active[$component->nomenklatura_id])) {
                            $remains_kolvo = abs($remains_after_active[$component->nomenklatura_id]);
                            // кол-во в наличии пойдет на изготовление (в приоритете)
                            $production_components[] = [
                                'nomenklatura_id' => $component->nomenklatura_id,
                                'kolvo' => $remains_kolvo > $production_component_kolvo ? $production_component_kolvo : $remains_after_active[$component->nomenklatura_id],
                                'replaced' => false
                            ];
                            // уменьшаем остатки
                            $remains_after_active[$component->nomenklatura_id] -= $production_component_kolvo;
                        } else {
                            $remains_after_active[$component->nomenklatura_id] = -$production_component_kolvo;
                        }
                    }
                }
                // все отрицательные остатки нужно раскидать по положительным остаткам
                // дефицит
                $deficit = collect($remains_after_active)->filter(function ($value, $key) {
                    return $value < 0;
                })->map(function ($value) {
                    return abs($value);
                });
                // кол-во (штук) недостающих товаров
                $sum_kolvo_deficit = $deficit->values()->sum();
                // положительные остатки после проведения
                $ostatki = collect($remains_after_active)->filter(function ($value, $key) {
                    return $value > 10;
                });
                // кол-во (штук) остатков товаров
                $sum_kolvo_ostatki = $ostatki->values()->sum();
                // логи
                // Log::info("-START-", [
                //     'ostatki' => $ostatki,
                //     'deficit' => $deficit,
                //     'sum_kolvo_ostatki' => $sum_kolvo_ostatki,
                //     'sum_kolvo_deficit' => $sum_kolvo_deficit,
                // ]);
                // все замены, которые мы планируем сделать в производстве
                $replacements = [];
                // не было замен на уровне рецептур
                $has_recipe_replace = false;
                // не было замен на уровне изделия
                $has_item_replace = false;
                // не было замен на уровне производства
                $has_production_replace = false;
                    // массив остатков
                    $remains = $ostatki->all();

                    // идем по всему дефициту
                    $deficit->each(function ($item, $key) use (&$has_production_replace, &$has_item_replace, &$has_recipe_replace, &$remains, &$production_components, $production, &$replacements, $items) {
                        // дефицитная номенклатура
                        $deficit_nomenklatura_id = $key;
                        // всего по номенклатуре дефицита
                        $deficit_nomenklatura_kolvo = $item;
                        $replaced_nomenklatura = [];
                        // заменяем пока дефицит по номенклатуре положительный
                        while ($deficit_nomenklatura_kolvo > 0) {
                            // фильтруем остатки от нулевых значений и выбираем случайный элемент остатков
                            $nomenklatura_without_replaced = array_filter($remains, function ($value, $key) use ($deficit_nomenklatura_id, $replaced_nomenklatura) {
                                return $value > 0 && $key != $deficit_nomenklatura_id && !in_array($key, $replaced_nomenklatura);
                            }, ARRAY_FILTER_USE_BOTH);
                            if (count($nomenklatura_without_replaced)>0) {
                                $r_nomenklatura_id = array_rand($nomenklatura_without_replaced);
                                $r_ostatok = $remains[$r_nomenklatura_id];
                                // лог остатков по итерациям
                                // Log::info("_ITERATION_", [
                                //     'remains' => $remains,
                                // ]);
                                // добавим замену в рецептуру
                                if (!$has_item_replace) {
                                    // найдем строку рецептуры, которую заменяем
                                    $recipe_item = RecipeItem::where('recipe_id', $production->recipe_id)->where('nomenklatura_id', $deficit_nomenklatura_id)->first();
                                    // изделие
                                    $production_item = $items->random();
                                    // найдем компонент, который заменяем
                                    $production_component = ProductionComponent::where('production_item_id', $production_item->id)->where('nomenklatura_id', $deficit_nomenklatura_id)->get()->last();
                                    // кол-во заменяемого
                                    $replace_kolvo = $r_ostatok >= $production_component->kolvo ? $production_component->kolvo : $r_ostatok;
                                    // добавляем замену
                                    $save_to_recipe = $this->faker->numberBetween(0, 1);
                                    $replacements[] = [
                                        'production_id' => $production->id,
                                        'component_id' => $production_component->id,
                                        'nomenklatura_from_id' => $deficit_nomenklatura_id,
                                        'nomenklatura_to_id' => $r_nomenklatura_id,
                                        'kolvo_from' => 1,
                                        'kolvo_to' => 1,
                                        'save_to_recipe' => $save_to_recipe
                                    ];

                                    // изменяем остаток
                                    $remains[$r_nomenklatura_id] -= $replace_kolvo;
                                    // добавляем компонент
                                    $production_components[] = [
                                        'nomenklatura_id' => $r_nomenklatura_id,
                                        'kolvo' => $replace_kolvo,
                                        'replaced' => true
                                    ];
                                    // изменяем кол-во остатка дефицитной строки
                                    $deficit_nomenklatura_kolvo -= $replace_kolvo;
                                    // замену в изделии сделали
                                    $has_item_replace = true;
                                    // следующая итерация
                                    continue;
                                }
                                // если не было замен на уровне производства
                                if (!$has_production_replace) {
                                    // кол-во заменяемого
                                    $replace_kolvo = $r_ostatok >= $deficit_nomenklatura_kolvo ? $deficit_nomenklatura_kolvo : $r_ostatok;
                                    // добавляем замену
                                    $save_to_recipe = $this->faker->numberBetween(0, 1);
                                    $replacements[] = [
                                        'production_id' => $production->id,
                                        'component_id' => 1,
                                        'nomenklatura_from_id' => $deficit_nomenklatura_id,
                                        'nomenklatura_to_id' => $r_nomenklatura_id,
                                        'kolvo_from' => 1,
                                        'kolvo_to' => 1,
                                        'save_to_recipe' => $save_to_recipe
                                    ];

                                    // изменяем остаток
                                    $remains[$r_nomenklatura_id] -= $replace_kolvo;
                                    // добавляем компонент
                                    $production_components[] = [
                                        'nomenklatura_id' => $r_nomenklatura_id,
                                        'kolvo' => $replace_kolvo,
                                        'replaced' => true
                                    ];
                                    // изменяем кол-во остатка дефицитной строки
                                    $deficit_nomenklatura_kolvo -= $replace_kolvo;
                                    // замену в производстве сделали
                                    $has_production_replace = true;
                                    // следующая итерация
                                    continue;
                                }
                                // остальные замены на уровне замен в рецептуре

                                // найдем строку рецептуры, которую заменяем
                                $recipe_item = RecipeItem::where('recipe_id', $production->recipe_id)->where('nomenklatura_id', $deficit_nomenklatura_id)->get()->first();
                                // необходимое кол-во для производства по строке рецептуры
                                $recipe_item_kolvo_by_production = $recipe_item->kolvo * $production->kolvo;
                                $kolvo_to = $this->faker->numberBetween(0, 2) == 0 ? 0.5 : 1;
                                // необходимое кол-во для замены по строке рецептуры
                                $recipe_item_kolvo_by_production = $recipe_item->kolvo * $kolvo_to * $production->kolvo;
                                // делаем замену
                                $recipe_replace = RecipeItemReplace::where([
                                    'recipe_item_id' => $recipe_item->id,
                                    'nomenklatura_to_id' => $r_nomenklatura_id,
                                ])->first();
                                if ($recipe_replace) {
                                    $recipe_replace->fill([
                                        'comment' => self::$comment,
                                        'kolvo_from' => 1,
                                        'kolvo_to' => $kolvo_to
                                    ])->save();
                                } else {
                                    $recipe_replace = new RecipeItemReplace;
                                    $recipe_replace->fill([
                                        'recipe_item_id' => $recipe_item->id,
                                        'nomenklatura_to_id' => $r_nomenklatura_id,
                                        'comment' => self::$comment,
                                        'kolvo_from' => 1,
                                        'kolvo_to' => $kolvo_to
                                    ])->save();
                                }
                                $replaced_nomenklatura[] = $r_nomenklatura_id;
                                // кол-во заменяемого
                                $replace_kolvo = $r_ostatok > $recipe_item_kolvo_by_production ? $recipe_item_kolvo_by_production : $r_ostatok;
                                // изменяем остаток
                                $remains[$r_nomenklatura_id] -= $replace_kolvo;
                                // добавляем компонент
                                $production_components[] = [
                                    'nomenklatura_id' => $r_nomenklatura_id,
                                    'kolvo' => $replace_kolvo,
                                    'replaced' => true
                                ];
                                // изменяем кол-во остатка дефицитной строки
                                $deficit_nomenklatura_kolvo -= $replace_kolvo;
                                // замену в рецептуре сделали
                                $has_recipe_replace = true;
                            } else {
                                break;
                            }
                        }
                    });

            $recepi = RecipeItem::where('recipe_id', $production->recipe_id)->get();
            $recept = $recepi->mapWithKeys(function ($item, $key) {
                return [$item['nomenklatura_id'] => $item['kolvo']];
            })->all();
            // Log::info("_PRODUCTION ITOGS_", [
            //     'replacements' => $replacements,
            //     'production_components' => $production_components,
            //     'deficit' => $deficit->toArray(),
            //     'ostatki_start' => $ostatki->all(),
            //     'remains' => $remains,
            //     'recept' => $recept,
            //     'production' => ['id' => $production->id, 'kolvo' => $production->kolvo,]
            // ]);

            // если нашли такое производство - сохраним замены
            $this->assertSame($has_item_replace && $has_recipe_replace && $has_recipe_replace, true, 'Не удалось обнаружить подходящее производство, запустите тест еще раз');
            // сохраняем замены
            $data = $production->toArray();
            $data['items'] = $items->toArray();
            $data['replaces'] = $replacements;
            // пытаемся сохранить и сравниваем кол-во недостающих номенклатур
            $url = $this->api_pref . "productions/" . $production->id;
            $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $url, $data);
            $response->assertStatus(202);
            $response->assertJson([
                "is_error" => false,
            ]);

            // наличие сохраненных замен для производства
            foreach ($replacements as $replace) {
                if ($replace['save_to_recipe'] == 1) {
                    $this->assertSame(
                        // ProductionReplace::where('production_id', $production_id)->where('component_id', $replace['component_id'])->where('nomenklatura_from_id', $replace['nomenklatura_from_id'])->where('nomenklatura_to_id', $replace['nomenklatura_to_id'])->count(),
                        RecipeItemReplace::where('comment', '<>', self::$comment)->where('nomenklatura_to_id', $replace['nomenklatura_to_id'])->count(),
                        1,
                        "Не сохранилась замена в рецептуре (recipe_item_replaces)"
                    );
                }
            }

        }
        return $production_id;
    }

    /**
     * тест проведения производства с подготовленными заменами
     *
     * @depends testCreateReplacesForProduction
     * @param  int $production_id
     * @return int $production_id
     */
    public function testSetActiveProductionWithReplaces(int $production_id):int
    {
        if ($production_id > 1) {
            // пытаемся провести и сравниваем кол-во недостающих номенклатур
            $url = $this->api_pref . "productions/" . $production_id . "/post";
            $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $url, ['is_active' => 1]);
            try {
                $response->assertStatus(202);
            } catch (\Throwable $th) {
                dd('PATCH',$url, $response);
            }
            $response->assertJson([
                "is_error" => false,
            ]);
            $p = Production::find($production_id);
            $check_remains = $this->checkRemains($p->sklad_id);

            $this->assertTrue($check_remains);
        }
        return $production_id;
    }

    /**
     * тест проверки наполнения изделия компонентами и корректности подсчета себестоимости
     *
     * @depends testSetActiveProductionWithReplaces
     * @param  int $production_id
     * @return int $production_id
     */
    public function testCalcComponentsForActiveProduction(int $production_id): int
    {
        if ($production_id>1) {
            $p = Production::find($production_id);
            $recipe_items = RecipeItem::where('recipe_id',$p->recipe_id)->get();
            $items = $p->items()->get();
            foreach($items as $item) {
                $components = $item->components()->get();
                $this->assertTrue($recipe_items->count()<=$components->count()); //"Кол-во компонентов изделия меньше количества набора рецептуры"
                $self_price = $components->sum('summa');
                $this->assertTrue($self_price>0);//"Себестоимость изделия ==0"
                $this->assertSame(round($item->summa,1), round($self_price,1),"Себестоимость изделия [".round($item->summa,2)."] не совпадает с суммой компонентов [".round($self_price,2)."]");
            }
        }
        return $production_id;
    }

    /**
     * тест отметки собраным изделие производства и расчет остатков
     *
     * @depends testCalcComponentsForActiveProduction
     * @param  int $production_id
     * @return int $production_item_id
     */
    public function testSetProductedItemForActiveProduction(int $production_id): int
    {
        $production_item_id = 1;
        if ($production_id>1) {
            $p = Production::find($production_id);
            $items = $p->items()->get();
            $production_items = $items->random(rand(2,$p->kolvo));
            $production_item = $production_items->first();
            $production_item_id = $production_item->id;
            // пытаемся пометить проведенным изделие
            $url = $this->api_pref . "production_items/" . $production_item_id;
            $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $url, ['is_producted' => 1]);
            try {
                $response->assertStatus(202);
                $response->assertJson([
                    "is_error" => false,
                ]);
            } catch (\Throwable $th) {
                dd($production_item_id, $response);
            }
            $producted_item = ProductionItem::find($production_item_id);
            $this->assertSame(intVal($producted_item->is_producted), 1,"Изделие не отмечено собранным");
            $is_usluga = false;
            try {
                $nomenklatura = $production_item->product();
                if ($nomenklatura->is_usluga==1) {
                    $is_usluga = true;
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
            if ($production_item->register()->get()->count() != $is_usluga?0:1) {
                if ($this->debug) Log::info("- PRODUCTION ITEM NOT REGISTERED -", [
                    'production_item' => $production_item->toArray(),
                    'nomenklatura' => $production_item->product(),
                    'register' => $production_item->register()->get()->toArray()
                ]);
            }
            $this->assertSame($production_item->register()->get()->count(),$is_usluga?0:1 ,"Изделие не добавлено в регистр хранения");
            // произведем остальные изделия
            foreach($production_items as $pi) {
                if ($pi->id==$production_item_id) continue;
                $pi->is_producted = 1;
                $pi->save();
                $this->assertSame($pi->register()->get()->count(),$is_usluga?0:1 ,"Изделие не добавлено в регистр хранения");
            }

            $check_remains = $this->checkRemains($p->sklad_id);
            $this->assertTrue($check_remains);

        }
        return $production_item_id;
    }

    /**
     * тест создания перемещения по складам и присваивания серийных номеров для перемещаемых позиций произведенных изделий
     * URL: PUT '/serials/{table}/{id}' -> 201
     *
     * @depends testSetProductedItemForActiveProduction
     * @param  mixed $production_item_id
     * @return array [int 'production_item_id', int 'sklad_move_id']
     */
    public function testMoveProductedItem(int $production_item_id):array {
        $debug = false;
        $firms = Firm::where('id', '>', 1)->get();
        $emloyers = Sotrudnik::where('comment', self::$comment)->get();
        $production_item = ProductionItem::find($production_item_id);
        $production = $production_item->production()->first();
        $target_sklad = Sklad::where('comment', self::$comment)->whereNotIn('id',[$production->sklad_id])->get()->random();
        $target_sklad2 = Sklad::where('comment', self::$comment)->whereNotIn('id',[$production->sklad_id, $target_sklad->id])->get()->random();
        $target_kolvo = 1;
        // $target_kolvo2 = $production->kolvo - $target_kolvo;
        $target_kolvo2 = 1;
        $data = [
            'comment' => self::$comment,
            'sklad_out_id' => $production->sklad_id,
            'sklad_in_id' => $target_sklad->id,
            'firm_id' => $firms->random()->id,
            'doc_date' => Carbon::today()->subDays(rand(0, 365))->format('Y-m-d'),
            'transitable_type' => Sotrudnik::class,
            'transitable_id' => $emloyers->random()->id,
        ];
        $sklad_move = new SkladMove();
        $sklad_move->fill($data)->save();
        $item = [
            'sklad_move_id'=>$sklad_move->id,
            'nomenklatura_id' => $production->recipes()->first()->nomenklatura()->first()->id,
            'kolvo' => $target_kolvo,
            'comment' => self::$comment,
        ];
        Log::info("- MOVE ITEM -", [
            'sklad_move' => $data,
            'sklad_move_item' => $item,
        ]);
        $sklad_move_item = new SkladMoveItem();
        $sklad_move_item->fill($item)->save();

        $sklad_move2 = new SkladMove();
        $sklad_move2_data = $data;
        $sklad_move2_data = array_merge($data,['sklad_in_id' => $target_sklad2->id]);
        $sklad_move2->fill($sklad_move2_data)->save();
        $sklad_move2_item = new SkladMoveItem();
        $sklad_move2_item->fill(array_merge($item, ['kolvo'=> $target_kolvo2, 'sklad_move_id'=>$sklad_move2->id]))->save();

        // добавляем серийный номер
        $url = $this->api_pref . "serials/sklad_move_items/$sklad_move_item->id";
        $request_data = ['data'=>json_encode($production_item->series()->get()->pluck('id')->values()->all())];
        // $request_data = ['data'=>$production_item->series()->get()->pluck('id')->all()];
        if (count($request_data)!=1) dd($url, $request_data, $production_item->toArray());
        $this->assertSame(1,count($request_data),'Для произведенного изделия получено серийных номеров !=1');
        if ($debug) Log::info("- TEST SERIALS -", [
            'url' => $url,
            'request_data' => $request_data,
        ]);

        $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $url, $request_data);
        try {
            $response->assertStatus(201);
        } catch (\Throwable $th) {
            dd($url,$request_data, $response);
        }
        $response->assertJson([
            "is_error" => false,
        ]);
        return ['production_item_id'=>$production_item_id, 'sklad_move_id'=>$sklad_move->id];
    }

    /**
     * тест чтения серийных номеров для производства
     * url: get '/serials/{table}/{id}' -> 200
     *
     * @depends testSetProductedItemForActiveProduction
     * @param  int $production_item_id
     * @return void
     */
    public function testGetSerialsForProductionItem(int $production_item_id) {
        // читаем серийные номера для записи
        $url = $this->api_pref . "serials/production_items/$production_item_id";
        $response = $this->actingAs($this->admin_user, 'api')->json('GET', $url);
        try {
            $response->assertStatus(200);
        } catch (\Throwable $th) {
            dd($url);
        }
        $response->assertJson([
            "is_error" => false,
        ]);
        $db_serials = $this->response_data($response)->pluck('id')->toArray();
        // должны быть на складе серийный номер для итемса $production_item_id
        $production_item = ProductionItem::find($production_item_id);
        $serials = $production_item->series()->get()->pluck('id')->toArray();
        // Log::info("- SERIALS -", [
        //     'serials' => $serials,
        //     'db_serials' => $db_serials,
        // ]);

        $this->assertTrue(in_array($serials[0], $db_serials));
    }

    /**
     * тест выборки доступных серийных номеров для записи таблицы
     * url: get '/serials_list/{table}/{id}' -> 200
     *
     * @depends testMoveProductedItem
     * @param  array [int 'production_item_id', int 'sklad_move_id']
     * @return void
     */
    public function testGetSerialListForNomenklatura($production_move_array) {
        $production_item = ProductionItem::find($production_move_array['production_item_id']);
        $production = $production_item->production()->first();
        $production_items = $production->items()->get();
        $production_nomenklatura = $production->product()->first();

        $move = SkladMove::find($production_move_array['sklad_move_id']);
        // $move_item = $move->items()->where('nomenklatura_id',$production_nomenklatura->id)->first();
        $move_item = $move->items()->first();

        $url = $this->api_pref . "serials_list/sklad_move_items/$move_item->id";
        $response = $this->actingAs($this->admin_user, 'api')->json('GET', $url);
        try {
            $response->assertStatus(200);
        } catch (\Throwable $th) {
            dd($url);
        }
        $response->assertJson([
            "is_error" => false,
        ]);
        $serials = $this->response_data($response)->only('id')->sort()->toArray();
        // должны быть на складе серийный номер для итемса $production_item_id
        $production_serials = [];
        foreach($production_items as $production_item) {
            $production_serials[] = $production_item->series()->first()->id;
        }
        $production_serials = sort($production_serials);
        $series = $production_item->series()->get()->only('id')->toArray();
        $serials = sort($series);
        $this->assertSame($production_serials, $serials);
    }

    /**
     * тест присвоения серийного номера перемещаемым изделиям
     * url: put '/serials/{table}/{id}' -> 201
     *
     * @depends testMoveProductedItem
     * @param  array [int 'production_item_id', int 'sklad_move_id']
     * @return array [int 'production_item_id', int 'sklad_move_id']
     */
    public function testSetSerialForMoveProductionItems(array $production_move_array) {
        $debug = false;
        $production_item = ProductionItem::find($production_move_array['production_item_id']);
        $production = $production_item->production()->first();
        $production_items = $production->items()->get();
        $production_nomenklatura = $production->product();

        $move = SkladMove::where('comment', self::$comment)
            ->where('id','<>',$production_move_array['sklad_move_id'])
            ->where('is_active',0)
            ->whereHas('items', function($query) use($production_nomenklatura){
                $query->where('nomenklatura_id',$production_nomenklatura->id);
            //}) ->dd();
            })->first();
        // dd($production_move_array['sklad_move_id'], $production_nomenklatura->id, $move->id);
        // $move_item = $move->items()->->first();
        $move_item = $move->items()->first();

        $data = ['data' => json_encode($production_item->series()->pluck('id')->values()->all())];
        $url = $this->api_pref . "serials/sklad_move_items/$move_item->id";
        if ($debug) Log::info("- TEST SERIALS -", [
            'url' => $url,
            'request_data' => $data,
        ]);

        $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $url, $data);
        try {
            $response->assertStatus(201);
        } catch (\Throwable $th) {
            dd($url, $data, $response);
        }
        $response->assertJson([
            "is_error" => false,
        ]);
        return $production_move_array;
    }

    /**
     * тест повторного присвоения серийного номера перемещаемым изделиям
     * url: put '/serials/{table}/{id}' -> 201
     *
     * @depends testSetSerialForMoveProductionItems
     * @param  array [int 'production_item_id', int 'sklad_move_id']
     * @return array [int 'production_item_id', int 'sklad_move_id']
     */
    public function testDuplicateSetSerialForMoveProductionItems(array $production_move_array) {
        $debug = false;
        $production_item = ProductionItem::find($production_move_array['production_item_id']);
        $production = $production_item->production()->first();
        $production_items = $production->items()->get();
        $production_nomenklatura = $production->product();

        $move = SkladMove::find($production_move_array['sklad_move_id']);
        // dd($production_move_array['sklad_move_id'], $production_nomenklatura->id, $move->id);
        // $move_item = $move->items()->->first();
        $move_item = $move->items()->first();

        $data = ['data' => json_encode($production_item->series()->pluck('id')->values()->all())];
        $url = $this->api_pref . "serials/sklad_move_items/$move_item->id";
        if ($debug) Log::info("- TEST SERIALS -", [
            'url' => $url,
            'request_data' => $data,
        ]);

        $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $url, $data);
        try {
            $response->assertStatus(201);
        } catch (\Throwable $th) {
            dd($url, $data, $response);
        }
        $response->assertJson([
            "is_error" => false,
        ]);
        return $production_move_array;
    }


    /**
     * тест проведения перемещения произведенного изделия с указанным серийным номером
     *
     * @depends testMoveProductedItem
     * @param  array [int 'production_item_id', int 'sklad_move_id']
     * @return array [int 'production_item_id', int 'sklad_move_id']
     */
    public function testSetActiveMoveProductedItemWithSerial(array $production_move_array) {
        $sklad_move = SkladMove::find($production_move_array['sklad_move_id']);
        $url = $this->api_pref . "sklad_moves/$sklad_move->id/post";
        $data = ["is_active" => 1];
        $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $url, $data);
        try {
            $response->assertStatus(202);
        } catch (\Throwable $th) {
            dd($url, $data, $response);
        }
        $response->assertJson([
            "is_error" => false,
            "data" => [
                "is_out" => 1,
                "is_active" => 1,
                "is_in" => 1
            ]
        ]);
        //  проверим остатки
        $check_remains = $this->checkRemains($sklad_move->sklad_out_id);
        $this->assertTrue($check_remains);
        $check_remains = $this->checkRemains($sklad_move->sklad_in_id);
        $this->assertTrue($check_remains);

        return $production_move_array;
    }


    /**
     * тест проведения перемещения произведенного изделия с отсутствующим на складе серийным номером
     *
     * @depends testSetActiveMoveProductedItemWithSerial
     * @param  array [int 'production_item_id', int 'sklad_move_id']
     * @return void
     */
    public function testSetActiveMoveProductedItemWithDeficitSerial(array $production_move_array) {
        $production_item = ProductionItem::find($production_move_array['production_item_id']);
        $production = $production_item->production()->first();
        $production_nomenklatura = $production->product();


        $sklad_move = SkladMove::where('comment', self::$comment)->where('is_out',0)->where('id','<>',$production_move_array['sklad_move_id'])
        ->whereHas('items',function($query) use($production_nomenklatura){
            $query->where('nomenklatura_id',$production_nomenklatura->id);
        })->first();
        $url = $this->api_pref . "sklad_moves/$sklad_move->id/post";
        $data = ["is_active" => 1];
        $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $url, $data);
        $response->assertStatus(421);
        $response->assertJson([
            "is_error" => true
        ]);
        //  проверим ошибку
        $sn = $production_item->series()->first()->number;
        $text_error = " с серийным № $sn нет на складе";
        try {
            $this->assertTrue($this->response_has_error($response,[$text_error]));
        } catch (\Throwable $th) {
            dd($url, $text_error, $this->get_response_error($response), $production_item->series()->first()->toArray());
        }
    }

    /**
     * тест изменения позиции проведенного перемещения без превышения остатков
     *
     * @depends testSetSerialForMoveProductionItems
     * @param  array [int 'production_item_id', int 'sklad_move_id']
     * @return void
     */
    public function testChangeProductedItemComponentWithoutDeficit(array $production_move_array):void {
        $production_item = ProductionItem::find($production_move_array['production_item_id']);
        $production = $production_item->production()->first();
        /**
         * остатки на складе производства
         * @var array
        */
        $sklad_remains = $this->calcRemains()[$production->sklad_id];
        $available_nomenklatura = collect($sklad_remains)->keys()->all();
        $production_item_component_with_remains = $production_item->components()->whereIn('nomenklatura_id', $available_nomenklatura)->get();
        $production_item_component_with_remains_count = $production_item_component_with_remains->count();

        if ($production_item_component_with_remains_count>0) {
            $components = $production_item_component_with_remains->random(1,$production_item_component_with_remains_count)->pluck('id')->all();
            /**
             * данные производства с измененным кол-вом в компонентах
             * @var array
            */
            $request_data = $production->toArray();
            $request_data['items'] = [];
            foreach($production->items as $pi) {
                $item_with_components = $pi->toArray();
                $item_with_components['components'] = [];
                foreach($pi->components as $pc) {
                    if (in_array($pc->id, $components)) {
                        $component = $pc->toArray();
                        $component_remains = $sklad_remains[$component['nomenklatura_id']];
                        $component['kolvo'] += rand(1,$component_remains);
                        $item_with_components['components'][] = $component;
                    } else {
                        $item_with_components['components'][] = $pc->toArray();
                    }
                }
                $request_data['items'][] = $item_with_components;
            }
            $url = $this->api_pref . "productions/$production->id";
            $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $url, $request_data);
            try {
                $response->assertStatus(202);
            } catch (\Throwable $th) {
                if ($this->debug) Log::info("_ testChangeProductedItemComponentWithoutDeficit _", [
                    'url' => $url,
                    'request_data' => $request_data,
                    'response' => $response,
                ]);
                dd('testChangeProductedItemComponentWithoutDeficit',$url);
            }
            $response->assertJson([
                "is_error" => false,
            ]);
            //  проверим остатки
            $check_remains = $this->checkRemains($production->sklad_id);
            $this->assertTrue($check_remains);
        }

    }

        /**
     * тест изменения позиции проведенного перемещения с превышением остатков
     *
     * @depends testSetSerialForMoveProductionItems
     * @param  array [int 'production_item_id', int 'sklad_move_id']
     * @return void
     */
    public function testChangeProductedItemComponentWithDeficit(array $production_move_array):void {
        $production_item = ProductionItem::find($production_move_array['production_item_id']);
        $production = $production_item->production()->first();
        /**
         * остатки на складе производства
         * @var array
        */
        $sklad_remains = $this->calcRemains()[$production->sklad_id];
        $available_nomenklatura = collect($sklad_remains)->keys()->all();
        $production_item_component_without_remains = $production_item->components()->whereNotIn('nomenklatura_id', $available_nomenklatura)->whereHas('component', function($query){
            $query->where('is_usluga',0);
        })->get();
        $production_item_component_without_remains_count = $production_item_component_without_remains->count();
        if ($production_item_component_without_remains_count>0) {
            $components = $production_item_component_without_remains->random(1,$production_item_component_without_remains_count)->pluck('id')->all();
            // $component = $production_item_component_without_remains->first()->toArray();
            // $component_remains = $sklad_remains[$component['nomenklatura_id']];
            $deficit = [];
            /**
             * данные производства с измененным кол-вом в компонентах
             * @var array
            */
            $request_data = $production->toArray();
            $request_data['items'] = [];
            foreach($production->items as $pi) {
                $item_with_components = $pi->toArray();
                $item_with_components['components'] = [];
                foreach($pi->components as $pc) {
                    if (in_array($pc->id,$components)) {
                        $component = $pc->toArray();
                        $deficit_kolvo = rand(15,120);
                        $component['kolvo'] += $deficit_kolvo;
                        $deficit[$component['nomenklatura_id']] = $deficit_kolvo;

                        $item_with_components['components'][] = $component;
                    } else {
                        $item_with_components['components'][] = $pc->toArray();
                    }
                }
                $request_data['items'][] = $item_with_components;
            }
            $url = $this->api_pref . "productions/$production->id";
            $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $url, $request_data);
            try {
                $response->assertStatus(421);
            } catch (\Throwable $th) {
                if ($this->debug) Log::info("_ testChangeProductedItemComponentWithDeficit _", [
                    'url' => $url,
                    'request_data' => $request_data,
                    'response' => $response,
                ]);
                dd('testChangeProductedItemComponentWithDeficit',$url, $request_data);
            }
            $response->assertJson([
                "is_error" => true,
            ]);
            $remains_errors = [];
            // ошибки
            $sklad = Sklad::find($production->sklad_id);
            foreach($deficit as $nomenklatura_id=>$kolvo) {
                $n = Nomenklatura::find($nomenklatura_id);
                $remains_errors[] = "Для списания $n->doc_title на $sklad->select_list_title не хватает $kolvo $n->edIsm";
            }
            $check_response = $this->response_has_error(
                $response,
                $remains_errors
            );
            $this->assertTrue($check_response);
        }
    }

    /**
     * тест разборки перемещенного изделия
     *
     * @depends testMoveProductedItem
     * @param  array [int 'production_item_id', int 'sklad_move_id']
     * @return void
     */
    public function testUnbuildingProductedItem(array $production_move_array) {
        $url = $this->api_pref . "production_items/" . $production_move_array['production_item_id'];
        $response = $this->actingAs($this->admin_user, 'api')->json('PUT', $url, ['is_producted' => 0]);
        if ($this->debug) Log::info("_ testUnactiveMovedProduction _", [
            'url' => $url,
            'response' => $response,
        ]);

        try {
            $response->assertStatus(421);
            $response->assertJson([
                "is_error" => true,
            ]);
        } catch (\Throwable $th) {
            dd('testUnbuildingProductedItem', $url);
        }

    }

    /**
     * тест распроведения перемещенного производства
     *
     * @depends testMoveProductedItem
     * @param  array $production_move_array
     * @return void
     */
    public function testUnactiveMovedProduction(array $production_move_array) {
        $production = ProductionItem::find($production_move_array['production_item_id'])->production()->first();
        $url = $this->api_pref . "productions/" . $production->id."/post";
        $response = $this->actingAs($this->admin_user, 'api')->json('PATCH', $url, ['is_active' => 0]);
        if ($this->debug) Log::info("_ testUnactiveMovedProduction _", [
            'url' => $url,
            'response' => $response,
        ]);

        try {
            $response->assertStatus(421);
            $response->assertJson([
                "is_error" => true,
            ]);
        } catch (\Throwable $th) {
            dd('testUnbuildingProductedItem', $url);
        }
    }

    // TODO тест выборки вложенных таблиц



    /**
     * сверяем остатки в БД с расчетной моделью
     *
     * @param  int $sklad_id
     * @return bool
     */
    private function checkRemains($sklad_id): bool
    {
        $db_remains = Sklad::find($sklad_id)->get_remains()->sortKeys()->filter(function ($remains) {
            return $remains > 0;
        })->toArray();
        $all_remains = $this->calcRemains();
        if (isset($all_remains[$sklad_id])) {
            $calc_remains = $all_remains[$sklad_id];
        } else {
            $calc_remains = [];
        }
        $res = $db_remains === $calc_remains;
        if (!$res && $this->debug) {
            Log::info("_ WRONG REMAINS _", [
                'db_remains' => $db_remains,
                'calc_remains' => $calc_remains,
            ]);
        }
        return $res;
    }

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
                $nomenklatura = Nomenklatura::find($item->nomenklatura_id);
                if ($nomenklatura->is_usluga == 0) $remains->push([
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
                $nomenklatura = Nomenklatura::find($item->nomenklatura_id);
                if ($nomenklatura->is_usluga == 0) $remains->push([
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
                $nomenklatura = Nomenklatura::find($item->nomenklatura_id);
                if ($nomenklatura->is_usluga == 0) $remains->push([
                    'document_type' => SkladMove::class,
                    'document_id' => $move_out->id,
                    'document_date' => $move_out->doc_date,
                    'sklad_id' => $move_out->sklad_in_id,
                    'saldo' => 1,
                    'nomenklatura_id' => $item->nomenklatura_id,
                    'kolvo' => $item->kolvo,
                    'price' => $item->price,
                    'summa' => $item->summa
                ]);
            }
        }
        // все проведенные производства
        $productions = Production::where('comment', self::$comment)->where('is_active', '=', 1)->get();
        foreach ($productions as $production) {
            // все произведенные изделия
            $items = $production->items()->get();
            // списываем все компоненты
            foreach ($items as $item) {
                $components = $item->components()->get();
                foreach ($components as $component) {
                    $nomenklatura = Nomenklatura::find($component->nomenklatura_id);
                    if ($nomenklatura->is_usluga == 0) $remains->push([
                        'document_type' => Production::class,
                        'document_id' => $production->id,
                        'document_date' => $production->doc_date,
                        'sklad_id' => $production->sklad_id,
                        'saldo' => 0,
                        'nomenklatura_id' => $component->nomenklatura_id,
                        'kolvo' => -$component->kolvo,
                        'price' => $component->price,
                        'summa' => $component->summa
                    ]);
                }
                // если изделие собрано - оприходуем его
                if ($item->is_producted == 1) {
                    $nomenklatura = $production->recipes->nomenklatura;
                    if ($nomenklatura->is_usluga == 0) $remains->push([
                        'document_type' => Production::class,
                        'document_id' => $production->id,
                        'document_date' => $production->doc_date,
                        'sklad_id' => $production->sklad_id,
                        'saldo' => 1,
                        'nomenklatura_id' => $nomenklatura->id,
                        'kolvo' => floatVal(1),
                        'price' => $nomenklatura->avg_price,
                        'summa' => $nomenklatura->avg_price
                    ]);
                }
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
            })->filter(function($remains){
                return $remains>0;
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
        $debug = $this->debug;
        $failed_assert = [];
        $err = $this->get_response_error($response);
        foreach ($text_array as $needle) {
            if (mb_strpos($err, $needle) === false) {
                if ($debug) {
                    $failed_assert[] = $needle;
                } else {
                    return false;
                }
            }
        }
        if ($debug && count($failed_assert)>0) {
            Log::info("_ WRONG REQUEST ERROR SET _", [
                'response error' => $err,
                'failed strings' => $failed_assert,
            ]);
            return false;
        }
        return true;
    }

    private function get_response_error($response)
    {
        try {
            return json_decode($response->getContent())->error[0];
        } catch (\Throwable $th) {
            return null;
        }
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
