<?php

namespace App\Triggers;

use App\Production;
use App\ProductionItem;
use App\ProductionComponent;
use App\SkladRegister;
use App\Nomenklatura;
use App\RecipeItem;
use App\RecipeItemReplace;
use App\ProductionReplace;

use Illuminate\Support\Facades\Auth;
use App\Exceptions\TriggerException;
use Illuminate\Support\Facades\Log;


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

    // кэш остатков
    private $cache_remains = [];

    /**
     * Получаем остатки из регистра накопления и сохраняем в кэш
     *
     * @param  Production $p
     * @param  int $nomenklatura_id
     * @return array [string title, string ed_ism, boolean is_usluga, float remains, float avg_price]
     */
    private function remains(Production $p, int $nomenklatura_id): array
    {
        if (!isset($this->cache_remains[$nomenklatura_id])) {
            $n = Nomenklatura::find($nomenklatura_id);
            if ($n) {
                $this->cache_remains[$nomenklatura_id] = [
                    'title' => $n->short_title,
                    'ed_ism' => $n->edIsm,
                    'avg_price' => $n->avg_price
                ];
                if ($n->is_usluga == 1) {
                    $this->cache_remains[$nomenklatura_id]['is_usluga'] = true;
                } else {
                    $reg = new SkladRegister;
                    $this->cache_remains[$nomenklatura_id]['is_usluga'] = false;
                    $this->cache_remains[$nomenklatura_id]['remains'] = $reg->ostatok($p->sklad_id, $nomenklatura_id, 'now', true);
                }
            } else {
                throw new TriggerException("#PO. Не удается найти номенклатуру $nomenklatura_id");
            }
        }
        return $this->cache_remains[$nomenklatura_id];
    }
    /**
     * Очистка кэша с остатками
     *
     * @return void
     */
    private function clear_remains()
    {
        $this->cache_remains = [];
    }
    /**
     * Уменьшаем остатки $nomenklatura_id в кэше на $kolvo
     *
     * @param  mixed $nomenklatura_id
     * @param  mixed $kolvo
     * @return null | array [string title, string ed_ism, boolean is_usluga, float remains, float avg_price]
     */
    private function sub_remains(int $nomenklatura_id, float $kolvo): array|null
    {
        if (isset($this->cache_remains[$nomenklatura_id])) {
            if (!$this->cache_remains[$nomenklatura_id]['is_usluga']) {
                if ($kolvo > $this->cache_remains[$nomenklatura_id]['remains']) {
                    return null;
                } else {
                    $this->cache_remains[$nomenklatura_id]['remains'] -= $kolvo;
                }
            }
            return $this->cache_remains[$nomenklatura_id];
        } else {
            return null;
        }
    }

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
        $debug = true;

        $this->set_vars($p);

        // изменяется количество
        if ($p->isDirty('kolvo')) {
            $old_kolvo = intval($p->getOriginal('kolvo'));
            $new_kolvo = intval($p->kolvo);
            if ($old_kolvo != $new_kolvo) {
                // если проведено - сначало надо распровести
                if ($p->getOriginal('is_active') == 1) {
                    throw new TriggerException('#PO.Для изменения количества необходимо сначала распровести производство');
                    // abort(421, '#PO.Для изменения количества необходимо сначала распровести производство');
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
                        throw new TriggerException('#PO.Уменьшение количества готовых изделий в партии невозможно');
                        // abort(421, '#PO.Уменьшение количества готовых изделий в партии невозможно');
                        return false;
                    }
                }
            }
        }

        // изменяется рецептура
        if ($p->isDirty('recipe_id')) {
            if ($p->getOriginal('is_active') == 1) {
                throw new TriggerException('#PO.Для изменения рецептуры необходимо сначала распровести производство');
                // abort(421, '#PO.Для изменения рецептуры необходимо сначала распровести производство');
                return false;
            }
        }

        // изменяется склад
        if ($p->isDirty('sklad_id')) {
            if ($p->getOriginal('is_active') == 1) {
                throw new TriggerException('#PO.Для изменения склада необходимо сначала распровести производство');
                // abort(421, '#PO.Для изменения склада необходимо сначала распровести производство');
                return false;
            }
        }

        // если распроводим документ
        if ($p->isDirty('is_active') && $p->is_active == 0) {
            $check = $this->check_unactive($p, "Распровести");
            if (!$check["can"]) {
                throw new TriggerException("#PO." . implode(", ", $check["err"]));
                // abort(421, "#PO." . implode(", ", $check["err"]));
                return false;
            }
        }

        // если проводим документ
        if ($p->isDirty('is_active') && $p->is_active == 1) {
            // распроводить по складам могут только кладовщики
            if ($this->is_keeper || $this->is_admin) {
                $items = $p->items()->select('id')->get();
                $items_ids = $items->pluck('id');
                $components = ProductionComponent::whereIn('production_item_id', $items_ids)->get()->values();
                // суммы списываемой номенклатуры
                $sum_by_components = $components->groupBy('nomenklatura_id')->map(function ($components) {
                    return $components->sum('kolvo');
                });
                // анализируем остатки на складе
                $this->clear_remains();
                $deficit = []; // недостаточно для списания
                $deficit_after_replace = [];  // недостаточно для списания после замен
                $allow_for_debt = []; // доступно для списания
                $sum_by_components->each(function ($need_kolvo, $nomenklatura_id) use ($p, &$deficit, &$allow_for_debt) {
                    $remains = $this->remains($p, $nomenklatura_id);
                    if (!$remains['is_usluga']) {
                        $deficit_nomenklatura = $remains['remains'] - $need_kolvo;
                        if ($deficit_nomenklatura < 0) {
                            $deficit[$nomenklatura_id] = $deficit_nomenklatura;
                            $allow_for_debt[$nomenklatura_id] = 0;
                        } else {
                            $allow_for_debt[$nomenklatura_id] = $deficit_nomenklatura;
                        }
                    }
                });
                // максимально возможное кол-во для списания
                $max_kolvo_for_replace = $allow_for_debt;
                // рецепт
                $recipe_replaces = collect([]);
                $production_replaces = collect([]);
                if (count($deficit) > 0) {
                    // подгрузим замены
                    $recipe_items_ids = RecipeItem::where('recipe_id', $p->recipe_id)->get()->pluck('id');
                    // поля замен
                    $replace_fields = ['nomenklatura_from_id', 'nomenklatura_to_id', 'kolvo_from', 'kolvo_to'];
                    // замены рецептуры
                    $recipe_replaces = RecipeItemReplace::whereIn('recipe_item_id', $recipe_items_ids)->get()
                        ->mapToGroups(function ($replace) use ($replace_fields) {
                            $res = [
                                'item_id' => 1,
                                'component_id' => 1
                            ];
                            foreach ($replace_fields as $field) {
                                $res[$field] = $replace->$field;
                            }
                            // return $res;
                            return [$replace['nomenklatura_from_id'] => $res];
                        });
                    // dd($recipe_replaces->toArray());
                    // замены производства
                    $production_replaces = ProductionReplace::where('production_id', $p->id)->get()
                        ->mapToGroups(function ($replace) use ($replace_fields, $components) {
                            $item_id = 1;
                            $component_id = 1;
                            $max_kolvo = null;
                            try {
                                if ($replace->component_id > 1) {
                                    $component = $components->firstWhere('id', $replace->component_id);
                                    $component_id = $replace->component_id;
                                    $item_id = $component->production_item_id;
                                    $max_kolvo = $component->kolvo;
                                }
                            } catch (\Throwable $th) {
                            }
                            $res = [
                                'item_id' => $item_id,
                                'component_id' => $component_id
                            ];
                            if ($max_kolvo) $res['max_kolvo'] = $max_kolvo;
                            foreach ($replace_fields as $field) {
                                $res[$field] = $replace->$field;
                            }
                            return [$replace['nomenklatura_from_id'] => $res];
                        })->map(function ($replaces) {
                            return $replaces->sortByDesc('item_id');
                        });

                    // проверим, что замен хватит для производства
                    foreach ($deficit as $deficit_nomenklatura_id => $deficit_kolvo) {
                        $need_debt = abs($deficit_kolvo);
                        $replaces = [];
                        if (isset($production_replaces[$deficit_nomenklatura_id])) {
                            $replaces = array_merge($replaces, $production_replaces[$deficit_nomenklatura_id]->toArray());
                        }
                        if (isset($recipe_replaces[$deficit_nomenklatura_id])) {
                            $replaces = array_merge($replaces, $recipe_replaces[$deficit_nomenklatura_id]->toArray());
                        }
                        // считаем замещение
                        foreach ($replaces as $replace) {
                            if ($need_debt > 0) {
                                // коэфициент списания
                                $ratio = $replace['kolvo_to'] / $replace['kolvo_from'];
                                // максимально возможное кол-во для списания
                                $nomenklatura_remains = $this->remains($p, $replace['nomenklatura_to_id']);
                                $replace_remains = $nomenklatura_remains['remains'];
                                // для услуг остатки не проверяем
                                if ($nomenklatura_remains['is_usluga']) {
                                    $need_debt = 0;
                                } else {
                                    if (isset($allow_for_debt[$replace['nomenklatura_to_id']])) {
                                        $max_debt_kolvo = $allow_for_debt[$replace['nomenklatura_to_id']];
                                    } else {
                                        $max_debt_kolvo = $replace_remains;
                                        $allow_for_debt[$replace['nomenklatura_to_id']] = $max_debt_kolvo;
                                    }
                                    // максимально возможное кол-во списания(например, при замене компонента)
                                    $max_replace_kolvo = $need_debt;
                                    if (isset($replace['max_kolvo'])) {
                                        $max_replace_kolvo = $replace['max_kolvo'];
                                    }
                                    // нужное кол-во с учетом коэффициента
                                    $need_debt_with_ratio = $max_replace_kolvo * $ratio;
                                    // сравниваем кол-во
                                    if ($need_debt_with_ratio > $max_debt_kolvo) {
                                        $need_debt -= $max_debt_kolvo / $ratio;
                                        $allow_for_debt[$replace['nomenklatura_to_id']] = 0;
                                    } else {
                                        $allow_for_debt[$replace['nomenklatura_to_id']] -= $need_debt_with_ratio;
                                        $need_debt = 0;
                                    }
                                }
                            } else {
                                break;
                            }
                        }
                        if ($need_debt > 0) {
                            $deficit_after_replace[$deficit_nomenklatura_id] = $need_debt;
                        }
                    }
                }
                if ($debug) Log::info("--- ПРОВОДИМ ПРОИЗВОДСТВО $p->id --- ", [
                    'sum_by_components' => $sum_by_components->toArray(),
                    'recipe_replaces' => $recipe_replaces->toArray(),
                    'production_replaces' => $production_replaces->toArray(),
                    'deficit_after_replace' => $deficit_after_replace,
                ]);
                // если после замен все равно недостаточно - выведем ошибку
                if (count($deficit_after_replace) > 0) {
                    $remains_errors = [];
                    foreach ($deficit_after_replace as $nomenklatura_id => $kolvo) {
                        $nomenklatura_remains = $this->remains($p, $nomenklatura_id);
                        $remains_errors[] = $nomenklatura_remains['title'] . " в количестве " . $kolvo . " " . $nomenklatura_remains['ed_ism'];
                    }
                    throw new TriggerException("#PO. Недостаточно: " . implode(', ', $remains_errors));
                    // abort(421, "#PO. Недостаточно: " . implode(', ', $remains_errors));
                    return false;
                } else {
                    // сборка изделий
                    $components_by_items_and_nomenklatura = $components->groupBy('production_item_id', 'nomenklatura_id');
                    $components_by_items_and_nomenklatura->each(function ($components, $item_id) use ($p, $production_replaces, $recipe_replaces, &$max_kolvo_for_replace, $debug) {
                        // себестоимость
                        $self_price = 0;
                        if ($debug) Log::info("--- СОБИРАЕМ ИЗДЕЛИЕ $item_id --- ", [
                            'components' => $components->pluck('kolvo', 'nomenklatura_id')->toArray(),
                            'max_kolvo_for_replace' => $max_kolvo_for_replace,
                        ]);
                        $components->each(function ($component) use ($p, &$self_price, $production_replaces, $recipe_replaces, &$max_kolvo_for_replace, $debug) {
                            // осталось списать
                            $need_kolvo = $component->kolvo;
                            // берем остатки из кэша
                            $cache_remains = $this->remains($p, $component->nomenklatura_id);
                            if ($debug) Log::info("--- ОБРАБАТЫВАЕМ НОМЕНКЛАТУРУ КОМПОНЕНТА $component->nomenklatura_id --- ", [
                                'need_kolvo' => $need_kolvo,
                                'cache_remains' => $cache_remains
                            ]);
                            // для услуги - только себестоимость
                            if ($cache_remains['is_usluga']) {
                                $sub_kolvo = $need_kolvo;
                            } else {
                                if ($need_kolvo > $cache_remains['remains']) {
                                    $sub_kolvo = $cache_remains['remains'];
                                } else {
                                    $sub_kolvo = $need_kolvo;
                                }
                            }
                            if (!$this->sub_remains($component->nomenklatura_id, $sub_kolvo)) {
                                throw new TriggerException("#PO. Ошибка чтения кэша остатков");
                                if ($debug) Log::info("--- ОШИБКА ЧТЕНИЯ ОСТАТКОВ ДЛЯ НОМЕНКЛАТУРЫ $component->nomenklatura_id. СПИСЫВАЕМ $sub_kolvo ПРИ НАЛИЧИИ " . $cache_remains['remains'] . " --- ");
                            }
                            $need_kolvo -= $sub_kolvo;
                            $component_sum = $cache_remains['avg_price'] * $sub_kolvo;
                            $component->fill([
                                "kolvo" => $sub_kolvo,
                                "price" => $cache_remains['avg_price'],
                                "summa" => $component_sum,
                            ])->save();
                            $self_price += $component_sum;
                            if ($debug) Log::info("--- СПИСАНИЕ ИЗ ОСТАТКОВ --- ", [
                                'sub_kolvo' => $sub_kolvo,
                                'need_kolvo' => $need_kolvo,
                                'self_price' => $self_price
                            ]);
                            // замены
                            if ($need_kolvo > 0) {
                                $replaces = [];
                                if (isset($production_replaces[$component->nomenklatura_id])) {
                                    $replaces = array_merge($replaces, $production_replaces[$component->nomenklatura_id]->toArray());
                                }
                                if (isset($recipe_replaces[$component->nomenklatura_id])) {
                                    $replaces = array_merge($replaces, $recipe_replaces[$component->nomenklatura_id]->toArray());
                                }
                                if ($debug) Log::info("--- ОБРАБАТЫВАЕМ ЗАМЕНЫ ДЛЯ КОМПОНЕНТА $component->nomenklatura_id --- ", [
                                    'need_kolvo' => $need_kolvo,
                                    'replaces' => $replaces
                                ]);
                                // считаем замещение
                                foreach ($replaces as $replace) {
                                    if ($debug) Log::info("--- ОБРАБАТЫВАЕМ ЗАМЕНУ --- ", [
                                        'replace' => $replace
                                    ]);
                                    if ($need_kolvo > 0) {
                                        if ($replace['component_id'] > 1 && $component->id != $replace['component_id']) {
                                            if ($debug) Log::info("--- ЗАМЕНА ТОЛЬКО ДЛЯ КОМПОНЕНТА С ID=" . $replace['component_id'] . ", У НАС ID= $component->id, ПРОПУСКАЕМ --- ");
                                            continue;
                                        } else {
                                            // коэфициент списания
                                            $ratio = $replace['kolvo_to'] / $replace['kolvo_from'];
                                            // максимально возможное кол-во для списания
                                            $nomenklatura_remains = $this->remains($p, $replace['nomenklatura_to_id']);
                                            $replace_remains = $nomenklatura_remains['remains'];
                                            // для услуг остатки не проверяем
                                            if ($nomenklatura_remains['is_usluga']) {
                                                $sub_kolvo = $need_kolvo * $ratio;
                                                $need_kolvo = 0;
                                            } else {
                                                // если указано максимально возможное кол-во списания
                                                if (isset($max_kolvo_for_replace[$replace['nomenklatura_to_id']]) && $max_kolvo_for_replace[$replace['nomenklatura_to_id']] < $replace_remains) {
                                                    $replace_remains = $max_kolvo_for_replace[$replace['nomenklatura_to_id']];
                                                }
                                                if ($replace_remains > 0) {
                                                    // нужное кол-во с учетом коэффициента
                                                    $need_debt_with_ratio = $need_kolvo * $ratio;
                                                    // сравниваем кол-во
                                                    if ($need_debt_with_ratio > $replace_remains) {
                                                        $need_kolvo -= $replace_remains / $ratio;
                                                        $sub_kolvo = $replace_remains;
                                                    } else {
                                                        $sub_kolvo = $need_kolvo * $ratio;
                                                        $need_kolvo = 0;
                                                    }
                                                    if (isset($max_kolvo_for_replace[$replace['nomenklatura_to_id']])) $max_kolvo_for_replace[$replace['nomenklatura_to_id']] -= $sub_kolvo;
                                                } else {
                                                    if ($debug) Log::info("--- ДОСТИГНУТО МАКСИМАЛЬНОЕ КОЛ-ВО ДЛЯ ЗАМЕНЫ " . $replace['component_id'] . " --- ");
                                                    continue;
                                                }
                                            }
                                            if (!$this->sub_remains($replace['nomenklatura_to_id'], $sub_kolvo)) {
                                                throw new TriggerException("#PO. Ошибка чтения кэша остатков");
                                                if ($debug) Log::info("--- ОШИБКА ЧТЕНИЯ ОСТАТКОВ ДЛЯ НОМЕНКЛАТУРЫ " . $replace['nomenklatura_to_id'] . ". СПИСЫВАЕМ $sub_kolvo ПРИ НАЛИЧИИ $replace_remains --- ");
                                            }

                                            $replace_sum = $nomenklatura_remains['avg_price'] * $sub_kolvo;
                                            $self_price += $replace_sum;
                                            // добавляем компонент в производство
                                            $new_production_component = $component->replicate();
                                            $new_component_data = [
                                                "nomenklatura_id" => $replace['nomenklatura_to_id'],
                                                "kolvo" => $sub_kolvo,
                                                "price" => $nomenklatura_remains['avg_price'],
                                                "summa" => $replace_sum,
                                                "is_replaced" => 1
                                            ];
                                            $new_production_component->fill($new_component_data)->save();
                                            if ($debug) Log::info("--- ДОБАВЛЯЕМ ЗАМЕНУ --- ", [
                                                'ratio' => $ratio,
                                                'nomenklatura_remains' => $nomenklatura_remains,
                                                'sub_kolvo' => $sub_kolvo,
                                                'new_component_data' => $new_component_data,
                                                'new_component_registers' => $new_production_component->register()->get()->pluck('kolvo', 'nomenklatura_id')->toArray()
                                            ]);
                                        }
                                    } else {
                                        if ($debug) Log::info("--- БОЛЬШЕ ЗАМЕН НЕ НУЖНО, ПРОПУСКАЕМ --- ");
                                        break;
                                    }
                                }
                            }
                            if ($need_kolvo > 0) {
                                throw new TriggerException("#PO. Недостаточно " . $need_kolvo . " " . $cache_remains['ed_ism'] . " " . $cache_remains['title'] . " для производства");
                            }
                        });
                        $production_item = $p->items()->find($item_id);
                        $production_item->fill([
                            'price' => $self_price,
                            'summa' => $self_price
                        ])->save();
                        if ($debug) Log::info("--- СОХРАНЕМ ИЗДЕЛИЕ $production_item->id --- ", [
                            'self_price' => $self_price,
                        ]);
                    });
                }
            } else {
                throw new TriggerException('#PO.Проводить можно только кладовщику или администратору');
                // abort(421, '#PO.Проводить можно только кладовщику или администратору');
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
            throw new TriggerException("#PO." . implode(", ", $check["err"]));
            // abort(421, "#PO." . implode(", ", $check["err"]));
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
                throw new TriggerException("#PO.Ошибка при удалении произведенной продукции #" . $item->id);
                // abort(421, "#PO.Ошибка при удалении произведенной продукции #" . $item->id);
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
            throw new TriggerException('Чтобы использовать if_set нужно сначала инициализировать set_vars.p');
            // abort(421, 'Чтобы использовать if_set нужно сначала инициализировать set_vars.p');
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
            throw new TriggerException('Чтобы использовать if_change нужно сначала инициализировать set_vars.p');
            // abort(421, 'Чтобы использовать if_change нужно сначала инициализировать set_vars.p');
            return false;
        }
        return false;
    }
}