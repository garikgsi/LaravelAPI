<?php

namespace App\Http\Controllers;
use App\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class SiteContentController extends Controller
{
    public function _list($menu_point_id) {
        $res = [];
        $res = SiteContent::where('menu_point_id',$menu_point_id)->get()->toJson();
        return [
            "is_error" => false,
            "error" => "",
            "data" => $res,
        ];
    }

    public function show($id) {
        $res = [];
        $menu_point = SiteContent::find($id);
        if ($menu_point) {
            $res = $menu_point->toJson();
        }
        return $res;
    }

    public function store(Request $request) {
        $reached_data = $request->all();
        if ($request->hasFile('img')) {
            $img_path = $request->file('img')->store('', 'local');
            if ($img_path) $reached_data["img"] = $img_path; else $reached_data["img"] = NULL;
        }
        $menu_content = SiteContent::create($reached_data);
// dd($reached_data, $menu_content);
        if ($menu_content) {
            return response(json_encode($menu_content, JSON_UNESCAPED_UNICODE),201);
        } else {
            $res = ["message"=>"Не удалось добавить запись"];
            return response(json_encode($res, JSON_UNESCAPED_UNICODE),500);
        }
    }

    public function update($id, Request $request)
    {
        // dd($request->all());
        $reached_data = $request->all();
        $menu_content = SiteContent::find($id);
        if ($menu_content) {
            if ($reached_data) {
                if ($request->has("img")) {
                    $storage = Storage::disk("local");
                    $old_file = $menu_content->img;
                    if ($old_file != $reached_data["img"]) {
                        if ($old_file && $storage->exists($old_file)) {
                            $storage->delete($old_file);
                        }
                    }
                    if ($request->hasFile('img')) {
                        $img_path = $request->file('img')->store('', 'local');
                        if ($img_path) $reached_data["img"] = $img_path; else $reached_data["img"] = NULL;
                    } else {
                        $reached_data["img"] = NULL;
                    }
                }
                $res = $menu_content->fill($reached_data)->save();
                return response(json_encode($res, JSON_UNESCAPED_UNICODE),200);
            } else {
                $res = ["message"=>"Данные не переданы","request"=>$request];
                return response(json_encode($res, JSON_UNESCAPED_UNICODE),500);
            }
        } else {
            $res = ["message"=>"Не передан id или id некорректен"];
            return response(json_encode($res, JSON_UNESCAPED_UNICODE),500);
        }
    }

}
