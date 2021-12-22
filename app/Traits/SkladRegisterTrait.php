<?php

// регистры хранения
namespace App\Traits;

use App\SkladRegister;
use App\Kontragent;

trait SkladRegisterTrait
{

    // использовать оперативный учет
    protected $use_ou_for_sklad_register = true;

    // СВЯЗИ ТАБЛИЦ
    // связь с регистром хранения
    public function register()
    {
        return $this->morphMany('App\SkladRegister', 'registrable');
    }

    // МЕТОДЫ
    // getter есть регистр по указанному saldo
    public function has_register($saldo)
    {
        return $this->register()->where('saldo', $saldo)->count() > 0 ? true : false;
    }

    // getter проверка что кол-ва хватает на складе
    // чтобы списать $kolvo с учетом уже списанного кол-ва в регистрах
    public function check_ostatok($sklad_id, $nomenklatura_id, $kolvo)
    {
        // нужно учитывать остатки регистра по текущей строке
        $this_kolvo = floatVal($this->register()->sklad_id($sklad_id)->nomenklatura_id($nomenklatura_id)->get()->sum('kolvo'));
        // правильное кол-во = требуемое кол-во за вычетом того, что есть
        $real_kolvo = $kolvo - $this_kolvo;
        // если требуемое кол-во больше 0
        if ($real_kolvo > 0) {
            $ostatok = $this->get_ostatok($sklad_id, $nomenklatura_id);
            $delta = $ostatok - $real_kolvo;
        } else {
            $delta = $real_kolvo;
        }
        return $delta;
    }

    // getter проверка что при удалении регистров остатки будут положительные
    public function check_del_register()
    {
        $delta = 0;
        // проверим, есть ли регистр
        if ($this->has_register(1)) {
            $register = $this->register()->where('saldo', 1)->first();
            $register_kolvo = floatVal($register->kolvo);
            $ostatok = $this->get_ostatok($register->sklad_id, $register->nomenklatura_id);
            $delta = $ostatok - $register_kolvo;
        }
        return $delta;
    }


    // getter остаток номенклатуры на складе
    public function get_ostatok($sklad_id, $nomenklatura_id)
    {
        $register  = new SkladRegister;
        // \DB::connection('db1')->enableQueryLog();
        return $register->ostatok($sklad_id, $nomenklatura_id);
        // var_dump(\DB::connection('db1')->getQueryLog());
    }

