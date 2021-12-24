<?php

namespace App;

use App\ABPTable;
use Carbon\Carbon;

class Act extends ABPTable
{
    // экспорт в 1С
    use Traits\Trait1C;

    // "odata.metadata": "http://10.0.1.22/1c/odata/standard.odata/$metadata#Document_РеализацияТоваровУслуг",
    // "value": [{
    // "Ref_Key": "8b9660f6-6332-11ec-ad72-000c29b45b09",
    // "DataVersion": "AAAACgAAAAA=",
    // "DeletionMark": false,
    // "Number": "0000-000001",
    // "Date": "2021-12-22T17:22:08",
    // "Posted": false,
    // "ВидОперации": "Товары",
    // "Организация_Key": "87fa6cc2-e901-11ea-8591-000c29b45b09",
    // "Склад_Key": "f40235d3-d84c-11ea-8133-0050569f62a1",
    // "ПодразделениеОрганизации_Key": "00000000-0000-0000-0000-000000000000",
    // "ДеятельностьНаПатенте": false,
    // "Патент_Key": "00000000-0000-0000-0000-000000000000",
    // "Контрагент_Key": "b7569bcd-f670-11ea-8591-000c29b45b09",
    // "ДоговорКонтрагента_Key": "00000000-0000-0000-0000-000000000000",
    // "СпособЗачетаАвансов": "Автоматически",
    // "ТипЦен_Key": "00000000-0000-0000-0000-000000000000",
    // "ВалютаДокумента_Key": "a192de9a-d84c-11ea-8133-0050569f62a1",
    // "КурсВзаиморасчетов": 1,
    // "КратностьВзаиморасчетов": "1",
    // "СуммаВключаетНДС": true,
    // "УдалитьУчитыватьНДС": false,
    // "СчетУчетаРасчетовСКонтрагентом_Key": "86eff6fd-d84c-11ea-8133-0050569f62a1",
    // "СчетУчетаРасчетовПоАвансам_Key": "86eff6fe-d84c-11ea-8133-0050569f62a1",
    // "СчетУчетаРасчетовПоТаре_Key": "86eff76a-d84c-11ea-8133-0050569f62a1",
    // "УдалитьСчетУчетаДоходовПоТаре_Key": "00000000-0000-0000-0000-000000000000",
    // "УдалитьСчетУчетаРасходовПоТаре_Key": "00000000-0000-0000-0000-000000000000",
    // "УдалитьСтатьяДоходовИРасходовПоТаре_Key": "00000000-0000-0000-0000-000000000000",
    // "СчетНаОплатуПокупателю_Key": "00000000-0000-0000-0000-000000000000",
    // "Грузоотправитель_Key": "00000000-0000-0000-0000-000000000000",
    // "Грузополучатель_Key": "00000000-0000-0000-0000-000000000000",
    // "АдресДоставки": "",
    // "БанковскийСчетОрганизации_Key": "63ee33d4-e908-11ea-8591-000c29b45b09",
    // "СуммаДокумента": 150129,
    // "Ответственный_Key": "10f78d15-dfe4-11eb-b4ad-000c29b45b09",
    // "Комментарий": "",
    // "РучнаяКорректировка": false,
    // "Руководитель_Key": "87fa6cc8-e901-11ea-8591-000c29b45b09",
    // "ГлавныйБухгалтер_Key": "00000000-0000-0000-0000-000000000000",
    // "ОтпускПроизвел_Key": "00000000-0000-0000-0000-000000000000",
    // "УдалитьЗаРуководителяПоПриказу": "",
    // "УдалитьЗаГлавногоБухгалтераПоПриказу": "",
    // "ЗаЗаказчикаНаОсновании": "",
    // "ДоверенностьНомер": "",
    // "ДоверенностьДата": "0001-01-01T00:00:00",
    // "ДоверенностьВыдана": "",
    // "ДоверенностьЧерезКого": "",
    // "ВидЭлектронногоДокумента": "ТОРГ12Продавец",
    // "ДокументБезНДС": false,
    // "ЗаРуководителяНаОсновании_Key": "00000000-0000-0000-0000-000000000000",
    // "ЗаГлавногоБухгалтераНаОсновании_Key": "00000000-0000-0000-0000-000000000000",
    // "Перевозчик_Key": "00000000-0000-0000-0000-000000000000",
    // "МаркаАвтомобиля": "",
    // "РегистрационныйЗнакАвтомобиля": "",
    // "Водитель": "",
    // "ВодительскоеУдостоверение": "",
    // "КраткоеНаименованиеГруза": "",
    // "СопроводительныеДокументы": "",
    // "ДеятельностьНаТорговомСборе": false,
    // "ОтветственныйЗаОформление_Key": "00000000-0000-0000-0000-000000000000",
    // "СведенияОТранспортировкеИГрузе": "",
    // "ПеревозкаАвтотранспортом": false,
    // "ЕстьМаркируемаяПродукцияГИСМ": false,
    // "НомерЧекаККМ": "0",
    // "СпособДоставки_Key": "2528e58f-e921-11ea-8591-000c29b45b09",
    // "НомерДляОтслеживания": "",
    // "МаркаПрицепа": "",
    // "РегистрационныйЗнакПрицепа": "",
    // "Товары": [
    // {
    // "Ref_Key": "8b9660f6-6332-11ec-ad72-000c29b45b09",
    // "LineNumber": "1",
    // "Номенклатура_Key": "2d4fe75f-48a4-11ec-ad72-000c29b45b09",
    // "КоличествоМест": 0,
    // "ЕдиницаИзмерения_Key": "f40235d5-d84c-11ea-8133-0050569f62a1",
    // "Коэффициент": 1,
    // "Количество": 25,
    // "Цена": 5455,
    // "Сумма": 136375,
    // "СтавкаНДС": "НДС20",
    // "СуммаНДС": 22729.17,
    // "СчетУчета_Key": "86eff64f-d84c-11ea-8133-0050569f62a1",
    // "ПереданныеСчетУчета_Key": "00000000-0000-0000-0000-000000000000",
    // "СчетДоходов_Key": "86eff7be-d84c-11ea-8133-0050569f62a1",
    // "Субконто": "f402359a-d84c-11ea-8133-0050569f62a1",
    // "Субконто_Type": "StandardODATA.Catalog_ПрочиеДоходыИРасходы",
    // "СчетУчетаНДСПоРеализации_Key": "86eff7bf-d84c-11ea-8133-0050569f62a1",
    // "СчетРасходов_Key": "86eff7bf-d84c-11ea-8133-0050569f62a1",
    // "ДокументОприходования": "",
    // "ДокументОприходования_Type": "StandardODATA.Undefined",
    // "Себестоимость": 0,
    // "НомерГТД_Key": "00000000-0000-0000-0000-000000000000",
    // "СтранаПроисхождения_Key": "00000000-0000-0000-0000-000000000000",
    // "КиЗ_ГИСМ_Key": "00000000-0000-0000-0000-000000000000",
    // "КодТНВЭД_Key": "00000000-0000-0000-0000-000000000000",
    // "СчетНаОплатуПокупателю_Key": "00000000-0000-0000-0000-000000000000"
    // },
    // {
    // "Ref_Key": "8b9660f6-6332-11ec-ad72-000c29b45b09",
    // "LineNumber": "2",
    // "Номенклатура_Key": "2d4fe756-48a4-11ec-ad72-000c29b45b09",
    // "КоличествоМест": 0,
    // "ЕдиницаИзмерения_Key": "f40235d5-d84c-11ea-8133-0050569f62a1",
    // "Коэффициент": 1,
    // "Количество": 2,
    // "Цена": 6877,
    // "Сумма": 13754,
    // "СтавкаНДС": "НДС20",
    // "СуммаНДС": 2292.33,
    // "СчетУчета_Key": "86eff64f-d84c-11ea-8133-0050569f62a1",
    // "ПереданныеСчетУчета_Key": "00000000-0000-0000-0000-000000000000",
    // "СчетДоходов_Key": "86eff7be-d84c-11ea-8133-0050569f62a1",
    // "Субконто": "f402359a-d84c-11ea-8133-0050569f62a1",
    // "Субконто_Type": "StandardODATA.Catalog_ПрочиеДоходыИРасходы",
    // "СчетУчетаНДСПоРеализации_Key": "86eff7bf-d84c-11ea-8133-0050569f62a1",
    // "СчетРасходов_Key": "86eff7bf-d84c-11ea-8133-0050569f62a1",
    // "ДокументОприходования": "",
    // "ДокументОприходования_Type": "StandardODATA.Undefined",
    // "Себестоимость": 0,
    // "НомерГТД_Key": "00000000-0000-0000-0000-000000000000",
    // "СтранаПроисхождения_Key": "00000000-0000-0000-0000-000000000000",
    // "КиЗ_ГИСМ_Key": "00000000-0000-0000-0000-000000000000",
    // "КодТНВЭД_Key": "00000000-0000-0000-0000-000000000000",
    // "СчетНаОплатуПокупателю_Key": "00000000-0000-0000-0000-000000000000"
    // }
    // ],
    // "ВозвратнаяТара": [],
    // "Услуги": [],
    // "АгентскиеУслуги": [],
    // "ЗачетАвансов": [],
    // "ШтрихкодыУпаковок": [],
    // "Организация@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/Организация",
    // "Склад@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/Склад",
    // "Контрагент@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/Контрагент",
    // "ВалютаДокумента@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/ВалютаДокумента",
    // "СчетУчетаРасчетовСКонтрагентом@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/СчетУчетаРасчетовСКонтрагентом",
    // "СчетУчетаРасчетовПоАвансам@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/СчетУчетаРасчетовПоАвансам",
    // "СчетУчетаРасчетовПоТаре@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/СчетУчетаРасчетовПоТаре",
    // "БанковскийСчетОрганизации@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/БанковскийСчетОрганизации",
    // "Ответственный@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/Ответственный",
    // "Руководитель@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/Руководитель",
    // "СпособДоставки@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f6-6332-11ec-ad72-000c29b45b09')/СпособДоставки"
    // }



