<?php

namespace App;

use App\ABPTable;

class FizLico extends ABPTable

{
    // экспорт в 1С
    use Traits\Trait1C;

    /*
        "name": "Catalog_ФизическиеЛица"
            {
                "Ref_Key": "11f1f52c-c349-11e5-5998-000c295e30eb",
                "DataVersion": "AAAAAQAAAAA=",
                "DeletionMark": false,
                "Parent_Key": "00000000-0000-0000-0000-000000000000",
                "IsFolder": false,
                "Code": "00-0000003",
                "Description": "Фелик Дмитрий Вадимович",
                "ДатаРождения": "1993-12-13T00:00:00",
                "Пол": "Мужской",
                "ИНН": "501713809008",
                "СтраховойНомерПФР": "167-272-679 00",
                "МестоРождения": "0,Истра,Истринский район,Московская,Россия",
                "ГруппаДоступа_Key": "00000000-0000-0000-0000-000000000000",
                "УдалитьПол": "",
                "ФИО": "Фелик Дмитрий Вадимович",
                "УточнениеНаименования": "",
                "ДатаРегистрации": "0001-01-01T00:00:00",
                "НаименованиеСлужебное": "ФЕЁЛИК ДМИТРИЙ ВАДИМОВИЧ",
                "ОсновнойБанковскийСчет_Key": "00000000-0000-0000-0000-000000000000",
                "ПостоянноПроживалВКрыму18Марта2014Года": false,
                "Фамилия": "Фелик",
                "Имя": "Дмитрий",
                "Отчество": "Вадимович",
                "ИнициалыИмени": "Д.",
                "МестоРожденияПредставление": "",
                "ЛьготаПриНачисленииПособий": "",
                "КонтактнаяИнформация": [],
                "ДополнительныеРеквизиты": [],
                "Predefined": false,
                "PredefinedDataName": ""
            }
 */

    public function __construct()
    {
        parent::__construct();

        $this->name_1c('Catalog_ФизическиеЛица');
        $this->table('fizlica');
        $this->has_folders_1c(true);

        // ДатаРождения, Пол, ИНН, СтраховойНомерПФР, МестоРождения, ФИО, ОсновнойБанковскийСчет_Key,
        // Фамилия, Имя, Отчество

        // $this->fillable(['birthday','gender','inn','snils','birth_place','fio','rs_id','firstname','namefl','fathername']);

        $this->model([
            ["name" => "birthday", "name_1c" => "ДатаРождения", "type" => "date", "title" => "Дата рождения", "require" => false, "default" => "0000-00-00", "index" => "index"],
            // ["name" => "gender", "name_1c" => "Пол", "type" => "enum", "data" => ["Мужской", "Женский"], "title" => "Пол", "require" => false, 'default' => 'Мужской', "index" => "index"],
            ["name" => "inn", "name_1c" => "ИНН", "type" => "string", "title" => "ИНН", "require" => false, "index" => "index"],
            ["name" => "snils", "name_1c" => "СтраховойНомерПФР", "type" => "string", "title" => "СНИЛС", "require" => false, "index" => "index"],
            ["name" => "birth_place", "name_1c" => "МестоРождения", "type" => "string", "title" => "Место Рождения", "require" => false, "index" => "index"],
            ["name" => "fio", "name_1c" => "ФИО", "type" => "string", "title" => "ФИО", "require" => false, "index" => "index"],
            ["name" => "rs_id", "name_1c" => "ОсновнойБанковскийСчет_Key", "type" => "select", "table" => "rs", "table_class" => "RS", "title" => "Основной расчетный счет", "require" => false, "default" => 1, "index" => "index"],
            ["name" => "firstname", "name_1c" => "Фамилия", "type" => "string", "title" => "Фамилия", "require" => false, "index" => "index"],
            ["name" => "namefl", "name_1c" => "Имя", "type" => "string", "title" => "Имя", "require" => false, "index" => "index"],
            ["name" => "fathername", "name_1c" => "Отчество", "type" => "string", "title" => "Отчество", "require" => false, "index" => "index"],
        ]);
    }
}