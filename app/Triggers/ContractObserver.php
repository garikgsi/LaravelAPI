<?php

namespace App\Triggers;

use App\Contract;

class ContractObserver
{
    public function creating(Contract $c)
    {
        // присвоим номер документа
        $new_doc_num = 1;
        $max_doc_num = Contract::whereYear('contract_date', date('Y'))->latest()->first();
        if ($max_doc_num) {
            $max = $max_doc_num->contract_num;
            $res = preg_match("/(\d+)/", $max, $matches);
            if ($res) {
                $n = $matches[1];
                $new_doc_num = (intval($n)) + 1;
            }
        }
        $c->contract_num = $new_doc_num;
    }
}