    // ,{
    // "Ref_Key": "8b9660f7-6332-11ec-ad72-000c29b45b09",
    // "DataVersion": "AAAACwAAAAA=",
    // "DeletionMark": false,
    // "Number": "0000-000002",
    // "Date": "2021-12-22T17:23:05",
    // "Posted": false,
    // "ВидОперации": "Услуги",
    // "Организация_Key": "87fa6cc2-e901-11ea-8591-000c29b45b09",
    // "Склад_Key": "f40235d3-d84c-11ea-8133-0050569f62a1",
    // "ПодразделениеОрганизации_Key": "00000000-0000-0000-0000-000000000000",
    // "ДеятельностьНаПатенте": false,
    // "Патент_Key": "00000000-0000-0000-0000-000000000000",
    // "Контрагент_Key": "b7569bf7-f670-11ea-8591-000c29b45b09",
    // "ДоговорКонтрагента_Key": "00000000-0000-0000-0000-000000000000",
    // "СпособЗачетаАвансов": "Автоматически",
    // "ТипЦен_Key": "00000000-0000-0000-0000-000000000000",
    // "ВалютаДокумента_Key": "a192de9a-d84c-11ea-8133-0050569f62a1",
    // "КурсВзаиморасчетов": 1,
    // "КратностьВзаиморасчетов": "1",
    // "СуммаВключаетНДС": true,
    // "УдалитьУчитыватьНДС": false,
    // "СчетУчетаРасчетовСКонтрагентом_Key": "86eff6fd-d84c-11ea-8133-0050569f62a1",
    // "СчетУчетаРасчетовПоАвансам_Key": "86eff6fe-d84c-11ea-8133-0050569f62a1",
    // "СчетУчетаРасчетовПоТаре_Key": "86eff76a-d84c-11ea-8133-0050569f62a1",
    // "УдалитьСчетУчетаДоходовПоТаре_Key": "00000000-0000-0000-0000-000000000000",
    // "УдалитьСчетУчетаРасходовПоТаре_Key": "00000000-0000-0000-0000-000000000000",
    // "УдалитьСтатьяДоходовИРасходовПоТаре_Key": "00000000-0000-0000-0000-000000000000",
    // "СчетНаОплатуПокупателю_Key": "00000000-0000-0000-0000-000000000000",
    // "Грузоотправитель_Key": "00000000-0000-0000-0000-000000000000",
    // "Грузополучатель_Key": "00000000-0000-0000-0000-000000000000",
    // "АдресДоставки": "",
    // "БанковскийСчетОрганизации_Key": "63ee33d4-e908-11ea-8591-000c29b45b09",
    // "СуммаДокумента": 108554,
    // "Ответственный_Key": "10f78d15-dfe4-11eb-b4ad-000c29b45b09",
    // "Комментарий": "",
    // "РучнаяКорректировка": false,
    // "Руководитель_Key": "87fa6cc8-e901-11ea-8591-000c29b45b09",
    // "ГлавныйБухгалтер_Key": "00000000-0000-0000-0000-000000000000",
    // "ОтпускПроизвел_Key": "00000000-0000-0000-0000-000000000000",
    // "УдалитьЗаРуководителяПоПриказу": "",
    // "УдалитьЗаГлавногоБухгалтераПоПриказу": "",
    // "ЗаЗаказчикаНаОсновании": "",
    // "ДоверенностьНомер": "",
    // "ДоверенностьДата": "0001-01-01T00:00:00",
    // "ДоверенностьВыдана": "",
    // "ДоверенностьЧерезКого": "",
    // "ВидЭлектронногоДокумента": "АктИсполнитель",
    // "ДокументБезНДС": false,
    // "ЗаРуководителяНаОсновании_Key": "00000000-0000-0000-0000-000000000000",
    // "ЗаГлавногоБухгалтераНаОсновании_Key": "00000000-0000-0000-0000-000000000000",
    // "Перевозчик_Key": "00000000-0000-0000-0000-000000000000",
    // "МаркаАвтомобиля": "",
    // "РегистрационныйЗнакАвтомобиля": "",
    // "Водитель": "",
    // "ВодительскоеУдостоверение": "",
    // "КраткоеНаименованиеГруза": "",
    // "СопроводительныеДокументы": "",
    // "ДеятельностьНаТорговомСборе": false,
    // "ОтветственныйЗаОформление_Key": "00000000-0000-0000-0000-000000000000",
    // "СведенияОТранспортировкеИГрузе": "",
    // "ПеревозкаАвтотранспортом": false,
    // "ЕстьМаркируемаяПродукцияГИСМ": false,
    // "НомерЧекаККМ": "0",
    // "СпособДоставки_Key": "2528e58f-e921-11ea-8591-000c29b45b09",
    // "НомерДляОтслеживания": "",
    // "МаркаПрицепа": "",
    // "РегистрационныйЗнакПрицепа": "",
    // "Товары": [],
    // "ВозвратнаяТара": [],
    // "Услуги": [
    // {
    // "Ref_Key": "8b9660f7-6332-11ec-ad72-000c29b45b09",
    // "LineNumber": "1",
    // "Номенклатура_Key": "f02ce7b6-e3b3-11eb-b4ad-000c29b45b09",
    // "Содержание": "сборка каркаса мойки",
    // "Количество": 1,
    // "Цена": 52110,
    // "Сумма": 52110,
    // "СтавкаНДС": "НДС20",
    // "СуммаНДС": 8685,
    // "СчетДоходов_Key": "86eff7ae-d84c-11ea-8133-0050569f62a1",
    // "Субконто": "f40235d6-d84c-11ea-8133-0050569f62a1",
    // "Субконто_Type": "StandardODATA.Catalog_НоменклатурныеГруппы",
    // "СчетУчетаНДСПоРеализации_Key": "86eff7b3-d84c-11ea-8133-0050569f62a1",
    // "СчетРасходов_Key": "86eff7b1-d84c-11ea-8133-0050569f62a1",
    // "СчетНаОплатуПокупателю_Key": "00000000-0000-0000-0000-000000000000"
    // },
    // {
    // "Ref_Key": "8b9660f7-6332-11ec-ad72-000c29b45b09",
    // "LineNumber": "2",
    // "Номенклатура_Key": "1e5586e1-e87f-11eb-b4ad-000c29b45b09",
    // "Содержание": "сборка каркаса мойки",
    // "Количество": 1,
    // "Цена": 56444,
    // "Сумма": 56444,
    // "СтавкаНДС": "НДС20",
    // "СуммаНДС": 9407.33,
    // "СчетДоходов_Key": "86eff7ae-d84c-11ea-8133-0050569f62a1",
    // "Субконто": "f40235d6-d84c-11ea-8133-0050569f62a1",
    // "Субконто_Type": "StandardODATA.Catalog_НоменклатурныеГруппы",
    // "СчетУчетаНДСПоРеализации_Key": "86eff7b3-d84c-11ea-8133-0050569f62a1",
    // "СчетРасходов_Key": "86eff7b1-d84c-11ea-8133-0050569f62a1",
    // "СчетНаОплатуПокупателю_Key": "00000000-0000-0000-0000-000000000000"
    // }
    // ],
    // "АгентскиеУслуги": [],
    // "ЗачетАвансов": [],
    // "ШтрихкодыУпаковок": [],
    // "Организация@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/Организация",
    // "Склад@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/Склад",
    // "Контрагент@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/Контрагент",
    // "ВалютаДокумента@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/ВалютаДокумента",
    // "СчетУчетаРасчетовСКонтрагентом@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/СчетУчетаРасчетовСКонтрагентом",
    // "СчетУчетаРасчетовПоАвансам@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/СчетУчетаРасчетовПоАвансам",
    // "СчетУчетаРасчетовПоТаре@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/СчетУчетаРасчетовПоТаре",
    // "БанковскийСчетОрганизации@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/БанковскийСчетОрганизации",
    // "Ответственный@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/Ответственный",
    // "Руководитель@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/Руководитель",
    // "СпособДоставки@navigationLinkUrl": "Document_РеализацияТоваровУслуг(guid'8b9660f7-6332-11ec-ad72-000c29b45b09')/СпособДоставки"
    // }]

