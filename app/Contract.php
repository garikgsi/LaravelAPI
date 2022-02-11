<?php

namespace App;

use App\ABPTable;
use Carbon\Carbon;


// "odata.metadata": "http://10.0.1.22/1c/odata/standard.odata/$metadata#Catalog_ДоговорыКонтрагентов",
// "value": [{
// "Ref_Key": "b7569c10-f670-11ea-8591-000c29b45b09",
// "DataVersion": "AAAAAQAAAAA=",
// "DeletionMark": false,
// "Owner_Key": "b7569c0f-f670-11ea-8591-000c29b45b09",
// "Parent_Key": "00000000-0000-0000-0000-000000000000",
// "IsFolder": false,
// "Code": "00-000001",
// "Description": "Основной договор",
// "ВалютаВзаиморасчетов_Key": "a192de9a-d84c-11ea-8133-0050569f62a1",
// "Комментарий": "",
// "Организация_Key": "87fa6cc2-e901-11ea-8591-000c29b45b09",
// "ПроцентКомиссионногоВознаграждения": 0,
// "СпособРасчетаКомиссионногоВознаграждения": "",
// "ТипЦен_Key": "00000000-0000-0000-0000-000000000000",
// "ВидДоговора": "СПокупателем",
// "УчетАгентскогоНДС": false,
// "ВидАгентскогоДоговора": "",
// "РасчетыВУсловныхЕдиницах": false,
// "УдалитьРеализацияНаЭкспорт": false,
// "ВидВзаиморасчетов_Key": "00000000-0000-0000-0000-000000000000",
// "Дата": "0001-01-01T00:00:00",
// "Номер": "",
// "СрокДействия": "0001-01-01T00:00:00",
// "УстановленСрокОплаты": false,
// "СрокОплаты": "0",
// "НаименованиеДляСчетаФактурыНаАванс_Key": "00000000-0000-0000-0000-000000000000",
// "ПорядокРегистрацииСчетовФактурНаАвансПоДоговору": "",
// "Валютный": false,
// "ОплатаВВалюте": false,
// "ИспользуетсяПриОбменеДанными": false,
// "УдалитьПредъявляетНДС": false,
// "НДСПоСтавкам4и2": false,
// "Руководитель_Key": "87fa6cc8-e901-11ea-8591-000c29b45b09",
// "ЗаРуководителяПоПриказу": "",
// "ДолжностьРуководителя_Key": "87fa6cc9-e901-11ea-8591-000c29b45b09",
// "РуководительКонтрагента": "",
// "ЗаРуководителяКонтрагентаПоПриказу": "",
// "ДолжностьРуководителяКонтрагента": "",
// "ФайлДоговора_Key": "00000000-0000-0000-0000-000000000000",
// "ДоговорПодписан": false,
// "ПолРуководителяКонтрагента": "Мужской",
// "ПлатежныйАгент": false,
// "ТелефонПоставщика": "",
// "НаименованиеОператораПеревода": "",
// "ИННОператораПеревода": "",
// "АдресОператораПеревода": "",
// "ТелефонОператораПеревода": "",
// "ТелефонПлатежногоАгента": "",
// "ТелефонОператораПоПриемуПлатежей": "",
// "ОперацияПлатежногоАгента": "",
// "ГосударственныйКонтракт_Key": "00000000-0000-0000-0000-000000000000",
// "КодРаздел7ДекларацииНДС_Key": "00000000-0000-0000-0000-000000000000",
// "ПризнакАгента": "",
// "УчетАгентскогоНДСПокупателем": false,
// "СчетаФактурыОтИмениОрганизации": false,
// "СпособЗаполненияСтавкиНДС": "Автоматически",
// "ЭлектронныеУслуги": false,
// "ВидОбеспеченияОбязательствИПлатежей": "",
// "ОбеспечениеПредоставил": "",
// "ОбеспечениеПредоставил_Type": "StandardODATA.Undefined",
// "ОбеспечениеПолучил": "",
// "ОбеспечениеПолучил_Type": "StandardODATA.Undefined",
// "ОбеспечениеВыданоЗа": "",
// "ОбеспечениеВыданоЗа_Type": "StandardODATA.Undefined",
// "ДатаОплаты": "0001-01-01T00:00:00",
// "ДополнительныеРеквизиты": [],
// "Predefined": false,
// "PredefinedDataName": "",
// "Owner@navigationLinkUrl": "Catalog_ДоговорыКонтрагентов(guid'b7569c10-f670-11ea-8591-000c29b45b09')/Owner",
// "ВалютаВзаиморасчетов@navigationLinkUrl": "Catalog_ДоговорыКонтрагентов(guid'b7569c10-f670-11ea-8591-000c29b45b09')/ВалютаВзаиморасчетов",
// "Организация@navigationLinkUrl": "Catalog_ДоговорыКонтрагентов(guid'b7569c10-f670-11ea-8591-000c29b45b09')/Организация",
// "Руководитель@navigationLinkUrl": "Catalog_ДоговорыКонтрагентов(guid'b7569c10-f670-11ea-8591-000c29b45b09')/Руководитель",
// "ДолжностьРуководителя@navigationLinkUrl": "Catalog_ДоговорыКонтрагентов(guid'b7569c10-f670-11ea-8591-000c29b45b09')/ДолжностьРуководителя"
// }

