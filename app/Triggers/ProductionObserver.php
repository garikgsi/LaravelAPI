<?php

namespace App\Triggers;

use App\Production;
use App\ProductionItem;
use App\ProductionComponent;
use App\SkladRegister;
use Illuminate\Support\Facades\Auth;
use App\Nomenklatura;


class ProductionObserver
{

    // производство
    private $p = NULL;
    // готовые изделия
    private $pi;
    // номенклатура изделия
    private $nomenklatura;
    // тайтл номенклатуры изделия
    private $nomenklatura_title;
    // тайтл единицы измерения изделия
    private $ed_ism_title;
    // тайтл склада
    private $sklad;
    // тайтл склада
    private $sklad_title;
    // сотрудник
    private $sotrudnik;
    // сотрудник == кладовщик
    private $is_keeper = false;
    // сотрудник == администратор
    private $is_admin = false;
    // значения до изменения
    private $old = [];
    // измененные значения
    private $new = [];


    public function creating(Production $p)
    {
        // присвоим номер документа
        $new_doc_num = 1;
        $max_doc_num = Production::whereYear('doc_date', date('Y'))->latest()->first();
        if ($max_doc_num) {
            $max = $max_doc_num->doc_num;
            $res = preg_match("/(\d+)/", $max, $matches);
            if ($res) {
                $n = $matches[1];
                $new_doc_num = $n + 1;
            }
        } else {
            $new_doc_num = 1;
        }
        $p->doc_num = $new_doc_num;
        // занесем членов комиссии
        $sklad = $p->sklad_()->first();
        $p->commission_member1 = $sklad->commission_member1;
        $p->commission_member2 = $sklad->commission_member2;
        $p->commission_chairman = $sklad->commission_chairman;
    }

    public function created(Production $p)
    {
        // если это не копирование - добавим автоматом произведенные изделия
        if ($p->is_copied != 1) {
            // добавим в производство изделия
            for ($i = 0; $i < $p->kolvo; $i++) {
                $this->add_item($p);
            }
        }
    }

