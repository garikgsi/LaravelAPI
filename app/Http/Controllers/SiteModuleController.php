<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SiteModule;

class SiteModuleController extends Controller
{
    public function _list() {
        $site_modules = SiteModule::select('id', 'title')->orderBy('title', 'asc')->get()->toJson();
        return $site_modules;
    }}