class Contract extends ABPTable
{
    // экспорт в 1С
    // use Traits\Trait1C;


    public function __construct()
    {
        parent::__construct();

        // $this->name_1c('Catalog_ДоговорыКонтрагентов');
        // $this->has_folders_1c(true);
        $this->table('contracts');
        $this->has_files(true);
        $this->has_images(false);
        $this->has_groups(false);


        $this->model([
            ["name" => "contract_num", "type" => "string", "max" => 32, "title" => "№ договора", "require" => true, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 1],
            ["name" => "contract_date", "type" => "date", "title" => "Дата договора", "require" => true, "index" => "index", "show_in_table" => true, "show_in_form" => true, "out_index" => 2],
            ["name" => "firm_id", "type" => "select", "table" => "firms", "table_class" => "Firm", "title" => "Организация", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false, "out_index" => 4],
            ["name" => "contractable", "title" => "Контрагент", "type" => "morph", "tables" => [["table" => "kontragents", "title" => "Контрагенты", "type" => "App\\Kontragent"]], "require" => true, "props" => ["edit" => ["tableSelectorReadonly" => true]], "show_in_table" => false],
            ["name" => "contract_type_id", "type" => "select", "table" => "contract_types", "table_class" => "ContractType", "title" => "Вид договора", "require" => true, "default" => 1, "index" => "index", "show_in_table" => true],
            [
                "name" => "rs_id", "type" => "foreign_select", "structure" =>
                [
                    ["table" => "firms", "title" => "организацию"],
                    ["table" => "rs", "title" => "р/сч", "key" => ["rs_table" => ["rs_table_type" => "App\Firm", "rs_table_id" => "rs_table_id"]]]
                ],
                "title" => "Расчетный счет", "default" => 1, "index" => "index", "readonly" => false, "require" => true, "show_in_table" => false, "show_in_form" => true, "out_index" => 5
            ],
            ["name" => "is_written", "type" => "boolean", "title" => "Подписан", "default" => "0", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => false, "out_index" => 7],
            ["name" => "end_date", "type" => "date", "title" => "Действует до", "require" => false, "index" => "index", "default" => date("Y-12-31"), "show_in_table" => false, "show_in_form" => true],
            ["name" => "write_date", "type" => "date", "title" => "Дата подписания", "require" => false, "readonly" => true, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "summa", "type" => "money", "title" => "Сумма договора", "require" => false, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "kontragent", "type" => "string", "virtual" => true, "title" => "Контрагент", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
        ]);

        // заменим стандартное поведение столбцов модели
        $this->modModel([
            ["name" => "comment", "out_index" => 1, "show_in_table" => true]
        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['kontragent', 'contract_type', 'firm']);

        // подчиненные таблицы
        $this->sub_tables([
            ["table" => "orders", "icon" => "mdi-cart-minus", "class" => "Order", "method" => "orders", "title" => "Заказы", "item_class" => "App\Order", "belongs_method" => "contract_", "keys" => ["foreign" => "contract_id", "references" => "id", "foreign_table" => "orders", "reference_table" => "contracts"]],
        ]);
    }

    // связи
    // контрагент договора
    public function contractable()
    {
        return $this->morphTo();
    }
    // расчетный счет
    public function rs_()
    {
        return $this->belongsTo('App\RS', 'rs_id');
    }
    // организация
    public function firm_()
    {
        $rs = $this->rs_;
        return $rs ? $this->rs_->rs_table : null;
    }
    // вид договора
    public function contract_type_()
    {
        return $this->belongsTo('App\ContractType', 'contract_type_id');
    }
    // заказы
    public function orders()
    {
        return $this->hasMany('App\Order');
    }


    // читатели
    // для селекта
    public function getSelectListTitleAttribute()
    {
        $title = '';
        if (isset($this->contract_date)) $doc_date = Carbon::createFromFormat('Y-m-d', $this->contract_date);
        $format_date = isset($doc_date) ? $doc_date->format('d.m.Y') : $this->contract_date;
        $title = "№ " . $this->contract_num . " от " . $format_date . ($this->kontragent ? " c " . $this->kontragent : '') . (strlen($this->comment > 0) ? "(" . $this->comment . ")" : '');
        return $title;
    }

    // контрагент
    public function getKontragentAttribute()
    {
        return $this->contractable ? $this->contractable->getSelectListTitleAttribute() : '';
    }
    // организация
    public function getFirmAttribute()
    {
        $firm = $this->firm_();
        return $firm ? $firm->first()->getSelectListTitleAttribute() : '';
    }
    // вид договора
    public function getContractTypeAttribute()
    {
        $contract_type = $this->contract_type_()->first();
        return $contract_type ? $contract_type->getSelectListTitleAttribute() : '';
    }
}