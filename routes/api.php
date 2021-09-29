<?php

use App\Http\Controllers\SiteMenuPointController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Auth\RegisterController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



// Route::group([
//     'prefix' => 'auth'
// ], function () {
//     Route::post('login', 'AuthController@login');
//     Route::post('registration', 'AuthController@registration');
//     Route::post('logout', 'AuthController@logout');
//     Route::post('refresh', 'AuthController@refresh');
//     Route::post('me', 'AuthController@me');
// });

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// внешнее апи
Route::group(['prefix' => 'v1'], function () {
    // авторизация
    Route::group(['namespace' => 'Api'], function () {
        Route::group(['namespace' => 'Auth'], function () {
            Route::post('login', 'LoginController');
            Route::post('logout', 'LogoutController')->middleware('auth:api');
        });
    });
    // // админка сайта
    Route::get('/site_menu_points/tree', 'SiteMenuPointController@getTree');
    Route::get('/site_menu_points/content/{id}', 'SiteContentController@_list');
    // пользовательская информация
    Route::get('/full_user_info/{id?}', 'UserInfoController@show')->middleware('auth:api');

    // права доступа
    Route::get('/roles/{id?}', 'PermissionsController@roles_index')->middleware('auth:api');
    Route::get('/users', 'UserController@index')->middleware('auth:api');
    Route::get('/users/{id}', 'UserController@show')->middleware('auth:api');
    Route::get('/user_roles/{id?}', 'PermissionsController@user_roles')->middleware('auth:api');
    Route::post('/user_roles/{id?}', 'PermissionsController@set_user_roles')->middleware('auth:api');
    // файлы
    Route::get('/files/{table}/{id}/{type}', 'APIController@get_files')->middleware('auth:api'); // выдать все файлы типа type для table-id
    Route::post('/files/{table}/{id}', 'APIController@store_file')->middleware('auth:api'); // добавить новый файл для table-id
    Route::patch('/files/{table}/{id}/{file_id}', 'APIController@edit_file')->middleware('auth:api'); // изменить существующий файл (table-id для проверки прав доступа к записи)
    Route::delete('/files/{table}/{id}/{file_id}', 'APIController@delete_file')->middleware('auth:api'); // удалить существующий файл (table-id для проверки прав доступа к записи)
    // список файлов
    Route::get('/file_list/{table}', 'APIController@get_file_list')->middleware('auth:api'); // выдать все возможные файлы для добавление в список для table
    Route::post('/file_list/{table}/{id}', 'APIController@sync_file_list')->middleware('auth:api'); // синхронизируем список файлов с переданной датой в запросе
    // группы
    Route::get('/groups/{table}', 'APIController@get_groups')->middleware('auth:api'); // получить все возможные группы для таблицы table
    Route::post('/groups/{table}/{id}', 'APIController@add_group')->middleware('auth:api'); // вставить новую группу и присвоить ее id table-id
    // серийные номера
    Route::get('/serials/{table}/{id}', 'APIController@get_serials')->middleware('auth:api'); // получить все серийники для записи таблицы
    Route::put('/serials/{table}/{id}', 'APIController@set_serials')->middleware('auth:api'); // обновить список серийников для записи таблицы
    Route::get('/serials_list/{table}/{id}', 'APIController@get_serials_list')->middleware('auth:api'); // получить все возможные серийники для записи таблицы (для выбора - по сути склад и номенклатура)
    // отчеты
    Route::get('/report/{table}', 'APIController@report')->middleware('auth:api'); // получить данные отчета
    // базовое API
    Route::get('/{table}', 'APIController@index')->middleware('auth:api');;
    Route::get('/{table}/{id}', 'APIController@show')->middleware('auth:api');;
    Route::post('/{table}', 'APIController@store')->middleware('auth:api');;
    Route::put('/{table}/{id}', 'APIController@update')->middleware('auth:api');;
    Route::patch('/{table}/{id}/post', 'APIController@post')->middleware('auth:api');;
    Route::patch('/{table}/{id}', 'APIController@update')->middleware('auth:api');;
    Route::delete('/{table}/{id}', 'APIController@destroy')->middleware('auth:api');;
    // формы
    Route::get('/forms/{table}/{id}/{view?}', 'FormController@get')->middleware('auth:api');
});

// // админка сайта
// Route::get('/site_menu_points','SiteMenuPointController@getTree');
// Route::get('/site_menu_point/{id}','SiteMenuPointController@show');
// Route::put('/site_menu_point/{id}','SiteMenuPointController@update');
// Route::patch('/site_menu_point/{id}','SiteMenuPointController@update');
// Route::post('/site_menu_point','SiteMenuPointController@store');
// Route::delete('/site_menu_point/{id}','SiteMenuPointController@remove');
// Route::get('/site_modules','SiteModuleController@_list');
// Route::get('/site_menu_point_content/{menu_point_id}','SiteContentController@_list');
// Route::get('/site_menu_point_content/{id}','SiteContentController@show');
// // Route::get('/site_contents/{id}','SiteMenuPointController@getContent');
// Route::post('/site_menu_point_content','SiteContentController@store');
// Route::put('/site_menu_point_content/{id}','SiteContentController@update');
// Route::patch('/site_menu_point_content/{id}','SiteContentController@update');