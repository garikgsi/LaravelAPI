<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SiteMenuPoint;

class SiteMenuPointController extends Controller
{
    public function index() {
        $data = SiteMenuPoint::all();
        return view('site_admin.menu_point')->with(["data"=>$data]);
    }

    public function getTree() {
        return [
            "is_error" => false,
            "error" => "",
            "data" => $this->get_menu_level(),
        ];
    }

    private function get_menu_level($level=0) {
        $res = [];
        $level_data = SiteMenuPoint::where('parent_menu_point','=',$level)->select('id', 'name')->orderBy('num_order', 'asc')->get();
        foreach($level_data as $level_data1) {
            $data1 = $level_data1->toArray();
            $data1["module"] = $level_data1->find($data1["id"])->module->name;
            $sub_level_data_count = SiteMenuPoint::where('parent_menu_point','=',$data1["id"])->count();
            if ($sub_level_data_count>0) {
                $data1["children"] = $this->get_menu_level($data1["id"]);
            }
            $res[] = $data1;
        }
        return $res;
    }

    public function get_full_tree($menu_point = null, $parent_url='') {
        if ($menu_point) {
            $this_url = trim($menu_point->surl);
            $url = ($parent_url=='/'?'/':$parent_url.'/').($this_url=='/'?'':$this_url);
            $res[] = ["url"=>$url, "menu_point"=>$menu_point];
            switch ($menu_point->module->name) {
                case "html": case "catalog": {
                    if (count($menu_point->children)>0) {
                        foreach($menu_point->children as $menu_child) {
                            $res = array_merge($res, $this->get_full_tree($menu_child, $url));
                        }
                    }
                } break;
                case "news": case "stories": {
                    if (count($menu_point->getList)>0) {
                        foreach ($menu_point->getList as $list_item) {
                            $res = array_merge($res, [["url"=>$url."/".$list_item->surl, "list_item"=>$list_item]]);
                        }
                    }
                } break;
            }
        } else {
            $top_menu_point = SiteMenuPoint::where('parent_menu_point','=','0')->first();
            if ($top_menu_point) {
                $res = $this->get_full_tree($top_menu_point);
            }
        }
        return $res;
    }

    public function show($id) {
        $menu_point = SiteMenuPoint::find($id)->toJson();
        return $menu_point;
    }

    public function store(Request $request)
    {
        $reached_data = $request->all();
        $menu_point = SiteMenuPoint::create($reached_data);
        if ($menu_point) {
// dd($reached_data, $menu_point);

            return response(json_encode($menu_point, JSON_UNESCAPED_UNICODE),201);
        } else {
            $res = ["message"=>"Не удалось добавить запись"];
            return response(json_encode($res, JSON_UNESCAPED_UNICODE),500);
        }
    }

    public function update($id, Request $request)
    {
        $reached_data = $request->all();
        $menu_point = SiteMenuPoint::find($id);
        if ($menu_point && $reached_data) {
            $res = $menu_point->fill($reached_data)->save();
            return response(json_encode($res, JSON_UNESCAPED_UNICODE),200);
        } else {
            $res = ["message"=>"Не передан id или id некорректен"];
            return response(json_encode($res, JSON_UNESCAPED_UNICODE),500);
        }
    }

    public function remove($id)
    {
        $menu_point = SiteMenuPoint::find($id);
        if ($menu_point) {
            $res = $menu_point->delete();
            return response(json_encode($res, JSON_UNESCAPED_UNICODE),200);
        } else {
            $res = ["message"=>"Не передан id или id некорректен"];
            return response(json_encode($res, JSON_UNESCAPED_UNICODE),500);
        }
    }

    public function getContent($id) {
        $menu_point = SiteMenuPoint::find($id);
        if ($menu_point) {
            $res = $menu_point->getList()->get()->toJson();
            return response(json_encode($res, JSON_UNESCAPED_UNICODE),200);
        } else {
            $res = ["message"=>"Не передан id или id некорректен"];
            return response(json_encode($res, JSON_UNESCAPED_UNICODE),500);
        }

    }
}