    public function updating(Production $p)
    {
        $this->set_vars($p);

        // изменяется количество
        if ($p->isDirty('kolvo')) {
            $old_kolvo = intval($p->getOriginal('kolvo'));
            $new_kolvo = intval($p->kolvo);
            if ($old_kolvo != $new_kolvo) {
                // если проведено - сначало надо распровести
                if ($p->getOriginal('is_active') == 1) {
                    abort(421, '#PO.Для изменения количества необходимо сначала распровести производство');
                    return false;
                } else {
                    // на сколько увеличивается кол-во
                    $delta = intVal($p->kolvo - $p->getOriginal('kolvo'));
                    // если кол-во увеличивается
                    if ($delta > 0) {
                        for ($i = 0; $i < $delta; $i++) {
                            $this->add_item($p);
                        }
                    } else {
                        abort(421, '#PO.Уменьшение количества готовых изделий в партии невозможно');
                        return false;
                    }
                }
            }
        }

        // изменяется рецептура
        if ($p->isDirty('recipe_id')) {
            if ($p->getOriginal('is_active') == 1) {
                abort(421, '#PO.Для изменения рецептуры необходимо сначала распровести производство');
                return false;
            }
        }

        // изменяется склад
        if ($p->isDirty('sklad_id')) {
            if ($p->getOriginal('is_active') == 1) {
                abort(421, '#PO.Для изменения склада необходимо сначала распровести производство');
                return false;
            }
        }

        // если распроводим документ
        if ($p->isDirty('is_active') && $p->is_active == 0) {
            $check = $this->check_unactive($p, "Распровести");
            if (!$check["can"]) {
                abort(421, "#PO." . implode(", ", $check["err"]));
                return false;
            }
        }

        // если проводим документ
        if ($p->isDirty('is_active') && $p->is_active == 1) {
            // распроводить по складам могут только кладовщики
            if ($this->is_keeper || $this->is_admin) {
                // нехватка количества
                $deficit = [];
                // цикл по кол-ву изготавливаемых изделий
                $items = $p->items;
                foreach ($items as $item) {
                    // себестоимость изделия
                    $prime_cost = 0;
                    // получим список компонентов
                    $components = $item->components;
                    foreach ($components as $component) {
                        // осталось списать $kolvo
                        $kolvo = floatVal($component->kolvo);
                        // уже проверенная номенклатура
                        $checked_ids = [];
                        // все замены компонента
                        $replacements = [];
                        // номенклатуры, которые уже вошли в замены
                        $replacement_ids = [];
                        // пытаемся найти замены для компонента
                        $component_replacements = $component->replaces;
                        // если есть замены на уровне компонента
                        // var_dump($component_replacements);
                        // if ($component_replacements->count() > 0) dd($component_replacements);
                        if ($component_replacements) {
                            foreach ($component_replacements as $cr) {
                                if (!in_array($cr->nomenklatura_to_id, $replacement_ids)) {
                                    $replacements[] = ["nomenklatura_id" => $cr->nomenklatura_to_id, "kolvo_from" => $cr->kolvo_from, "kolvo_to" => $cr->kolvo_to];
                                    $replacement_ids[] = $cr->nomenklatura_to_id;
                                }
                            }
                        }
                        // если есть замены на уровне производства
                        $production_replacements = $p->replaces->where('component_id', 1)->where('nomenklatura_from_id', $component->nomenklatura_id);
                        if ($production_replacements) {
                            foreach ($production_replacements as $pr) {
                                if (!in_array($pr->nomenklatura_to_id, $replacement_ids)) {
                                    $replacements[] = ["nomenklatura_id" => $pr->nomenklatura_to_id, "kolvo_from" => $pr->kolvo_from, "kolvo_to" => $pr->kolvo_to];
                                    $replacement_ids[] = $pr->nomenklatura_to_id;
                                }
                            }
                        }
                        // замены на уровне рецептур
                        $recipe_replacements = $p->recipes->items()->whereHas('replaces', function ($query) use ($component) {
                            $query->where('nomenklatura_id', $component->nomenklatura_id);
                        });
                        if ($recipe_replacements) {
                            foreach ($recipe_replacements as $rr) {
                                if (!in_array($rr->nomenklatura_to_id, $replacement_ids)) {
                                    $replacements[] = ["nomenklatura_id" => $rr->nomenklatura_to_id, "kolvo_from" => $rr->kolvo_from, "kolvo_to" => $rr->kolvo_to];
                                    $replacement_ids[] = $rr->nomenklatura_to_id;
                                }
                            }
                        }
                        // if (count($replacements)>0) var_dump($replacements);
                        $n = Nomenklatura::find($component->nomenklatura_id);
                        if ($n) {
                            // если услуга - остатки не проверяем, только суммируем себестоимость
                            if ($n->is_usluga != 1) {
                                // создаем экземпляр регистра остатков
                                $register  = new SkladRegister;
                                // данные регистра без учета номенклатуры списания
                                $register_data = [
                                    "doc_date" => $p->doc_date,
                                    "ou_date" => date("Y-m-d"),
                                    "sklad_id" => $p->sklad_id,
                                    "firm_id" => 1,
                                    "kontragent_id" => 1,
                                    "price" => 0,
                                    "summa" => 0,
                                    "nds_id" => 1,
                                    "saldo" => 0
                                ];
                                // проверим остаток на складе
                                if (!in_array($component->nomenklatura_id, $checked_ids)) {
                                    $register_data_balance = $register->sklad_id($p->sklad_id)->nomenklatura_id($component->nomenklatura_id)->ou_date()->lockForUpdate();
                                    if ($register_data_balance) {
                                        $ostatok = floatVal($register_data_balance->get()->sum('kolvo'));
                                        // если такая номенклатура есть на складе
                                        if ($ostatok > 0) {
                                            // если остаток меньше необходимого кол-ва - списываем сколько есть
                                            if ($ostatok < $kolvo) {
                                                // изменяем кол-во компонента
                                                $component->fill(["kolvo" => $ostatok])->save();
                                                // записываем регистр
                                                $register_data += [
                                                    "nomenklatura_id" => $component->nomenklatura_id,
                                                    "kolvo" => -$ostatok,
                                                    "ou_kolvo" => -$ostatok,
                                                ];
                                            } else {
                                                // списываем все кол-во
                                                $register_data += [
                                                    "nomenklatura_id" => $component->nomenklatura_id,
                                                    "kolvo" => -$kolvo,
                                                    "ou_kolvo" => -$kolvo,
                                                ];
                                            }
                                            // сохраняем в регистр
                                            $reg_item = $component->register()->first();
                                            if ($reg_item) {
                                                $reg = $reg_item->update($register_data);
                                            } else {
                                                $sklad_register = new SkladRegister;
                                                $reg = $component->register()->save($sklad_register->fill($register_data));
                                            }
                                            // $reg = $component->register()->updateOrCreate(["saldo"=>$register_data["saldo"], "nomenklatura_id"=>$register_data["nomenklatura_id"]],$register_data);
                                            if ($reg) {
                                                // изменяем кол-во
                                                $kolvo -= abs($register_data["kolvo"]);
                                                // добавляем в массив проверенных
                                                $checked_ids[] = $component->nomenklatura_id;
                                                // добавляем к себестоимости
                                                $prime_cost += abs($register_data["kolvo"]) * floatVal($n->avg_price);
                                            }
                                        } else {
                                            // удаляем запись из списка компонентов
                                            $component->fill(["kolvo" => 0])->save();
                                        }
                                    } else {
                                        $component->fill(["kolvo" => 0])->save();
                                    }
                                }
                                // если есть замены
                                if (count($replacements) > 0) {
                                    // пройдем по всем заменам, пока есть что списывать
                                    foreach ($replacements as $replace) {
                                        // если еще есть что списывать
                                        if ($kolvo > 0) {
                                            // если мы еще не искали остатки по этой номенклатуре
                                            if (!in_array($replace["nomenklatura_id"], $checked_ids)) {
                                                // номенклатура замены
                                                $replacement_nomenklatura_id = Nomenklatura::find($replace['nomenklatura_id']);
                                                // коэффициент замены
                                                $ratio = $replace['kolvo_to'] / $replace['kolvo_from'];
                                                // необходимое кол-во с учетом коэффициента
                                                $ratio_kolvo = $kolvo * $ratio;
                                                // если услуга - не проверяем наличие
                                                if ($replacement_nomenklatura_id->is_usluga != 1) {
                                                    // ищем остаток номенклатуры
                                                    $register_data_balance = $register->sklad_id($p->sklad_id)->nomenklatura_id($replace['nomenklatura_id'])->date($p->doc_date)->lockForUpdate();
                                                    if ($register_data_balance) {
                                                        $ostatok = floatVal($register_data_balance->get()->sum('kolvo'));
                                                        // если такая номенклатура есть на складе
                                                        if ($ostatok > 0) {
                                                            // необходимое кол-во с учетом коэффициента
                                                            $ratio_kolvo = $kolvo * $ratio;
                                                            // если остаток меньше необходимого кол-ва - списываем сколько есть
                                                            if ($ostatok < $ratio_kolvo) {
                                                                $register_data["nomenklatura_id"] = $replace['nomenklatura_id'];
                                                                $register_data["kolvo"] = -$ostatok;
                                                                $register_data["ou_kolvo"] = -$ratio_kolvo;
                                                            } else {
                                                                // списываем все кол-во
                                                                $register_data["nomenklatura_id"] = $replace['nomenklatura_id'];
                                                                $register_data["kolvo"] = -$ratio_kolvo;
                                                                $register_data["ou_kolvo"] = -$ratio_kolvo;
                                                            }
                                                            // добавляем в список компонентов
                                                            $new_production_component = $component->replicate();
                                                            $new_production_component->fill([
                                                                "nomenklatura_id" => $replace['nomenklatura_id'],
                                                                "kolvo" => abs($register_data["kolvo"]),
                                                                "price" => 0,
                                                                "summa" => 0,
                                                                "is_replaced" => 1
                                                            ])->save();
                                                            // сохраняем в регистр
                                                            $sklad_register = new SkladRegister;
                                                            $reg = $new_production_component->register()->save($sklad_register->fill($register_data));
                                                            if ($reg) {
                                                                // изменяем кол-во
                                                                $kolvo -= abs($register_data["kolvo"]) / $ratio;
                                                                // добавляем в массив проверенных
                                                                $checked_ids[] = $replace['nomenklatura_id'];
                                                                // добавляем к себестоимости
                                                                $prime_cost += abs($register_data["kolvo"]) * floatVal($replacement_nomenklatura_id->avg_price);
                                                            } else {
                                                                abort(421, "#PO.Не записан регистр " . $register_data["nomenklatura_id"]);
                                                            }
                                                            unset($new_production_component);
                                                        }
                                                    }
                                                } else {
                                                    // добавим к себестоимости
                                                    $prime_cost += abs($ratio_kolvo) * floatVal($replacement_nomenklatura_id->avg_price);
                                                    // просто добавим в компоненты
                                                    $new_production_component = $component->replicate();
                                                    $new_production_component->fill([
                                                        "nomenklatura_id" => $replace['nomenklatura_id'],
                                                        "kolvo" => abs($ratio_kolvo),
                                                        "price" => 0,
                                                        "summa" => 0
                                                    ])->save();
                                                    unset($new_production_component);
                                                    // ошибок по остаткам нет
                                                    $kolvo = 0;
                                                }
                                            }
                                        }
                                    }
                                }
                                // если осталось несписанное количество
                                if ($kolvo > 0) {
                                    $deficit[$component->nomenklatura_id] = (isset($deficit[$component->nomenklatura_id]) ? floatVal($deficit[$component->nomenklatura_id]) : 0) + $kolvo;
                                }
                            } else {
                                $prime_cost += $kolvo * floatVal($n->avg_price);
                            }
                        } else {
                            // если не нашли компонент в таблице номенклатур - ошибка
                            abort(421, "#PO.Номенклатуры " . $component->nomenklatura_id . " не найдено");
                        }
                    }
                    // все компоненты проведены без ошибок - добавляем изделие в регистр
                    if (count($deficit) === 0) {
                        // все компоненты списаны - добавляем продукцию на склад
                        $register_data = [
                            "doc_date" => $p->doc_date,
                            "nomenklatura_id" => $p->product()->id,
                            "sklad_id" => $p->sklad_id,
                            "firm_id" => 1,
                            "kontragent_id" => 1,
                            "kolvo" => 1,
                            "price" => $prime_cost,
                            "summa" => $prime_cost,
                            "nds_id" => 1,
                            "saldo" => 1
                        ];
                        // если установлена галочка is_producted - добавим дату в оперативный учет
                        if ($item->is_producted == 1) {
                            $register_data["ou_date"] = date("Y-m-d");
                            $register_data["ou_kolvo"] = 1;
                        }
                        // добавляем или обновляем регистр
                        $reg_item = $item->register()->first();
                        if ($reg_item) {
                            $reg = $reg_item->update($register_data);
                        } else {
                            $sklad_register_model = new SkladRegister;
                            $sklad_register_item = $sklad_register_model->fill($register_data);
                            $reg = $item->register()->save($sklad_register_item);
                        }
                        if (!$reg) {
                            abort(421, '#PO.Произведенное изделие с Инв.№' . $item->serial . ' не добавлено в регистр');
                            return false;
                        }
                    }
                }
                // если ошибок не было
                if (count($deficit) > 0) {
                    $remains_errors = [];
                    foreach ($deficit as $nomenklatura_id => $kolvo) {
                        $n = Nomenklatura::find($nomenklatura_id);
                        $remains_errors[] = $n->select_list_title . " в количестве " . $kolvo . " " . $n->edIsm;
                    }
                    abort(421, "#PO. Недостаточно: " . implode(', ', $remains_errors));
                    return false;
                }
            } else {
                abort(421, '#PO.Проводить можно только кладовщику или администратору');
                return false;
            }
        }
    }

