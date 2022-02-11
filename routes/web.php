<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
// use App\Http\Controllers\Auth;

use App\Mail\Sendmail;



use App\EdIsm;
use App\Nomenklatura;
use App\DocType;
use App\SkladReceive;
use App\Kontragent;
use App\Firm;
use App\FizLico;
use App\RS;
use App\Bank;
use App\Sklad;
use App\Common\API1C;
use App\Http\Controllers\SiteMenuPointController;
use App\UserInfo;
use App\User;
use Illuminate\Support\Facades\Auth;

// use Storage;
// use Illuminate\Support\Facades\Auth;



use Kily\Tools1C\OData\Client;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view(
        'welcome',
        []
    );
});

Auth::routes(['verify' => true]);

// домашняя страница
Route::get('/home', 'HomeController@index')->name('home')->middleware('verified');

// Начальные данные
// перенос остатков из старой БД
Route::post('/move_remains', 'StartController@move_remains')->middleware('verified');
// синхронизация с 1С
Route::post('/sync1c', 'StartController@sync1c')->middleware('verified');

// файловый менеджер
Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'verified', 'is_site_manager']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

// form test
// Route::get('/forms/{table}/{id}', function ($table, $id) {
//     return view('forms/' . $table . '/main', ["id" => $id]);
// })->middleware('verified', 'is_admin');
Route::get('/forms/{table}/{id}/{view?}', 'FormController@get')->middleware('verified');
// форма в html для загрузки (доступна без авторизации по ссылке)
Route::get('/print/{document_id}', 'FormController@form');

// Route::get('/phpinfo', function () {
//     phpinfo();
// });