    // setter изменяем регистры с проверкой учитывая приход/расход ($in_out) или выполняем проверку наличия
    // $mode может принимать значения : null - только проверка (по умолчанию), delete - удаление регистров,
    // update - обновление регистров, update_only - обновление без проверок, delete_only - удаление без проверок
    // check_for_delete - выполнить проверки для удаления регистров
    public function mod_register($in_out, $mode = null)
    {
        // строка документа
        $doc_item = $this;
        // типы документов
        $ReceiveClass = 'App\SkladReceiveItem';
        $ProductionItemClass = 'App\ProductionItem';
        $ProductionComponentClass = 'App\ProductionComponent';
        $MoveClass = 'App\SkladMoveItem';
        $ActClass = 'App\ActItem';

        // ПОЛУЧАЕМ НЕОБХОДИМЫЕ ПЕРЕМЕННЫЕ ДЛЯ РЕГИСТРА В ЗАВИСИМОСТИ ОТ ДОКУМЕНТА
        // если это продажа
        if ($doc_item instanceof $ActClass) {
            // документ
            $doc = $doc_item->act;
            if ($doc) {
                // признак при котором должны быть регистры $is_active == true
                $is_active = $doc->is_active == 1 ? true : false;
                // переменные для регистра и вывода ошибок
                $sklad_id = $doc->sklad_id;
                $sklad_title = $doc->sklad;
                $kolvo = floatVal($doc_item->kolvo);
                $nomenklatura_id = $doc_item->nomenklatura_id;
                $doc_date = $doc->doc_date;
                // организация
                try {
                    $firm_id = $doc->order_()->first()->contract_()->first()->firm_()->first()->id;
                } catch (\Throwable $th) {
                    $firm_id = 1;
                }
                // контрагент
                try {
                    $kontragent = $doc->order_()->first()->contract_()->first()->contractable;
                    if ($kontragent instanceof Kontragent) {
                        $kontragent_id = $kontragent->id;
                    }
                } catch (\Throwable $th) {
                    $kontragent_id = 1;
                }
                $nomenklatura_title = $doc_item->nomenklatura;
                $ed_ism_title = $doc_item->ed_ism;
                $price = $doc_item->price;
                $nds_id = $doc_item->nds_id;
                $nomenklatura = $doc_item->nomenklatura_()->first();
            }
        }
        // если это компоненты произведенных изделий
        if ($doc_item instanceof $ProductionComponentClass) {
            // документ
            $doc = $doc_item->production;
            if ($doc) {
                // признак при котором должны быть регистры $is_active == true
                $is_active = $doc->is_active == 1 ? true : false;
                // переменные для регистра и вывода ошибок
                $sklad_id = $doc->sklad_id;
                $sklad_title = $doc->sklad;
                $kolvo = floatVal($doc_item->kolvo);
                $nomenklatura_id = $doc_item->nomenklatura_id;
                $doc_date = $doc->doc_date;
                $firm_id = 1;
                $kontragent_id = 1;
                $nomenklatura_title = $doc_item->nomenklatura;
                $ed_ism_title = $doc_item->ed_ism;
                $price = 0;
                $nds_id = 1;
                $nomenklatura = $doc_item->component;
            }
        }
        // если это произведенные изделия
        if ($doc_item instanceof $ProductionItemClass) {
            // документ
            $doc = $doc_item->production;
            if ($doc) {
                // признак при котором должны быть регистры $is_active == true
                $is_active = $doc_item->is_producted == 1 ? true : false;
                // переменные для регистра и вывода ошибок
                $sklad_id = $doc->sklad_id;
                $sklad_title = $doc->sklad;
                $kolvo = floatVal($doc_item->kolvo);
                $nomenklatura_id = $doc->nomenklatura_id;
                $doc_date = $doc->doc_date;
                $firm_id = 1;
                $kontragent_id = 1;
                $nomenklatura_title = $doc->nomenklatura;
                $ed_ism_title = $doc->ed_ism;
                $price = $doc_item->self_price;
                $nds_id = 1;
                $nomenklatura = $doc->product();
            }
        }
        // если это поступление
        if ($doc_item instanceof $ReceiveClass) {
            // документ
            $doc = $doc_item->sklad_receive;
            if ($doc) {
                // признак при котором должны быть регистры $is_active == true
                $is_active = $doc->is_active == 1 ? true : false;
                // переменные для регистра и вывода ошибок
                $sklad_id = $doc->sklad_id;
                $sklad_title = $doc->sklad;
                $kolvo = floatVal($doc_item->kolvo);
                $nomenklatura_id = $doc_item->nomenklatura_id;
                $doc_date = $doc->doc_date;
                $firm_id = $doc->firm_id;
                $kontragent_id = $doc->kontragent_id;
                $nomenklatura_title = $doc_item->nomenklatura;
                $ed_ism_title = $doc_item->ed_ism;
                $price = $doc_item->price;
                $nds_id = $doc_item->nds_id;
                $nomenklatura = $doc_item->nomenklatura_()->first();
            }
        }
        // если это перемещение
        if ($doc_item instanceof $MoveClass) {
            // документ
            $doc = $doc_item->sklad_move;
            if ($doc) {
                // признак при котором должны быть регистры $is_active == true
                // если поступление
                if ($in_out == 1) {
                    $is_active = $doc->is_in == 1 || $doc->is_active == 1 ? true : false;
                }
                // если расход
                if ($in_out == 0) {
                    $is_active = $doc->is_out == 1 || $doc->is_active == 1 ? true : false;
                }
                // переменные для регистра и вывода ошибок
                $sklad_id = $in_out == 1 ? $doc->sklad_in_id : $doc->sklad_out_id;
                $sklad_title = $in_out == 1 ? $doc->sklad_in : $doc->sklad_out;
                $kolvo = floatVal($doc_item->kolvo);
                $nomenklatura_id = $doc_item->nomenklatura_id;
                $doc_date = $doc->doc_date;
                $firm_id = 1;
                $kontragent_id = 1;
                $nomenklatura_title = $doc_item->nomenklatura;
                $ed_ism_title = $doc_item->ed_ism;
                $price = 0;
                $nds_id = 1;
                $nomenklatura = $doc_item->nomenklatura_()->first();
            }
        }

        if (isset($nomenklatura)) {
            // услуги не проверяем и не регистрируем
            $is_usluga = $nomenklatura->is_usluga == 1 ? true : false;
            // если не услуга
            if (!$is_usluga) {
                // только если идентифицирован документ
                if (isset($doc)) {

                    // моды при которых проверки не проводятся
                    $uncheck_modes = ['update_only', 'delete_only'];
                    $delete_checks_modes = ['check_for_delete'];
                    // получим регистр по этой строке с таким saldo
                    $existed_register = $doc_item->register()->where('saldo', $in_out);
                    $reg_exist = $existed_register->count() > 0 ? true : false;
                    // данные регистра
                    if ($reg_exist) {
                        $e_register = $existed_register->first();
                        $e_kolvo = floatVal(abs($e_register->ou_kolvo));
                        $e_nomenklatura_id = $e_register->nomenklatura_id;
                        $e_sklad_id = $e_register->sklad_id;
                    }
                    // если нужно проверять
                    if (!in_array($mode, $uncheck_modes)) {
                        // это проверка для удаления?
                        $is_delete_check = in_array($mode, $delete_checks_modes);
                        // если это поступление - нужно проверить не отрицательные ли будут остатки, если
                        // уже есть регистр с таим saldo, в случае если изменилась номенклатура или количество
                        // а также если документ распроведен или проверка на удаление
                        if ($in_out == 1) {
                            if ($reg_exist) {
                                // если документ проведен
                                if ($is_active && !$is_delete_check) {
                                    // если номенклатура и склад совпадают и новое значение меньше оприходованного - проверяем только разницу
                                    // между старым и новым значением
                                    if ($e_sklad_id == $sklad_id && $e_nomenklatura_id == $nomenklatura_id) {
                                        if ($e_kolvo > $kolvo) {
                                            // проверяем только разницу между оприходованным и переданным кол-вом
                                            $check_kolvo = $e_kolvo - $kolvo;
                                        } else {
                                            // списываем меньше, чем есть в регистре - остаток просто вернется на склад
                                        }
                                    } else {
                                        // проверяем все есть в регистре
                                        $check_kolvo = $e_kolvo;
                                    }
                                } else {
                                    // проверяем все что есть в регистре
                                    $check_kolvo = $e_kolvo;
                                }
                                // если надо проверять
                                if (isset($check_kolvo)) {
                                    // проверяем остаток
                                    $ostatok = $this->get_ostatok($e_sklad_id, $e_nomenklatura_id);
                                    // dd($check_kolvo);
                                    $delta = $ostatok - $check_kolvo;
                                    if ($delta < 0) {
                                        // ошибка - не хватает после изменения
                                        $error = "После сделанных изменений на " . $e_register->sklad . " не будет хватать " . $e_register->nomenklatura . " в количестве " . abs($delta) . " " . $e_register->ed_ism;
                                    }
                                }
                            } else {
                                // регистра нет и приход = больше нет проверок
                                // if ($in_out == 1 && $kolvo > 2)  dd("no checks");
                            }
                        }
                        // если расход
                        if ($in_out == 0) {
                            // var_dump($existed_register->first()->toArray());
                            // по умолчанию проверяем остатки для проведенных или неудаляемых записей
                            if ($is_delete_check || !$is_active) {
                                $dont_check = true;
                            } else {
                                $dont_check = false;
                            }

                            // регистр есть
                            if ($reg_exist) {
                                // если значения склада, номенклатуры и кол-ва в регистре и строке записи различаются
                                if ($e_nomenklatura_id != $nomenklatura_id || $e_kolvo != $kolvo || $e_sklad_id != $sklad_id) {
                                    // если номенклатура и склад совпадают
                                    if ($e_sklad_id == $sklad_id && $e_nomenklatura_id == $nomenklatura_id) {
                                        // var_dump($kolvo . "-" . $e_kolvo);
                                        // если новое значение меньше оприходованного - проверяем только разницу между новым и старым значением
                                        if ($e_kolvo < $kolvo) {
                                            $check_kolvo = $kolvo - $e_kolvo;
                                        } else {
                                            // проверять остатки не нужно - по регистру все совпадает, кроме количества, которое меньше,
                                            // чем уже списано по регистру
                                            $dont_check = true;
                                        }
                                    } else {
                                        // склад или номенклатура отличаются от учтенного в регистре - проверяем все
                                        $check_kolvo = $kolvo;
                                    }
                                } else {
                                    // var_dump('no changes');
                                    // проверять остатки не нужно - по регистру все совпадает
                                    $dont_check = true;
                                }
                            } else {
                                // регистра нет
                                // проверяем все новые значения
                                $check_kolvo = $kolvo;
                            }
                            // проверяем остатки, если необходимо
                            if (!$dont_check) {
                                $ostatok = $this->get_ostatok($sklad_id, $nomenklatura_id);
                                $delta = $ostatok - $check_kolvo;
                                // dd($ostatok, $delta, $check_kolvo, $sklad_id);
                                if ($delta < 0) {
                                    // ошибка - не хватает для списания
                                    $error = "Для списания " . $nomenklatura_title . " на " . $sklad_title . " не хватает " . abs($delta) . " " . $ed_ism_title;
                                }
                            }
                        }
                    }
                    // если нет ошибок - проверим, может нужно вносить изменения в регистры
                    // var_dump(isset($error));
                    if (!isset($error)) {
                        // если передан валидный $mode на изменение регистра
                        switch ($mode) {
                            case 'update':
                            case 'update_only': {
                                    // если документ проведен - обновляем регистры
                                    if ($is_active) {
                                        $register_data = [
                                            "doc_date" => $doc_date,
                                            "nomenklatura_id" => $nomenklatura_id,
                                            "firm_id" => $firm_id,
                                            "kontragent_id" => $kontragent_id,
                                            "nds_id" => $nds_id,
                                            "saldo" => $in_out,
                                            "kolvo" => $in_out == 1 ? $kolvo : -$kolvo,
                                            "ou_kolvo" => $in_out == 1 ? $kolvo : -$kolvo,
                                            "sklad_id" => $sklad_id
                                        ];
                                        if (isset($price)) {
                                            $register_data["price"] = $price;
                                            $register_data["summa"] = $price * $kolvo;
                                        }
                                        // если регистр уже существует
                                        // if ($in_out == 1 && $kolvo > 2) dd($register_data);
                                        if ($reg_exist) {
                                            $res = $e_register->update($register_data);
                                        } else {
                                            $register_data["ou_date"] = date("Y-m-d");
                                            $sklad_register_model = new SkladRegister;
                                            $sklad_register_item = $sklad_register_model->fill($register_data);
                                            $res = $doc_item->register()->save($sklad_register_item);
                                        }
                                        // если не обновлен регистр
                                        if (!$res) $error = "Не удалось изменить остатки на " . $sklad_title . " при обновлении " . $nomenklatura_title;
                                    } else {
                                        // документ не проведен - удаляем регистры
                                        if ($reg_exist) {
                                            // dd($existed_register->count());
                                            $res = $existed_register->forceDelete();
                                            if (!$res) {
                                                $error = "Не удалось изменить остатки на " . $sklad_title . " при удалении " . $nomenklatura_title;
                                            }
                                        }
                                    }
                                }
                                break;
                            case 'delete':
                            case 'delete_only': {
                                    if ($reg_exist) {
                                        $res = $existed_register->forceDelete();
                                        if (!$res) {
                                            $error = "Не удалось изменить остатки на " . $sklad_title . " при удалении " . $nomenklatura_title;
                                        }
                                    }
                                }
                                break;
                        }
                    }
                } else {
                    // документ не получен или не идентифицирован
                    // $error = "документ не получен или не идентифицирован";
                }
            }
        }
        return [
            "is_error" => isset($error) ? true : false,
            "err" => isset($error) ? $error : null
        ];
    }
}