<?php

namespace App;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteMenuPointController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;


class SitePages
{
    public function routes()
    {
        foreach ($this->pages() as $page) {
            $url = $page["url"];
            if (isset($page["menu_point"])) {
                $menu_point = $page["menu_point"];
                if ($menu_point->module) {
                    switch ($menu_point->module->name) {
                        case 'news': {
                            Route::get($url, function(Request $request) use ($menu_point) {
                                return App::make(SiteController::class)->callAction('news', ['page' => $menu_point, 'request'=>$request]);
                            });
                        } break;
                        case 'stories': {
// var_dump($menu_point->module);
                            Route::get($url, function(Request $request) use ($menu_point) {
                                App::make(SiteController::class)->callAction('stories', ['page' => $menu_point, 'request'=>$request]);
                            });
                        } break;
                        case 'catalog': {
                            Route::get($url, function(Request $request) use ($menu_point) {
                                App::make(SiteController::class)->callAction('catalog', ['page' => $menu_point, 'request'=>$request]);
                            });
                        } break;
                        default: {
                            Route::get($url, function() use ($menu_point) {
                                return App::make(SiteController::class)->callAction('html', ['page' => $menu_point]);
                            });
                        }
                    }
                }
            } elseif(isset($page["list_item"])) {
                $list_item = $page["list_item"];
                if ($list_item->site_menu_points->module) {
                    switch ($list_item->site_menu_points->module->name) {
                        case 'news': {
                            Route::get($url, function() use ($list_item) {
                                App::make(SiteController::class)->callAction('news_item', ['page' => $list_item]);
                            });
                        } break;
                        case 'stories': {
                            Route::get($url, function() use ($list_item) {
                                App::make(SiteController::class)->callAction('stories_item', ['page' => $list_item]);
                            });
                        } break;
                        case 'catalog': {
                            Route::get($url, function() use ($list_item) {
                                App::make(SiteController::class)->callAction('catalog_item', ['page' => $list_item]);
                            });
                        } break;
                    }
                }
            }

        }
    }

    // все страницы сайта
    private function pages()
    {
        Cache::forget('pages_routes');
        return Cache::remember(
            'pages_routes',
            Carbon::now()->addWeek(),
            function() {
                try {
                    $smpc = new SiteMenuPointController;
                    $pages = $smpc->get_full_tree();
                    // dd($pages);
                    return $pages;
                }
                catch (\Exception $exception) {
                    return new Collection();
                }
            }
        );
    }

}