    public function saved(Production $p)
    {
        // если изменился признак проведения - изменим его у каждого изделия
        if ($p->isDirty('is_active')) {
            $items = $p->items;
            foreach ($items as $item) {
                if ($p->is_active == 0) {
                    $item->fill(['is_producted' => 0, 'is_active' => 0])->save();
                } else {
                    $item->fill(['is_active' => 1])->save();
                }
            }
        } elseif ($p->isDirty('sklad_id') || $p->isDirty('doc_date') || $p->isDirty('recipe_id') || ($p->isDirty('kolvo') && floatval($p->getOriginal(('kolvo')) != floatval($p->kolvo)))) {
            // dd($p->getChanges());
            // если изменились поля, влияющие на регистры - обновим подчиненную таблицу
            $items = $p->items;
            foreach ($items as $item) {
                // просто обновляем готовые изделия
                $item->touch();
            }
        }
    }

    // проверки при удалении
    public function deleting(Production $p)
    {
        $this->set_vars($p);

        // проверяем можно ли распровести оприходованные изделия
        $check = $this->check_unactive($p);
        if (!$check["can"]) {
            abort(421, "#PO." . implode(", ", $check["err"]));
            return false;
        }
    }

    // удаление
    public function deleted(Production $p)
    {
        // удалим все готовые изделия с компонентами
        $items = $p->items;
        foreach ($items as $item) {
            $res = $item->delete();
            if (!$res) {
                abort(421, "#PO.Ошибка при удалении произведенной продукции #" . $item->id);
                return false;
            }
        }
    }


