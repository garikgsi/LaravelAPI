<?php

namespace App;

use App\ABPTable;

class Kontragent extends ABPTable

{
    // экспорт в 1С
    use Traits\Trait1C;

    /*
        "name": "Catalog_Контрагенты"
            {
            "Ref_Key": "a348aa8c-d629-11e5-0a9b-000c295e30eb",
            "DataVersion": "AAAAAQAAAAA=",
            "DeletionMark": false,
            "Parent_Key": "4bc96af0-ba16-11e5-d193-000c295e30eb",
            "IsFolder": false,
            "Code": "00-000036",
            "Description": "ТЕХНО-ЛОГИСТИК ООО",
            "НаименованиеПолное": "ООО \"ТЕХНО-ЛОГИСТИК\"",
            "ОбособленноеПодразделение": false,
            "ЮридическоеФизическоеЛицо": "ЮридическоеЛицо",
            "СтранаРегистрации_Key": "c4ccb0a9-8857-11e5-a85a-3085a93ddca2",
            "ГоловнойКонтрагент_Key": "a348aa8c-d629-11e5-0a9b-000c295e30eb",
            "ИНН": "5024089920",
            "КПП": "501701001",
            "КодПоОКПО": "",
            "ДокументУдостоверяющийЛичность": "",
            "ОсновнойБанковскийСчет_Key": "a3476596-d629-11e5-0a9b-000c295e30eb",
            "УдалитьОсновнойДоговорКонтрагента_Key": "00000000-0000-0000-0000-000000000000",
            "ОсновноеКонтактноеЛицо_Key": "a3c5a3a2-d629-11e5-0a9b-000c295e30eb",
            "Комментарий": "",
            "ДополнительнаяИнформация": "",
            "УдалитьЮрФизЛицо": "",
            "ИННВведенКорректно": true,
            "КППВведенКорректно": true,
            "РасширенноеПредставлениеИНН": "5024089920",
            "РасширенноеПредставлениеКПП": "501701001",
            "НалоговыйНомер": "",
            "РегистрационныйНомер": "1075024007156",
            "ГосударственныйОрган": false,
            "ВидГосударственногоОргана": "",
            "КодГосударственногоОргана": "",
            "СвидетельствоСерияНомер": "",
            "СвидетельствоДатаВыдачи": "0001-01-01T00:00:00",
            "ДатаРегистрации": "0001-01-01T00:00:00",
            "ДатаСоздания": "0001-01-01T00:00:00",
            "КонтактнаяИнформация": [],
            "ДополнительныеРеквизиты": [],
            "ИсторияКПП": [],
            "ИсторияНаименований": [],
            "ИсторияКонтактнойИнформации": [],
            "Predefined": false,
            "PredefinedDataName": "",
            "Parent@navigationLinkUrl": "Catalog_Контрагенты(guid'a348aa8c-d629-11e5-0a9b-000c295e30eb')/Parent",
            "СтранаРегистрации@navigationLinkUrl": "Catalog_Контрагенты(guid'a348aa8c-d629-11e5-0a9b-000c295e30eb')/СтранаРегистрации",
            "ГоловнойКонтрагент@navigationLinkUrl": "Catalog_Контрагенты(guid'a348aa8c-d629-11e5-0a9b-000c295e30eb')/ГоловнойКонтрагент",
            "ОсновнойБанковскийСчет@navigationLinkUrl": "Catalog_Контрагенты(guid'a348aa8c-d629-11e5-0a9b-000c295e30eb')/ОсновнойБанковскийСчет",
            "ОсновноеКонтактноеЛицо@navigationLinkUrl": "Catalog_Контрагенты(guid'a348aa8c-d629-11e5-0a9b-000c295e30eb')/ОсновноеКонтактноеЛицо"
            }
 */