    public function __construct()
    {
        parent::__construct();

        $this->table('acts');
        $this->has_files(true);
        $this->has_images(false);
        $this->has_groups(false);
        $this->table_type('document');
        $this->icon('mdi-human-dolly');


        // модель
        $this->model([
            ["name" => "order_id", "type" => "key", "table" => "orders", "table_class" => "Order", "title" => "Заказ", "require" => false, "default" => 1, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            ["name" => "sklad_id", "type" => "select", "table" => "sklads", "table_class" => "Sklad", "title" => "Склад", "require" => true, "default" => 1, "index" => "index", "show_in_table" => true, "out_index" => 3],
            ["name" => "period_start_date", "type" => "date", "title" => "Дата начала периода", "require" => false, "index" => "index", "default" => date("Y-m-d"), "show_in_table" => false, "show_in_form" => false],
            ["name" => "period_end_date", "type" => "date", "title" => "Дата окончания периода", "require" => false, "index" => "index", "default" => date("Y-12-31"), "show_in_table" => false, "show_in_form" => false],
            ["name" => "summa", "type" => "money", "title" => "Сумма", "require" => false, "default" => 0, "index" => "index", "show_in_table" => false, "show_in_form" => false, "readonly" => true],
            ["name" => "summa_nds", "type" => "money", "title" => "Сумма НДС", "require" => false, 'default' => 0, "index" => "index", "show_in_table" => false, "show_in_form" => false],
            // виртуальные столбцы
            ["name" => "kontragent", "type" => "string", "virtual" => true, "title" => "Контрагент", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "firm", "type" => "string", "virtual" => true, "title" => "Организация", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "contract_type", "type" => "string", "virtual" => true, "title" => "Вид договора", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "order", "type" => "string", "virtual" => true, "title" => "Заказ", "require" => false, "default" => 0, "index" => "index", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            ["name" => "sum", "type" => "money", "virtual" => true, "title" => "Сумма", "show_in_table" => true, "show_in_form" => false, "readonly" => true],
            // фильтры
            // ["name" => "order_.contract_.contractable", "type" => "morph", "tables" => [["table" => "kontragents", "title" => "Контрагенты", "type" => "App\\Kontragent"]], "filter" => true, "virtual" => true, "title" => "Контрагент", "show_in_table" => false, "show_in_form" => false],

        ]);

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['order', 'kontragent', 'firm', 'contract_type', 'sklad', 'sum', 'ddate']);

        // подчиненные таблицы
        $this->sub_tables([
            ["table" => "act_items", "class" => "ActItem", "method" => "items", "title" => "Позиции накладной", "item_class" => "App\ActItem", "belongs_method" => "act", "keys" => ["foreign" => "act_id", "references" => "id", "foreign_table" => "act_items", "reference_table" => "acts"]],
        ]);
    }

    // связи
    // позиции накладной
    public function items()
    {
        return $this->hasMany('App\ActItem');
    }
    // заказ
    public function order_()
    {
        return $this->belongsTo('App\Order', 'order_id');
    }
    // склад
    public function sklad_()
    {
        return $this->belongsTo('App\Sklad', 'sklad_id');
    }

    // читатели
    // форматированная дата документа
    public function getDdateAttribute()
    {
        $doc_date = Carbon::createFromFormat('Y-m-d', $this->doc_date);
        return $doc_date ? $doc_date->format('d.m.Y') : $this->doc_date;
    }
    // заказ
    public function getOrderAttribute()
    {
        if (isset($this->order_id)) {
            $order = $this->order_()->first();
            return $order ? $order->getSelectListTitleAttribute() : '';
        }
        return '';
    }
    // склад
    public function getSkladAttribute()
    {
        if (isset($this->sklad_id)) {
            $sklad = $this->sklad_()->first();
            // dd($sklad->getSelectListTitleAttribute());
            return $sklad ? $sklad->getSelectListTitleAttribute() : '';
        }
        return '';
    }
    // организация
    public function getFirmAttribute()
    {
        $order = $this->order_()->first();
        if ($order) {
            return $order->firm;
        }
        return '';
    }
    // контрагент
    public function getKontragentAttribute()
    {
        $order = $this->order_()->first();
        if ($order) {
            return $order->kontragent;
        }
        return '';
    }
    // вид договора
    public function getContractTypeAttribute()
    {
        $order = $this->order_()->first();
        if ($order) {
            return $order->contract_type;
        }
        return '';
    }
    // сумма реализации
    public function getSumAttribute()
    {
        $items = collect($this->items()->get());
        // dd($items->toArray());
        return $items->sum('summa');
    }

    // печатные формы
    public function pf_data()
    {
        // табличная часть
        $table_items = $this->items;
        // дата документа
        $doc_date = Carbon::createFromFormat('Y-m-d', $this->doc_date);
        // начальные данные
        $table_data_arr = [
            "status" => 1, //_N0
            "doc_num" => $this->doc_num, //_N1
            "doc_date" => $doc_date->format('d.m.Y'), //_N2
            "saler" => '', //_N4
            // пустые по умолчанию
            'saler_addr' => '',
            'saler_inn_kpp' => '',
            'saler_go_addr' => '',
            'saler_addr' => '',
            'saler_addr' => '',
            'buyer_gp_addr' => '',
            'buyer_pp_num' => '',
            'buyer_pp_date' => '',
            'buyer' => '',
            'buyer_addr' => '',
            'buyer_inn_kpp' => '',
            'valuta' => '',
            'podpis_ceo' => '',
            'podpis_account' => '',
            'ip_fio' => '',
            'ip_ogrnip' => '',
            'doverennost' => '',
            'manager_firm_position' => '',
            'manager_fio' => '',
            'keeper_fio' => '',
            'keeper_firm_position' => '',
            'out_date_date' => '',
            'out_date_month' => '',
            'out_date_year' => '',
        ];
        $order = $this->order_()->first();
        if ($order) {
            $contract = $order->contract_()->first();
            if ($contract) {
                // организация
                $firm = $contract->firm_()->first();
                if ($firm) {
                    $table_data_arr["saler"] = $firm->short_name; // _N4
                    $table_data_arr["saler_addr"] = ''; // _N5
                    $table_data_arr["saler_inn_kpp"] = $firm->inn . ' / ' . $firm->kpp; // _N6
                    $table_data_arr["saler_go_addr"] = $firm->short_name . ' '; // _N7
                    $table_data_arr["saler_addr"] = ''; // _N5
                }
                // контрагент
                $kontragent = $contract->contractable()->first();
                if ($kontragent) {
                    $table_data_arr["buyer_gp_addr"] = $kontragent->full_name . ' ' . $kontragent->address; //_N8
                    $table_data_arr["buyer_pp_num"] = ''; //_N9
                    $table_data_arr["buyer_pp_date"] = ''; //_N10
                    $table_data_arr["buyer"] = $kontragent->full_name; //_N11
                    $table_data_arr["buyer_addr"] = $kontragent->address; //_N12
                    $table_data_arr["buyer_inn_kpp"] = $kontragent->inn . ' / ' . $kontragent->kpp; //_N12
                }
            }
            $table_data_arr["valuta"] = 'руб.'; // _N14
        }
        // кол-во листов
        $table_data_arr["pages_count"] = ''; //_N29
        // подписанты
        // руководитель
        $table_data_arr["podpis_ceo"] = ''; //_N30
        $table_data_arr["podpis_account"] = ''; //_N31
        $table_data_arr["ip_fio"] = ''; //_N32
        $table_data_arr["ip_ogrnip"] = ''; //_N33
        $table_data_arr["doverennost"] = ''; //_N34
        // ответственный
        $table_data_arr["manager_firm_position"] = ''; //_N40
        $table_data_arr["manager_fio"] = ''; //_N41

        // складарь
        $sklad = $this->sklad_()->first();
        if ($sklad) {
            $keeper = $sklad->keeper()->first();
            if ($keeper) {
                $table_data_arr["keeper_fio"] = $keeper->short_fio; //_N36
                $table_data_arr["keeper_firm_position"] = $keeper->firm_position; //_N35
            }
        }
        // дата отгрузки
        $table_data_arr["out_date_date"] = $doc_date->format('d'); //_N37
        $table_data_arr["out_date_month"] = $doc_date->translatedFormat('F'); //_N37
        $table_data_arr["out_date_year"] = $doc_date->translatedFormat('y'); //_N39

        // табличная часть
        $items = [];
        // итоговая часть
        $itogs = [
            "summa" => 0,
            "sum_nds" => 0,
            "sum_sum" => 0
        ];

        // № п/п
        $npp = 1;
        foreach ($table_items as $item) {
            $row = [
                "npp" => $npp, //_N14
            ];
            $nomenklatura = $item->nomenklatura_()->first();
            if ($nomenklatura) {
                $row["code"] = $nomenklatura->artikul; //_N15
                $row["name"] = $nomenklatura->name; //_N16
                $row["code_tov"] = ''; //_N17
                // единица измерения
                $ed_ism = $nomenklatura->ed_ism_()->first();
                if ($ed_ism) {
                    $row["ed_ism_code"] = $ed_ism->okei; //_N18
                    $row["ed_ism"] = $nomenklatura->ed_ism; //_N19
                }
            }
            $row["kolvo"] = $item->kolvo; //_N20
            $row["price"] = $item->price; //_N21
            $row["summa"] = $item->summa; //_N22
            // НДС
            $nds = $item->nds_()->first();
            if ($nds) {
                $row["stavka_nds"] = $nds->comment; //_N23
            }
            $row["sum_nds"] = $item->summa_nds; //_N24
            $row["sum"] = $item->summa_nds + $item->summa; //_N25
            // суммы
            $itogs["summa"] += $row["summa"]; //_N26
            $itogs["sum_nds"] += $row["sum_nds"]; //_N27
            $itogs["sum_sum"] += $row["sum"]; //_N28
            // добавляем строку в табличную часть
            $items[] = (object)$row;
            // инкремент #п/п
            $npp++;
        }
        // формируем результат
        $res = [
            "doc" => $table_data_arr,
            "table" => $items,
            "itogs" => $itogs
        ];
        // dd($res);
        return $res;
    }
}