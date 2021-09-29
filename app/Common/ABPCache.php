<?php

namespace App\Common;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\ABPTable;

class ABPCache extends Cache {
    // получить значение из кэша или БД и поместить в кэш
    public static function get_select_list($table,$value) {
        // Cache::flush();
            return Cache::remember(
                $table."_".$value,
                Carbon::now()->addWeek(),
                function() use ($table, $value) {
                    try {
                        $t = new ABPTable($table);
                        return $t->find($value)->select_list_title;
                    }
                    catch (\Exception $exception) {
                        return '';
                    }
                }
            );
    }

    // записать значение селекта в кэш
    public static function put_select_list($table,$id,$title) {
        // Cache::flush();
        return Cache::put(
            $table."_".$id,
            $title,
            Carbon::now()->addWeek()
        );
    }
}