    // служебные методы
    // добавление готового изделия в партию
    protected function add_item(Production $p)
    {
        // модель и данные готового изделия
        $pi_model = new ProductionItem;
        $pi_data = [
            "kolvo" => 1
        ];
        $production_item = $p->items()->save($pi_model->fill($pi_data));
        // рецептура
        $recipe = $p->recipes;
        // позиции рецептуры
        $recipe_items = $recipe->items;
        // добавим компоненты из рецепта
        if ($recipe_items) {
            foreach ($recipe_items as $item) {
                $pc = new ProductionComponent;
                $component_data = [
                    "nomenklatura_id" => $item->nomenklatura_id,
                    "kolvo" => $item->kolvo,
                ];
                $production_item->components()->save($pc->fill($component_data));
                unset($pc);
            }
        }
    }

    // проверим возможность распроведения документа
    protected function check_unactive(Production $p, $err_prefix = "Удалить")
    {
        $this->set_vars($p);

        $err = [];

        // кол-во готовых изделий которые изготовили (оприходовали на склад)
        $del_kolvo = $p->items()->where('is_producted', 1)->count();

        // проверим права
        if (($p->is_active == 1 || $del_kolvo > 0) && !$this->is_keeper && !$this->is_admin) {
            if ($p->is_active == 1) {
                $text = "проведенный документ";
            }
            if ($del_kolvo > 0) {
                $text = 'производство с оприходованной продукцией';
            }
            $err[] = $err_prefix . ' ' . $text . ' может только кладовщик или администратор';
        }

        // проверим возможность удаления компонентов
        // если такие изделия есть - проверяем
        if ($del_kolvo > 0) {
            // получим остаток на складе
            $item1 = $p->items->first();
            $ostatok = $item1->get_ostatok($p->sklad_id, $p->nomenklatura_id);
            // дефицит
            $delta = $ostatok - $del_kolvo;
            // если дефицит есть - ошибка
            if ($delta < 0) {
                $err[] = "Дефицит " . $p->nomenklatura . " на складе " . $p->sklad . " в количестве " . abs($delta) . " " . $p->ed_ism;
            }
        }
        return [
            "can" => count($err) > 0 ? false : true,
            "err" => $err
        ];
    }

