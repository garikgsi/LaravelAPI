<?php

namespace App;

use App\ABPTable;
use App\Common\ABPCache;

class SkladRegister extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('sklad_register');
        $this->table_type('register');
        // модель для наследования отчетами
        $this->model([
            ["name" => "doc_date", "type" => "date", "title" => "Дата проведения", "show_in_table" => true, "out_index" => 2],
            ["name" => "ou_date", "type" => "date", "title" => "Дата документа", "show_in_table" => true, "out_index" => 2],
            ["name" => "nomenklatura_id", "type" => "select", "table" => "nomenklatura", "table_class" => "Nomenklatura", "title" => "Номенклатура", "show_in_table" => true, "out_index" => 1],
            ["name" => "sklad_id", "type" => "select", "table" => "sklads", "table_class" => "Sklad", "title" => "Склад", "show_in_table" => true, "show_in_form" => true, "out_index" => 4],
            ["name" => "kontragent_id", "type" => "select", "table" => "kontragents", "table_class" => "Kontragent", "title" => "Поставщик", "show_in_table" => true, "out_index" => 5],
            ["name" => "firm_id", "type" => "select", "table" => "firms", "table_class" => "Firm", "title" => "Организация", "show_in_table" => true, "out_index" => 6],
            ["name" => "kolvo", "type" => "kolvo", "title" => "Количество", "show_in_table" => true, "out_index" => 3, "show_in_form" => true],
            ["name" => "price", "type" => "money", "title" => "Цена", "out_index" => 2, "show_in_form" => true],
            ["name" => "summa", "type" => "money", "title" => "Сумма", "show_in_table" => true, "out_index" => 4],
            ["name" => "nds_id", "type" => "select", "table" => "nds", "title" => "Ставка НДС", "show_in_table" => false, "out_index" => 5],
            ["name" => "registrable", "title" => "Документ", "type" => "morph", "out_index" => 1],
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['nomenklatura', 'sklad', 'kontragent', 'firm', 'registrable_title', 'ed_ism']);
    }

    public function registrable()
    {
        return $this->morphTo();
    }

    public function nomenklatura()
    {
        $this->belongsTo('App\Nomenklatura', 'nomenklatura_id');
    }

    // остаток номенклатуры по складу на дату
    public function ostatok($sklad_id, $nomenklatura_id, $date = 'now', $with_lock = 'false')
    {
        // $ost = $this->where('sklad_id',$sklad_id)
        //             ->where('nomenklatura_id',$nomenklatura_id)
        //             ->where(function ($query) use($date){
        //                 $query->whereDate('doc_date', '<=', $date=='now' ? date("Y-m-d") : $date )
        //                 ->orWhereDate('ou_date', '<=', $date=='now' ? date("Y-m-d") : $date );
        //             })
        //             ->sum('kolvo');
        $ost = $this->where('sklad_id', $sklad_id)
            ->where('nomenklatura_id', $nomenklatura_id)
            ->whereDate('ou_date', '<=', $date == 'now' ? date("Y-m-d") : $date);
        if ($with_lock) $ost->lockForUpdate();
        // $ost->dd();

        $ost = $ost->sum('ou_kolvo');
        // $ost = $this->where('sklad_id',$sklad_id)
        //             ->where('nomenklatura_id',$nomenklatura_id)
        //             ->whereDate('doc_date', '<=', $date=='now' ? date("Y-m-d") : $date )
        //             ->sum('kolvo');
        return $ost ? floatVal($ost) : 0;
    }

    // фильтр по складу
    public function scopeSklad_id($query, $sklad_id)
    {
        if (is_array($sklad_id)) {
            return $query->whereIn('sklad_id', $sklad_id);
        } else {
            return $query->where('sklad_id', $sklad_id);
        }
    }
    // фильтр по номенклатуре
    public function scopeNomenklatura_id($query, $nomenklatura_id)
    {
        if (is_array($nomenklatura_id)) {
            return $query->whereIn('nomenklatura_id', $nomenklatura_id);
        } else {
            return $query->where('nomenklatura_id', $nomenklatura_id);
        }
    }
    // фильтр по группам номенклатур
    public function scopeNomenklatura_groups($query, $nomenklatura_groups)
    {
        if (!is_array($nomenklatura_groups)) $nomenklatura_groups = [$nomenklatura_groups];
        // получим айдишники номенклатур соответствующих переданным группам
        $nomenklatura_id = Nomenklatura::select('id')->whereHas('groups', function ($query) use ($nomenklatura_groups) {
            $query->whereIn('tag_id', $nomenklatura_groups);
        })->get()->pluck('id')->all();
        // отфильтруем по указанным айдишникам
        return $query->whereIn('nomenklatura_id', $nomenklatura_id);
    }
    // фильтр по производителю номенклатур
    public function scopeManufacturer_id($query, $manufacturers)
    {
        if (!is_array($manufacturers)) $manufacturers = [$manufacturers];
        // dd($manufacturers);
        $nomenklatura_id = Nomenklatura::select('id')->whereIn('manufacturer_id', $manufacturers)->get()->pluck('id')->all();
        // отфильтруем по указанным айдишникам
        return $query->whereIn('nomenklatura_id', $nomenklatura_id);
    }
    // фильтр по дате (не более указанной или текущей)
    public function scopeDate($query, $date = 'now')
    {
        return $query->whereDate('doc_date', '<=', $date == 'now' ? date("Y-m-d") : $date);
    }
    // фильтр по дате (не более указанной или текущей)
    public function scopeOu_date($query, $date = 'now')
    {
        return $query->whereDate('ou_date', '<=', $date == 'now' ? date("Y-m-d") : $date);
    }
    // фильтр по фирме
    public function scopeFirm_id($query, $firm_id)
    {
        if (is_array($firm_id)) {
            return $query->whereIn('firm_id', $firm_id);
        } else {
            return $query->where('firm_id', $firm_id);
        }
    }
    // фильтр по контрагенту
    public function scopeKontragent_id($query, $kontragent_id)
    {
        if (is_array($kontragent_id)) {
            return $query->whereIn('kontragent_id', $kontragent_id);
        } else {
            return $query->where('kontragent_id', $kontragent_id);
        }
    }
    // только оприходованные
    public function scopeOnly_add($query)
    {
        return $query->where('saldo', 1);
    }
    // только отгруженные
    public function scopeOnly_diff($query)
    {
        return $query->where('saldo', 0);
    }
    // не учитывать перемещения
    public function scopeWithout_moves($query)
    {
        return $query->where('price', '>', 0);
    }
    // только оперативный учет
    public function scopeOu_only($query)
    {
        return $query->whereNotNull('ou_date');
    }
    // // суммарное количество
    // public function scopeKolvo($query) {
    //     return $query->sum('kolvo');
    // }
    // // среднее значение цены
    // public function scopeAvg($query) {
    //     return $query->where('price','>',0)->avg('price');
    // }
    // // сумма всех записей
    // public function scopeSum($query) {
    //     return $query->sum('summa');
    // }

    // читатели
    // выдаем номенклатуру
    public function getNomenklaturaAttribute()
    {
        if (isset($this->attributes['nomenklatura_id'])) {
            $value = $this->attributes['nomenklatura_id'];
            return ABPCache::get_select_list('nomenklatura', $value);
        } else {
            return '';
        }
    }
    // выдаем склад
    public function getSkladAttribute()
    {
        if (isset($this->attributes['sklad_id'])) {
            $value = $this->attributes['sklad_id'];
            return ABPCache::get_select_list('sklads', $value);
        } else {
            return '';
        }
    }
    // выдаем контрагента
    public function getKontragentAttribute()
    {
        if (isset($this->attributes['kontragent_id'])) {
            $value = $this->attributes['kontragent_id'];
            return ABPCache::get_select_list('kontragents', $value);
        } else {
            return '';
        }
    }
    // выдаем фирму
    public function getFirmAttribute()
    {
        if (isset($this->attributes['firm_id'])) {
            $value = $this->attributes['firm_id'];
            return ABPCache::get_select_list('firms', $value);
        } else {
            return '';
        }
    }
    // выдаем документ
    public function getRegistrableTitleAttribute()
    {
        if (isset($this->attributes['registrable_id'])) {
            switch ($this->attributes['registrable_type']) {
                case "App\SkladReceiveItem": {
                        return "Поступление";
                    }
                    break;
                case "App\SkladMoveItem": {
                        return "Перемещение";
                    }
                    break;
                case "App\SkladmanufactureItem": {
                        return "Производство";
                    }
                    break;
                case "App\Act": {
                        return "Реализация";
                    }
                    break;
            }
        } else {
            return '';
        }
    }
    // выдаем единицу измерения номенклатуры
    public function getEdIsmAttribute()
    {
        if (isset($this->attributes['nomenklatura_id'])) {
            $n = Nomenklatura::find($this->attributes['nomenklatura_id']);
            if ($n) {
                if ($ed_ism_id = $n->ed_ism_id) {
                    return ABPCache::get_select_list('ed_ism', $ed_ism_id);
                }
            }
        }
        return '';
    }
}