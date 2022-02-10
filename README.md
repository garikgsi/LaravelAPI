<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## Язык запросов API

<p>GET /api/v1/table*name - вывод 1-й страницы данных (по умолчанию сортируется по столбцу name, 10 строк на странице)</p>
<p>GET /api/vi/table_name/N - вывод всех полуй записи таблицы table_name с id=N</p>
<p>GET /api/v1/table_name?odata=[full|data|model|list|count] - формат вывода данных data - только данные, model - только модель столбцов таблицы,
full - данные и модель, list - список в виде массива ['id'=>'N', 'title'=>'template']
(формат вывода list определяется методом listFormat класса ABPTable, в случае неверно
указанного шаблона будем передавать таблицу целиком), count - только посчитать записи
</p><p>Параметры фильтрации в запросе GET:<br/>
&fields=fieldName1,fieldName2,...,fieldNameN - вывод только перечисленных столбцов таблицы<br/>
&order=id,[desc|asc] - сортировка выдачи: поле,порядок сортировки<br/>
&filter=fieldName1[lt|gt|eq|ne|ge|le|like]filterValue1 [or|and] fieldName2[lt|gt|eq|ne|ge|le|like]filterValue1 -</p>
<p>доступные операнды:<br/>
lt => меньше<br/>
gt => больше<br/>
eq => равно<br/>
ne => не равно<br/>
ge => больше или равно<br/>
le => меньше или равно<br/>
like => like<br/>
in => входит в массив (IN)<br/>
ni => не входит в массив (NOT IN)<br/>
morphin => принадлежит массиву и соответствует полиморфной связи<br/>
morphni => принадлежит массиву и соответствует полиморфной связи<br/>
morph => равняется id и соответствует полиморфной связи<br/>
!! к операнду like значение обрамляется %% с обеих сторон</p>
<p>доступные условия:<br/>
or => ИЛИ<br/>
and => И</p>
<p>!!невозможно указывать условия, обрамленные в скобки
<p>!! для полиморфных условий необходимо соблюсти синтаксис filterValue: ["App\\Kontragent"].[734,755,743,327] - второй параметр
точечной нотации должен содержать массив в любом случае, даже если указан операнд morph и/или единственное значение. Пример:<br/>
/contracts?filter=contractable morphin ["App\\Kontragent"].[734]</p>
<p>----</p>
<p>в качестве fieldName можно указывать связи таблиц,разделенные точками, например, acts?filter=order*.contract*.contract_type_id in [2,5,7]
в примере из модели соответствующей таблице acts будет выбрана связь order*, далее из модели Order выбирается связь contract*,
в котором уже ищется поле contract_type_id, которое и фильтруется в соответствии с операндами.
если необходимо отфильтровать по группам - в качестве последней связи необходимо указывать значение groups, например, следующий пример
выберет из актов только те, позиции которых содержат заданные группы номенклатур: acts?filter=items.nomenklatura*.groups in [36,2]
здесь сначала вызываеся метод items, получающий позиции накладной, потом применяется связь nomenklatura* из модели ActItem, затем
номенклатура фильтруется по группам. !Группы применяются к последнему параметру, перед ключевым параметром 'groups'!
Для фильтрации полиморфных полей необходимо использовать в качестве последнего параметра точечной нотации значение morph-поля, например<br/>
acts?filter=order*.contract\_.contractable morphin ["App\\Kontragent"].[734,755] <br/>В результате примера получаем все накладные, которые указаны
в качестве contractable-поля модели Contract, как контрагенты с id == [734,755]</p>
<p>----</p>
<p>&search=text - поиск по всем возможным полям<br/>
&tags=id1,id2,...,idN - дополнительный фильтр по тегам (выбор должен содержать строки имеющий хотя бы 1 тег)<br/>
&extensions=ext1,ext2,...,extN - добавить в ответ расширения для записи из возможных [files,images,groups,file_list,main_image,select_list_title]<br/>
&scope=stock_balance.9, - добавить в запрос scope. Параметры передаются через точки, скопы разделяются запятыми<br/>
&offset - смещение относительно 0-го элемента выдачи, отсортированного согласно правилам сортировки (только совместно с limit)<br/>
&limit - количество выдаваемых значений выдачи (-1 для отсутствия лимитов)<br/>
&trashed=1 - выдавать помеченные на удаление записи
</p><p>
POST /api/v1/table_name - добавление записи в таблицу table_name. Ответ при успехе - 201 и вставленная запись в объекте data, в случае ошибки - 500</p>
<p>PUT|PATCH /api/v1/table_name/N - изменение записи с id=N в таблице table_name. В ответе count сервер вернет кол-во измененных записей</p>
<p>PATCH /api/v1/table_name/N/post - проводим документ с id=N в таблице table_name. В запросе необходимо передать массив полей для проведения (в моделе должны быть отмечены признаком "post"=>true). В ответе count сервер вернет измененную запись</p>
<p>DELETE /api/v1/table_name/N - удаление записи с id=N в таблице table_name. В ответе count сервер вернет кол-во удаленных записей или true</p>
<p>----</p>
<p>Формат ответа сервера:<br/>
{<br/>
"is_error": false, /_ булево поле наличия ошибки _/<br/>
"error": "", /_ текстовое описание ошибки _/<br/>
"count": 4, /** количество записей в таблице соответствующих запросу \*/<br/>
"data": [], /** массив объектов данных _/<br/>
"time_request": "0.596 sec", /\*\* справочно - время выборки данных _/<br/>
"model": [] /\*_ модель структуры таблицы, если передан параметр odata _/<br/>
}</p>
<p>----</p>
<p>Коды ответов сервера:</p>
<ul>
<li>200 OK - самый часто используемый код, свидетельствующий об успехе операции;
<li>201 CREATED - используется, когда с помощью метода POST создается ресурс;
<li>202 ACCEPTED - используется, чтобы сообщить, что ресурс принят на сервер (запись обновлена);
<li>400 BAD REQUEST - используется, когда со стороны клиента допущена ошибка в вводе;
<li>401 UNAUTHORIZED / 403 FORBIDDEN - используются, если для выполнения операции требуется аутентификация пользователя или системы;
<li>404 NOT FOUND - используется, если в системе отсутствуют искомые ресурсы;
<li>422 - переданы неверные данные для внесения изменений в БД или неверная логика (ошибка триггера и т.п.)
<li>500 INTERNAL SERVER ERROR - это никогда не используется просто так - в таком случае произошла ошибка в системе;
<li>502 BAD GATEWAY - используется, если сервер получил некорректный ответ от предыдущего сервера.
</ul>
