<?php

namespace App\Triggers;

use App\Order;

class OrderObserver
{
    public function creating(Order $o)
    {
        // присвоим номер документа
        $new_doc_num = 1;
        $max_doc_num = Order::whereYear('doc_date', date('Y'))->latest()->first();
        if ($max_doc_num) {
            $max = $max_doc_num->doc_num;
            $res = preg_match("/(\d+)/", $max, $matches);
            if ($res) {
                $n = $matches[1];
                $new_doc_num = (intval($n)) + 1;
            }
        }
        $o->doc_num = $new_doc_num;
    }
}