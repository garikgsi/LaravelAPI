<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sklad;
use App\SkladRegister;
use App\Production;
use App\Nomenklatura;
use App\ProductionItem;
use App\RecipeItem;
use App\SkladMove;



class TestCcontroller extends Controller
{

    private static $comment = '___abp-';


    public function test() {

        $move = SkladMove::find(1257);
        $total_items = $move->items()->dd();
        dd($total_items);

    // $remains = collect([]);

    // $productions = Production::where('comment', self::$comment)->where('is_active', '=', 1)->get();
    // foreach ($productions as $production) {
    //     // все произведенные изделия
    //     $items = $production->items()->get();
    //     // списываем все компоненты
    //     foreach ($items as $item) {
    //         $components = $item->components()->get();
    //         foreach ($components as $component) {
    //             $nomenklatura = Nomenklatura::find($component->nomenklatura_id);
    //             if ($nomenklatura->is_usluga == 0) $remains->push([
    //                 'document_type' => Production::class,
    //                 'document_id' => $production->id,
    //                 'document_date' => $production->doc_date,
    //                 'sklad_id' => $production->sklad_id,
    //                 'saldo' => 0,
    //                 'nomenklatura_id' => $component->nomenklatura_id,
    //                 'kolvo' => -$component->kolvo,
    //                 'price' => $component->price,
    //                 'summa' => $component->summa
    //             ]);
    //         }
    //         // если изделие собрано - оприходуем его
    //         if ($item->is_producted == 1) {
    //             $nomenklatura = $production->recipes->nomenklatura;
    //             $remains->push([
    //                 'document_type' => Production::class,
    //                 'document_id' => $production->id,
    //                 'document_date' => $production->doc_date,
    //                 'sklad_id' => $production->sklad_id,
    //                 'saldo' => 1,
    //                 'nomenklatura_id' => $nomenklatura->id,
    //                 'kolvo' => 1,
    //                 'price' => $nomenklatura->avg_price,
    //                 'summa' => $nomenklatura->avg_price
    //             ]);
    //         }
    //     }
    //     dd($remains->toArray());
    // }
    }
}