    // заносим основные переменные класса
    protected function set_vars(Production $p)
    {
        $this->p = $p;
        $this->pi = $p->items;
        $this->nomenklatura_title = $p->nomenklatura;
        $this->nomenklatura = $p->nomenklatura_id;
        $this->ed_ism_title = $p->ed_ism;
        $this->sklad = $p->sklad_id;
        $this->sklad_title = $p->sklad;
        // пользователь
        $user = Auth::user();
        $user_info = $user->info;
        // сотрудник
        $this->sotrudnik = $user_info->sotrudnik();
        // пользователь == кладовщик
        $this->is_keeper = $this->sotrudnik ? $this->sotrudnik->is_keeper($p->sklad_id) : false;
        // пользователь = администратор
        $this->is_admin = $user_info->is_admin();
        // старые значения
        $this->old = $p->getOriginal();
        // новые значения
        $this->new = $p;
    }

    // проверяем изменилось значение поля $field на $val
    protected function if_set($field, $val)
    {
        if ($this->p) {
            if (isset($this->old[$field]) && isset($this->new[$field]) && $this->old[$field] != $this->new[$field] && $this->new[$field] == $val) return true;
        } else {
            abort(421, 'Чтобы использовать if_set нужно сначала инициализировать set_vars.p');
            return false;
        }
        return false;
    }
    // проверяем изменилось значение поля
    protected function if_change($field)
    {
        if ($this->p) {
            if (isset($this->old[$field]) && isset($this->new[$field]) && $this->old[$field] != $this->new[$field]) return true;
        } else {
            abort(421, 'Чтобы использовать if_change нужно сначала инициализировать set_vars.p');
            return false;
        }
        return false;
    }
}