    public function __construct()
    {
        parent::__construct();

        $this->name_1c('Catalog_Контрагенты');
        $this->table('kontragents');
        $this->has_folders_1c(true);
        $this->has_files(true);
        $this->has_groups(true);
        $this->sub_tables([
            ["name_1c" => "КонтактнаяИнформация"],
            ["name_1c" => "ДополнительныеРеквизиты"],
            ["name_1c" => "ИсторияКПП"],
            ["name_1c" => "ИсторияНаименований"],
            ["name_1c" => "ИсторияКонтактнойИнформации"],
        ]);


        // НаименованиеПолное, ЮридическоеФизическоеЛицо,
        // ИНН,КПП,КодПоОКПО,ДокументУдостоверяющийЛичность, ОсновнойБанковскийСчет_Key,РегистрационныйНомер,СвидетельствоСерияНомер,
        // СвидетельствоДатаВыдачи",ДатаРегистрации,ДатаСоздания

        // $this->fillable(['full_name','type','inn','kpp','okpo','passport','rs_id','ogrn','svid_num','svid_date','reg_date']);

        $this->model([
            ["name" => "full_name", "name_1c" => "НаименованиеПолное", "type" => "string", "title" => "Полное наименование", "require" => false, "index" => "index", "show_in_table" => true, "size" => 4],
            ["name" => "type", "name_1c" => "ЮридическоеФизическоеЛицо", "type" => "enum", "items" => ["ЮридическоеЛицо", "ФизическоеЛицо"], "title" => "Юридическое или физическое лицо", "require" => true, "index" => "index", "show_in_table" => true, "size" => 12],
            ["name" => "inn", "name_1c" => "ИНН", "type" => "string", "title" => "ИНН", "require" => false, "index" => "index", "show_in_table" => true],
            ["name" => "kpp", "name_1c" => "КПП", "type" => "string", "title" => "КПП", "require" => false, "index" => "index", "show_in_table" => true],
            ["name" => "okpo", "name_1c" => "КодПоОКПО", "type" => "string", "title" => "Код по ОКПО", "require" => false, "index" => "index"],
            ["name" => "passport", "name_1c" => "ДокументУдостоверяющийЛичность", "type" => "string", "title" => "Документ удостоверяющий личность", "require" => false, "index" => "index", "show_in_form" => false],
            ["name" => "rs_id", "name_1c" => "ОсновнойБанковскийСчет_Key", "type" => "select", "table" => "rs", "table_class" => "RS", "title" => "Основной расчетный счет", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "ogrn", "name_1c" => "РегистрационныйНомер", "type" => "string", "title" => "ОГРН", "require" => false, "index" => "index", "show_in_table" => true],
            ["name" => "svid_num", "name_1c" => "СвидетельствоСерияНомер", "type" => "string", "title" => "Свидетельство серия и номер", "require" => false, "index" => "index", "show_in_form" => false],
            ["name" => "svid_date", "name_1c" => "СвидетельствоДатаВыдачи", "type" => "date", "title" => "Дата выдачи свидетельства", "require" => false, "default" => "0000-00-00", "index" => "index", "show_in_form" => false],
            ["name" => "reg_date", "name_1c" => "ДатаРегистрации", "type" => "date", "title" => "Дата регистрации", "require" => false, "default" => "0000-00-00", "index" => "index", "show_in_form" => false],
            ["name" => "address", "type" => "string", "title" => "Юридический адрес", "require" => false, "index" => "index", "show_in_table" => false, "size" => 4],
            ["name" => "phone", "type" => "phone", "title" => "Телефон", "require" => false, "index" => "index", "show_in_table" => true],
            ["name" => "www", "type" => "string", "title" => "Сайт", "require" => false, "index" => "index", "show_in_table" => false],
            ["name" => "email", "type" => "email", "title" => "Email", "require" => false, "index" => "index", "show_in_table" => true],


        ]);

        $this->sub_tables([
            [
                "table" => "sotrudniks", "icon" => "mdi-account", "class" => "Kontragent", "method" => "employees", "title" => "Сотрудники", "item_class" => "App\Sotrudnik", "belongs_method" => "employeable",
                "keys" => ["morph" => "employeable", "references" => "id", "foreign_table" => "sotrudniks", "reference_table" => "kontragents"]
            ],
            [
                "table" => "rs", "icon" => "mdi-bank", "class" => "RS", "method" => "rs_", "title" => "Расчетные счета", "item_class" => "App\RS", "belongs_method" => "rs_table",
                "keys" => ["morph" => "rs_table", "references" => "id", "foreign_table" => "rs", "reference_table" => "kontragents"]
            ],
            [
                "table" => "contracts", "icon" => "mdi-file-sign", "class" => "Contract", "method" => "contracts", "title" => "Договоры", "item_class" => "App\Contract", "belongs_method" => "contractable",
                "keys" => ["morph" => "contractable", "references" => "id", "foreign_table" => "contracts", "reference_table" => "kontragents"]
            ],
        ]);
    }

    // сотрудники
    public function employees()
    {
        return $this->morphMany('App\Sotrudnik', 'employeable');
    }
    // расчетные счета
    public function rs_()
    {
        return $this->morphMany('App\RS', 'rs_table');
    }
    // договоры
    public function contracts()
    {
        return $this->morphMany('App\Contract', 'contractable');
    }
}