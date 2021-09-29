<?php

namespace App;

use App\ABPTable;
use App\SkladRegister;
use App\Sklad;

class ReportSkladRemains extends SkladRegister
{
    public function __construct() {
        parent::__construct();

        $this->table_type("report");
        $this->table("sklad_register");
        // добавляем читателей
        $this->appends = array_merge($this->appends,[]);
    }

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(function ($query) {

            // $query->select(['sklad_id','nomenklatura_id'])->selectRaw('SUM(ou_kolvo) as kolvo')->whereNotNull('ou_date')->groupBy('sklad_id','nomenklatura_id');
            $query->select(['sklad_id','nomenklatura_id'])->selectRaw('SUM(ou_kolvo) as kolvo')->groupBy('sklad_id','nomenklatura_id');

            // $ou = clone $query;
            // $bu = clone $query;

            // $total = $bu->selectRaw('deleted_at,deleted_by,doc_date, ou_date, sklad_id,firm_id,kontragent_id,price,summa,nds_id, nomenklatura_id, SUM(kolvo) as kolvo, 0 as ou_kolvo')
            //     ->whereNull('ou_date')
            //     ->groupBy('sklad_id','nomenklatura_id')
            //     ->union(
            //         $ou->selectRaw('deleted_at,deleted_by,doc_date, ou_date, sklad_id,firm_id,kontragent_id,price,summa,nds_id, nomenklatura_id, 0 as kolvo, SUM(ou_kolvo) as ou_kolvo')
            //         ->whereNotNull('ou_date')
            //         ->groupBy('sklad_id','nomenklatura_id')
            //         ->getQuery()
            //     );

            // $query->fromSub($total->getQuery(),'sr_all')
            //     ->selectRaw('deleted_at,deleted_by,doc_date, ou_date, sklad_id,firm_id,kontragent_id,price,summa,nds_id, nomenklatura_id, SUM(kolvo) as kolvo, SUM(ou_kolvo) as ou_kolvo')
            //     ->groupBy('sklad_id','nomenklatura_id');


                // select
                //     deleted_at,
                //     doc_date,
                //     ou_date,
                //     sklad_id,
                //     firm_id,
                //     kontragent_id,
                //     price,
                //     summa,
                //     nds_id,
                //     nomenklatura_id,
                //     SUM(kolvo) as kolvo,
                //     SUM(ou_kolvo) as ou_kolvo
                // from (
                //     (
                //         select
                //             deleted_at,
                //             doc_date,
                //             ou_date,
                //             sklad_id,
                //             firm_id,
                //             kontragent_id,
                //             price,
                //             summa,
                //             nds_id,
                //             nomenklatura_id,
                //             SUM(kolvo) as kolvo,
                //             0 as ou_kolvo
                //         from `sklad_register`
                //             where (date(`doc_date`) <= 2021-05-12 or date(`ou_date`) <= 2021-05-12)
                //             and `sklad_register`.`deleted_at` is null
                //             and `ou_date` is null
                //         group by `sklad_id`, `nomenklatura_id`
                //     )
                //     union
                //     (
                //         select
                //             deleted_at,
                //             doc_date,
                //             ou_date,
                //             sklad_id,
                //             firm_id,
                //             kontragent_id,
                //             price,
                //             summa,
                //             nds_id,
                //             nomenklatura_id,
                //             0 as kolvo,
                //             SUM(ou_kolvo) as ou_kolvo
                //         from `sklad_register`
                //             where (date(`doc_date`) <= 2021-05-12 or date(`ou_date`) <= 2021-05-12)
                //             and `sklad_register`.`deleted_at` is null
                //             and `ou_date` is not null
                //         group by `sklad_id`, `nomenklatura_id`
                //     )
                // ) as `sr_all`
                //     where (date(`doc_date`) <= 2021-05-12 or date(`ou_date`) <= 2021-05-12)
                //     and `sklad_register`.`deleted_at` is null
                // group by `sklad_id`, `nomenklatura_id`;


                // $query->selectRaw('sklad_id, nomenklatura_id, SUM(kolvo) as kolvo, 0 as ou_kolvo')
                // ->whereNull('ou_date')
                // ->groupBy('sklad_id','nomenklatura_id')
                // ->union(
                //     $bu->selectRaw('sklad_id, nomenklatura_id, SUM(kolvo) as kolvo, 0 as ou_kolvo')
                //     ->whereNull('ou_date')
                //     ->groupBy('sklad_id','nomenklatura_id')
                //     ->getQuery()
                // )
                // ->union(
                //     $ou->selectRaw('sklad_id, nomenklatura_id, 0 as kolvo, SUM(ou_kolvo) as ou_kolvo')
                //     ->whereNotNull('ou_date')
                //     ->groupBy('sklad_id','nomenklatura_id')
                //     ->getQuery()
                // );

            // $query->select(['sklad_id','nomenklatura_id'])->selectRaw('SUM(kolvo) as kolvo')->whereNotNull('ou_date')->groupBy('sklad_id','nomenklatura_id');
        });
    }

    public function scopeResult($query) {

        $data = $query->get();
        $data = $data->filter(function($val){
            return floatVal($val->kolvo)!=0;
        });
        $data = $data->groupBy('sklad_id');
        $res = [];
        $data->each(function($items, $sklad_id) use(&$res){
            $sklad = Sklad::find($sklad_id);
            $sklad_item = $sklad->toArray();
            $sklad_item["items"] = $items->toArray();
            $res[] = $sklad_item;
        });
        return $res;
    }
}
