<?php

namespace App\Triggers;

use App\Sotrudnik;
use App\FirmPosition;
use Illuminate\Support\Facades\DB;



class SotrudnikObserver
{
    public function saving(Sotrudnik $s)
    {
        // если передан текст должности - добавим ее в список должностей и укажем ее в firm_position_id
        if (isset($s->firm_position_text) && strlen($s->firm_position_text) > 0) {
            $fp = mb_strtoupper(mb_substr($s->firm_position_text, 0, 1)) . mb_substr(mb_strtolower($s->firm_position_text), 1);
            $same_firm_positions = FirmPosition::where(DB::raw('lower(name)'), '=', mb_strtolower($fp));
            if ($same_firm_positions->count() > 0) {
                $firm_position = $same_firm_positions->first();
            } else {
                $firm_position = new FirmPosition;
                $firm_position->name = $fp;
                $firm_position->save();
            }
            if ($firm_position) {
                $s->firm_position_id = $firm_position->id;
                $s->firm_position_text = '';
            }
        }
    }
}