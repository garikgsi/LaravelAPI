<?php

namespace App\Triggers;

use App\InvoiceItem;
use App\NDS;

class InvoiceItemObserver
{
    public function saving(InvoiceItem $ii)
    {
        // обновим сумму НДС
        if ($ii->nds_id > 1) {
            $nds = NDS::find($ii->nds_id);
            if ($nds) {
                $ii->summa_nds = $nds->stavka * $ii->summa;
            }
        }
        // обновим наименование
        $nomenklatura = $ii->nomenklatura_()->first();
        if ($nomenklatura) {
            $ii->nomenklatura_name = $nomenklatura->doc_title;
        }
    }

    public function saved(InvoiceItem $ii)
    {
        // изменим сумму счета
        $this->up_sum_invoice($ii);
    }

    public function deleted(InvoiceItem $ii)
    {
        // изменим сумму счета
        $this->up_sum_invoice($ii);
    }

    private function up_sum_invoice(InvoiceItem $ii)
    {
        try {
            $i = $ii->invoice;
            $items = $i->items()->get();
            if ($items) {
                $i->summa = $items->sum('summa');
                $i->summa_nds = $items->sum('summa_nds');
                $i->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}