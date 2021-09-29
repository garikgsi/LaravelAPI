<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\SiteMenuPoint;
use App\SiteContent;
use Illuminate\View\View;
use Illuminate\Http\Request;

/**
 * Class PagesController
 * @package App\Http\Controllers\Visible
 */
class SiteController extends Controller
{
    // html- контент
    public function html(SiteMenuPoint $page)
    {
        return view('site.html')->with(compact('page'));
    }
    // список новостей
    public function news(SiteMenuPoint $page, Request $request)
    {
        return view('site.news')->with(compact('page'))->with(compact('request'));
    }
    // список статей
    public function stories(SiteMenuPoint $page, Request $request)
    {
        return view('site.stories')->with(compact('page'))->with(compact('request'));
    }
    // список товаров
    public function catalog(SiteMenuPoint $page, Request $request)
    {
        return view('site.catalog')->with(compact('page'))->with(compact('request'));
    }
    // 1 новость
    public function news_item(SiteContent $page)
    {
        return view('site.news')->with(compact('page'));
    }
    // 1 статья
    public function stories_item(SiteContent $page)
    {
        return view('site.stories')->with(compact('page'));
    }
    // 1 товар
    public function catalog_item(SiteContent $page)
    {
        return view('site.catalog')->with(compact('page'));
    }
}
