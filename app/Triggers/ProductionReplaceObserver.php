<?php

namespace App\Triggers;

use App\ProductionReplace;

class ProductionReplaceObserver {

    public function saved(ProductionReplace $pr) {
        // производство
        $p = $pr->production;
        if ($p) {
            // рецептура
            $recipe = $p->recipes()->first();
            if ($recipe) {
                // создаем или обновляем запись в заменах рецептур
                if ($pr->save_to_recipe == 1) {
                    $recipe_item = $recipe->items->where('nomenklatura_id',$pr->nomenklatura_from_id)->first();
                    if ($recipe_item) {
                        $replace_data = [
                            "kolvo_from"=>$pr->kolvo_from,
                            "kolvo_to"=>$pr->kolvo_to,
                            "nomenklatura_to_id"=>$pr->nomenklatura_to_id
                        ];
                        $recipe_item->replaces()->updateOrCreate(["recipe_item_id"=>$recipe_item->id,"nomenklatura_to_id"=>$pr->nomenklatura_to_id], $replace_data);
                    }
                }
            }
        }
    }

}

