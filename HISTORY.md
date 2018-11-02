## 2.4.1 (02.11.2018)
* [Fix] Исправлена фатальная ошибка допущенная в версии 2.3.16 (Issue #317)

## 2.4.0 (01.11.2018)
* [Add] Добавлена поддержка шаблонизатора Blade 
* [Add] Поддержка массивов параметром prepare (PR #323)
* [Add] Добавлен фильтр isnotnull (PR #330)
* [Add] Метод AssetsHelper::registerScript теперь поддержкивает подключение удаленных скриптов, путь к которым начинается с //
* [Fix] Метод AssetsHelper::registerScript некорректно подключал файлы в имени которых встречалось http
* [Fix] Корректная работа фильтров для NULL значений в выборке (PR #330)
* [Fix] На уровне чанка не работали глобальные плейсхолдеры (Issue #321)
* [Fix] Убрано экранирование (добавленно при решении Issue #276) WHERE услоий (Issue #322)
* [Fix] Обработка "(, ), ;" в фильрах. Решение может вызвать конфликт обратной совместимости из-за смены имен подключаемых таблиц (Issue #276)
* [Fix] Если фильтр containsOne получает пустой список, то на выходе получался некорректный SQL запрос (PR #328)
* [Refactor] Класс DLTemplate теперь не использует метод $modx->getChunk()

## 2.3.17 (08.08.2018)
* [Fix] Ошибка в modResource::checkAlias.
* [Fix] Ошибка в методе checkVersion.
* [Fix] Не выполнялись сниппеты из prepareWrap (Issue #317).
* [Fix] Утечка памяти в DLPHx.
* [Fix] Исправлена ошибка SQL в strict-режиме.
* [Fix] Некорректный вывод плейсхолдера dl.full_iteration.
* [Add] Перевод на японский для лексикона paginate.
* [Refactor] Уменьшено количество запросов в классе modUsers (Issue #309).
* [Fix] Неправильная установка локали в лексиконе paginate.
* [Refactor] Метод cleandIDs перенесен в APIHelpers (Issue #308).
* [Fix] Некорректная проверка существования поля в modResource.
* [Add] Параметр disablePHx для отключения PHx в DLTemplate.
* [Add] Поддержка плагина MultiCategories.

## 2.3.15 (28.04.2018)

* [Fix] Не выводились значения tv-параметров по умолчанию в DocLister (Issue #307).
* [Fix] Ошибка при сохранении tv-параметров в modResource.
* [Fix] Ошибка при редактировании tv-параметров  в modResource (Issue #304).
* [Enhancement] Дополнительные лексиконы для js-компонентов.
* [Fix] Ошибка в классе DLphx при использовании условий (Issue #297).
* [Refactor] В DLSitemap используется функция date, если формат даты не задан, иначе strftime.
* [Enhancement] Кэширование данных в DocLister и DLMenu при использовании evoTwig (Issue #298, Issue #303). 
* [Fix] Ошибка в контроллере site_content_tags при отладке (Issue #300).
* [Fix] Ошибка при проверке на уникальность в методе checkUnique после обновления DBAPI.

## 2.3.14 (02.03.2018)
* [Refactor] Метод parseChunk в DocLister можно вызывать с одним аргументом.
* [Add] Методы setRememberTime, getRemeberTime в modManagers.
* [Fix] Ошибка при сортировке по количеству комментариев Jot в контроллере shopkeeper.
* [Refactor] Инициализация DLTemplate теперь в конструкторе класса.
* [Enhancement] Поддержка параметра documents в DLMenu.
* [Refactor] Запросы в modUsers вынесены в отдельные методы для упрощения расширения класса (Issue #275).
* [Fix] Некорректная работа параметра start (Issue #287).
* [Enahancement] Поддержка параметра dateFormat в сниппете DLSitemap.
* [Refactor] Обработка изменения session id в modManagers.
* [Fix] Некорректная обработка куки для автологина в modUsers.
* [Add] Польский язык.
* [Enahancement] Helpers\Mailer обрабатывает код письма с помощью метода msgHtml, если включен параметр isHtml.

## 2.3.13 (19.01.2018)
* [Enhancement] Параметр ouputSeparator для разделения документов при выводе.
* [Refactor] Поддержка фильтров в контроллере onetable.
* [Fix] Ошибки в js-компоненте EUIGrid.
* [Fix] Метод loadArray в Helpers\Config не возвращал данные, заданные в виде массива.
* [Enhancement] Поддержка польского языка в js-компонентах.
* [Enhancement] Параметр paginationMeta для добавления мета-тегов link rel="next" и link rel="prev".
* [Enhancement] Валидация данных в APIhelpers::getkey.
* [Enhancement] Локализация размеров в Helpers\FS::fileSize.
* [Fix] Некорректная проверка параметра parents на пустоту.
* [Fix] Параметр pageLimit.

## 2.3.12 (14.11.2017)
* [Fix] Метод loadArray в Helpers\Config мог вернуть null.
* [Fix] Обратная совместимость в Helpers\Mailer.
* [Refactor] Cookie в modUsers/modManagers устанавливаются с учетом константы MODX_BASE_URL.
* [Fix] Опечатки в modManagers.
* [Enhancement] Поддержка голландского языка в js-компонентах.
* [Refactor] Возможность вручную или с помощью автозагрузчика загружать классы для фильтрации. 
* [Fix] Вывод classes="" в DLMenu.
* [Fix] Предупреждения в PHP 7.
* [Fix] Определение версии Evo для загрузки jQuery в \Helpers\Assets.
* [Fix] Неверное значение глобальных плейсхолдеров from и to (Issue #278).
* [Fix] Неверное значение глобального плейсхолдера current (Issue #277).
* [Refactor] Обработка (, ), ; в фильтрах (Issue #276).
* [Refactor] Имена сниппетов приведены к единому виду.
* [Fix] Ошибка заполнения плейсхолдера [+date+] (Issue #274).
* [Refactor] Список полей в modResource приведен в соответствие с изменениями в Evo 1.3.6 (Issue #268).

## 2.3.11 (21.08.2017)
* [Refactor] Сортировка по количеству комментариев при включенном экстендере jot_count (Issue #273).
* [Refactor] Возможность перезаписывать массив настроек методом \Helpers\Config::setConfig.
* [Refactor] Метод getPK (DocLister) может возвращать значение как с алиасом таблицы, так и без него.
* [Add] \Helpers\Mailer - возможность сохранить письмо в файл и загрузить из файла.

## 2.3.10 (09.08.2017)
* [Fix] Ошибки при формировании запросов в контроллере Onetable.
* [Add] Возможность задавать значения по умолчанию для json-полей в MODxAPI (Issue #198).

## 2.3.9 (07.08.2017)
* [Fix] Сортировка по TV в DLMenu.
* [Refactor] Issue #272.
* [Refactor] Изменено формирование плейсхолдера iteration в DLMenu, теперь он доступен и в prepare.
* [Add] Плейсхолдер \_display в DLMenu, показывает количество документов в текущей ветке.

## 2.3.8 (06.08.2017)
* [Add] Метод setDocumentGroups в modResource для привязки создаваемых документов к группам (Issue #71).
* [Refactor] Методы getDocumentGroups (modResource) и getUserGroups (modUsers, modManagers) возвращают результат в виде ассоциативного массива "id группы => имя группы" (Issue #270).
* [Refactor] Метод setUserGroups (modUsers, modManagers) удаляет группы, которых нет в заданном списке (Issue #271).
* [Add] В modResource проверяется при сохранении принадлежность tv-параметров к заданному шаблону (Issue #73).
* [Add] Обратная пагинация в режиме offset.
* [Fix] Неверное вычисление dl.full_iteration в режиме обратной пагинации.
* [Refactor] Изменен алгоритм сохранения tv-параметров (Issue #235).

## 2.3.7 (05.08.2017)
* [Add] Обратная пагинация (параметр reversePagination) (Issue #130).
* [Add] Возможность задать максимальное число выводимых документов (параметр maxDocs) (Issue #166).
* [Refactor] Поля в json-массиве в режиме api (Issue #246).
* [Refactor] Метод APIHelpers::_getEnv переименован в getEnv и изменен на публичный (Issue #244).
* [Fix] Некорректная работа метода \Helpers\FS::relativePath в Windows (Issue #267).
* [Fix] Ошибка в DLMenu при включенном параметре hideSubMenus.

## 2.3.6 (23.07.2017)
* [Add] Сниппет DLSitemap для построения xml-карты сайта.
* [Refactor] Убран сниппет DLBuildMenu.
* [Refactor] Убран сниппет DLUsers.

## 2.3.5 (18.07.2017)
* [Refactor] Лишнее условие (Issue #249).
* [Fix] Убрано бессмысленное значение по умолчанию параметра groupBy (c.id).
* [Refactor] Переделано задание плейсхолдера [+date+]: задается только при наличии источника даты, а в контроллере site_content при отсутствии даты pub_date используется дата createdon.
* [Add] Модель modManagers для работы с менеджерами.
* [Add] Сниппет DLMenu для вывода меню.

## 2.3.4 (03.07.2017)
* [Fix] Возможность запуска prepare-сниппета с пустым именем.
* [Fix] Исправлен файл .gitignore.
* [Refactor] modResource: возможность работать с json-полями (как в autoTable).
* [Refactor] modResource: преобразование значений TV-параметров, в которых хранятся массивы в виде строки с разделителем ||. Имена TV-параметров или задаются в свойстве tvaFields или определяются автоматически по типу параметра (checkbox или listbox-multiple).

## 2.3.3 (27.06.2017)
* [Fix] Исправлены ошибки.

## 2.3.2 (23.06.2017)
* [Add] Тема modx для Easy UI - для Evolution 1.2.1.
* [Refactor] Обновление Easy UI до версии 1.5.2.
* [Fix] Некорретная загрузка параметров в DLBuildMenu и DLCrumbs.
* [Refactor] В параметре sanitarTags можно указывать список полей через запятую.
* [Refactor] Возможность переопределить методы checkAlias и getAlias в modResource.
* [Refactor] Оптимизация работы с tv-параметрами в modResource.
* [Add] Редирект в пагинаторе с doc.html?page=1 на doc.html.
* [Refactor]  При расширении контроллера onetable имя таблицы можно указать в свойстве table.
* [Add] Поддержка конфигов в DLCrumbs и DLBuildMenu.
* [Fix] Ошибка при выполнении редиректа в пагинаторе.
* [Refactor] Убрано сообщение о том, что параметр tpl пуст.
## 2.3.1 (09.05.2017)
* [Refactor] В параметре dateSource можно указывать tv-параметр.
* [Add] Параметр sanitarTags для экранирования тэгов MODX при выводе.
* [Refactor] Оптимизация подсчета количества документов в контроллере site_content.
* [Fix] Ошибка при вызове метода APIHelpers::_getEnv (Issue #237).
* [Fix] Ошибка в методе copy MODxAPI.
* [Fix] Неверное определенности четности номера документа при выводе. 
* [Refactor] Не запрашивать дочерние документы, если текущий документ не является папкой в DLBuildMenu.
* [Add] Возможность приведения расширения файла к нижнему регистру при вызове метода Heplers\FS::takeFileExt.
* [Add] Параметр makePaginateUrl, задающий функцию для формирования ссылок в пагинаторе (Issue #238).
* [Refactor] Уменьшение количества запросов в MODxAPI.
* [Refactor] Вызов метода decodeFields() в классе autoTable после сохранения данных (Issue #228).
* [Refactor] Переделан загрузчик шаблонов для Twig (Issue #231).
* [Fix] Ошибка при загрузке конфигов.

## 2.3.0 (12.11.2016)
* [Fix] Игнорирование системной настройки udperms_allowroot в modResource (Issue #69).
* [Fix] Новые шаблоны в пагинаторе влияют на вид сайта при обновлении; сейчас по умолчанию содержат пустые значения (Issue #210). 
* [Refactor] Более безопасная работа с кукой автологина в modUsers (Issue #211, #213, #215).
* [Fix] Лишние пробелы в шаблонах пагинатора.
* [Add] Возможность задавать путь к папке с файлами шаблонов и расширение файла с шаблоном (параметры &templatePath, &templateExtension в DocLister, методы setTemplatePath() и setTemplateExtension() в DLTemplate) (Issue #216).
* [Add] Поддержка шаблонизатора Twig в чанках, обрабатываемых DLTemplate, при установленном плагине [EvoTwig](https://github.com/Pathologic/EvoTwig)
* [Fix] Языковые плейсхолдеры обрабатывались только в шаблонах пагинатора (Issue #219)
* [Refactor] Оптимизация классов MODxAPI: запросы на создание/обновление теперь выполняются только для измененных данных. При редактировании документа можно отменить изменения методом rollback().
* [Fix] Отсутвуют поля city, street в modUsers.
* [Add] Новый метод setDefaultTemplate в modResource для назначения шаблона согласно системному параметру auto_template_logic (Issue #112).

## 2.2.0 (10.10.2016)
* [Fix] При вызове DocLister с параметрами &makeUrl=0 &api=1 не получается отключить формирование ссылок (Issue #171).
* [Fix] modResource::delete не удаляет дочерние ресурсы (Issue #184).
* [Fix] Неверное определение пути к контроллерам в DocLister (Issue #199).
* [Fix] Не задано поле city в modUsers.
* [Fix] Некорректная работа плагинов, вызванных из modResource, если в плагинах выполнялся метод save().
* [Add] Возможность в DocLister указывать несколько тэгов в режиме static через разделитель в параметре tagsSeparator.
* [Add] Метод modUsers::setUserGroups для помещения пользователя в группы.
* [Fix] Ошибка в DocLister при фильтрации по TV-параметрам и режиме сортировки doclist (Issue #189).
* [Add] При вызове событий в классах MODxAPI в плагины передается объект класса, из которого было вызвано событие.
* [Add] Вызов событий в классе modUsers.
* [Add] Метод getInvokeEventResult для получения результатов работы плагинов при вызове из MODxAPI.
* [Fix] Ошибка в modUsers при вызове метода getUserGroups.
* [Fix] Ошибка в modUsers при вызове метода getUserGroups.
* [Fix] Некорректная установка поля published в modResource.
* [Refactor] Метод Assets/Helpers::registerScript возвращает пустую строку вместо false.
* [Add] Метод Assets/Helpers::registerJQuery.
* [Add] Загрузка скриптов из массива в Assets/Helpers.
* [Fix] Бесконечная переадресация в плагине DLLogout
* [Fix] Плагин DLLogout загружал неверный файл.
* [Add] Вывод ссылок на первую и последнюю страницу в пагинаторе DocLister. 
* [Add] Методд MODxAPI checkUnique теперь позволяет проверять уникальность записи по нескольким полям.
* [Add] В класс Helpers/Assets добавлена возможность загружать скрипты, расположенные на удаленных серверах (Issue #161).
* [Add] Микроразметка в шаблонах DLCrumb.
* [Refactor] Модули, плагины и TV-параметры перенесены из основной ветки компонента в ветку full.

## 2.1.30 (20.12.2015)
* [Add] При помощи класса [Formatter\SqlFormatter](https://github.com/jdorn/sql-formatter) добавлена подсветка SQL запросов в выводе отладчика DocLister
* [Add] При помощи класса Formatter\HtmlFormatter добавлена подсветка имен шаблонов, лексиконов и плейсхолдеров
* [Add] В методе APIhelpers::sanitarTag теперь можно подменять заменяемые символы
* [Add] В методе APIhelpers::sanitarTag теперь можно отключить преобразование в html сущности не modx зарезервированных символов
* [Add] В класс Helpers\FS добавлен метод unlink для удаления файлов
* [Add] В метод Helpers\FS добавлен универсальный метод delete для удаления файла/каталога
* [Add] В класс jsonHelper добавлен метод toJSON, для создания отформатированной JSON строки
* [Add] Новый параметр minDocs для сниппета DLcrumbs (Issue #160)
* [Fix] Метод DocLister::renderWrap больше не игнорирует параметр сниппета noneWrapOuter, если доки отфильтрованы через prepare (Issue #155)
* [Refactor] Метод Helpers\FS::rmDir теперь вовзарщает флаг со статусом удаления папки
* [Refactor] В экстендер TV добавлен флаг определяющий нужно ли сохранять сгенерированные псевдонимы таблиц или нет

## 2.1.29 (14.11.2015)
* [Refactor] Картинка создаваемая при помощи класса Helpers\PHPThumb теперь сохраняет тип файла, если не задан специфичный формат.

## 2.1.28 (13.11.2015)
* [Fix] Исправлено игнорирование параметра $hideMain в сниппете DLcrumbs

## 2.1.27 (04.11.2015)
* [Add] В класс Helpers\PHPThumb добавлен метод для оптимизации картинки через консльную утилиту jpegtran

## 2.1.26 (18.08.2015)
* [Fix] Исправлена ошибка с удалением не пустой папки

## 2.1.25 (05.08.2015)
* [Fix] Некорректная загрузка prepare экстендера  (Issue #147)
* [Fix] Некорректное определение ID текущего документа (Issue #146)
* [Add] Добавлен фильтр по ресурсам с ограничением прав для веб-пользователя (Issue #153)
* [Add] Добавлен метод getTitle в класс modResource (Issue #70)

## 2.1.24 (03.08.2015)
* [Add] Добавлен параметр PaginateClass для смены класса пагинатора

## 2.1.23 (14.06.2015)
* [Add] Добавлен параметр activeClass для сниппета DLBuildMenu
* [Add] JS-cкрипт Sortable обновлен до версии 1.2.0
* [Add] Вывод шаблона empty.tpl в SimpleTab-плагинах при создании документа
* [Add] Метод checkPermissions для проверки разрешений в SimpleTab-плагинах
* [Add] Возможность изменять свойства класса \SimpleTab\Plugin с помощью события OnParseProperties
* [Add] Методы для удаления записей, перемещения записей в пределах документа, изменения порядка записей добавлены в абстрактные классы \SimpleTab\AbstractController и \SimpleTab\autoTable
* [Add] Метод для получения кода языка в \SimpleTab\AbstractController
* [Fix] Исправлена возможная ошибка в SQL запросе при фильтрации DocLister
* [Fix] Исправлена ошибка в SQL запросе при изменении порядка записей в \SimpleTab\autoTable
* [Refactor] \SimpleTab\autoTable - название поля, в котором хранится порядковый номер записи, задается в свойстве indexName и используется в методах, меняющих порядок записей 
* [Refactor] Параметры для запуска DocLister в \SimpleTab\AbstractController вынесены в отдельное свойство dlParams


## 2.1.22 (13.05.2015)
* [Add] Добавлен модификатор default для PHX

## 2.1.21 (04.05.2015)
* [Add] Добавлен метод getUrl в класс modResource
* [Add] Добавлен модификатор empty в класс DLPhx для поддержки then/else модификаторов

## 2.1.20 (30.04.2015)
* [Fix] Обработка магических кавычек в MODxAPI отключена
* [Fix] Отключен вывод обертки пагинатора при пустом списке страниц

## 2.1.19 (29.03.2015)
* [Refactor] Игнорирование главной страницы в DLcrumbs

## 2.1.18 (25.03.2015)
* [Fix] Некорректный подсчет номера следующей и предыдущей страницы в пагинаторе DocLister'a

## 2.1.17 (21.03.2015)
* [Fix] Значение по умолчанию для error_page в конструкторе DLUsers\Actions
* [Add] Новый метод DocLister::docsCollection
* [Add] Пагинация в стиле Ditto (Issue #129)

## 2.1.16 (16.03.2015)
* [Add] Новый параметр addNopValue к сниппету DLValueList
* [Fix] Использование ignoreEmpty в связке с idType=parents в сниппете DocLister

## 2.1.15 (24.02.2015)
* [Add] Сброс кеша определенного документа через MODxAPI

## 2.1.14 (18.02.2015)
* [Fix] В контроллере onetable исправлен некорретный SQL запрос для вычисления общего числа записей в пагинаторе
* [Fix] Некорректная работа контроллера shopkeeper с пагинацией и групировкой
* [Add] Добавлены новые методы для работы с массивом параметров в DocLister
* [Refactor] Отказ финальных методов

## 2.1.13 (11.02.2015)
* [Add] Разбиение фильтра на субфильтры с учётом вложенности (PR #122)

## 2.1.12 (25.01.2015)
* [Fix] Некорректная работа с экстендером request в методах getUrl
* [Fix] Игнорирование родителя с параметром showParent=1
* [Add] Поддержка новых шаблонов в сниппете DLBuildMenu для активного пункта

## 2.1.11 (24.01.2015)
* [Fix] Загрузка лексиконов при множественных вызовах DocLister
* [Fix] Опечатка в имени функции APIhelpers::getkey (PR #119)
* [Fix] Некорректная работа DocLister'a с пустым параметром parents и ignoreEmpty=1
* [Fix] Исправление стилей Debug стека DocLister'a
* [Add] Вывод хлебных крошек к произвольному документу через сниппет DLcrumbs

## 2.1.10 (20.01.2015)
* [Fix] Не устанавливается published в значение 0 (issue #117)
* [Fix] Фильтрация документов при помощи prepare DocLister'a в API режиме
* [Fix] Некорректное завершение autoTable::save() (issue #118)

## 2.1.9 (10.01.2015)
* [Add] Добавлен новый метод ksort в класс \Helpers\Collection (для сортировки массивов по ключу)
* [Fix] Возобновлена поддержка массивов в методе APIhelpers::sanitarTag
* [Refactor] В классе autoTable добавлена обработка SQL запросов с флагом IGNORE (для комфортной работы с уникальными полями)

## 2.1.8 (06.01.2015)
* [Add] Добавлен новый метод \Helpers\FS::fileSize
* [Fix] Исправлена обработка пустых элементов в методах sanitarIn
* [Fix] Предварительная фильтрация пустых значений в сниппете DLReflect
* [Refactor] Игонрирование ссылки на текущий документ в сниппете DLPrevNext
* [Refactor] Пропуск дублирующихся ссылок prev и next в сниппете DLPrevNext
* [Refactor] Переключение типа списка дат (год/месяц) в сниппете DLReflect
* [Refactor] Сниппет DLMonthFilter заменен на DLReflectFilter

## 2.1.7 (05.01.2015)
* [Add] Добавлена поддержка экстендера "e" в режиме api DocLister'a
* [Add] Добавлена поддержка генерации url и title плейсхолдеров в режиме api контроллера site_content

## 2.1.6 (02.01.2015)
* [Fix] Пропадают служебные классы для активного пункта меню в сниппете DLBuildMenu (Issue #116)
* [Fix] Исправлен запуск DLBuildMenu с параметром idType=documents

## 2.1.5 (11.12.2014)
* [Fix] Принудительная установка флага deleted при редактировании любого документа

## 2.1.4 (10.12.2014)
* [Fix] Исправлена ошибка работы с данными через класс modUsers
* [Fix] Исправлена циклическая переадресация в DocLister с параметром id при запросе несуществующей страницы пагинатора

## 2.1.3 (08.12.2014)
* [Add] Добавлен сниппет DLBeforeAfter
* [Add] В DLdebug добавлен новый метод clearLog
* [Refactor] Исправлена сортировка документов по ТВ параметру дата в сниппетах DLReflect и DLMonthFilter

## 2.1.2 (06.12.2014)
* [Add] Добавлен сниппет DLReflect
* [Add] Добавлен сниппет DLMonthFilter
* [Add] Добавлен новый лексикон в DocLister со списком месяцев
* [Fix] Исправлена работа с экстендером e
* [Refactor] Доработан класс \Helpers\Collection

## 2.1.1 (02.12.2014)
* [Add] Добавлен новый сниппет DLUsers и зависящий от него плагин DLLogout
* [Add] Добавлены методы show и toArray() в классе \Helpers\Collection
* [Fix] Исправлено имя ключа массива в методе \Helpers\Collection::add()
* [Fix] Перепутаны местами переменные в цикле метода e_DL_Extender::run()
* [Refactor] FS::makeDir теперь возвращает true если директория существует (Issue #114)
* [Refactor] Переименован файл с классом DLCollection
* [Refactor] Исправлена праверка существования поля в методе modResource::issetField()
* [Refactor] Исправлена проверка существования поля в методе modUsers::issetField()
* [Refactor] Пересмотрен принцип загрузки экстендера e в DocLister
* [Refactor] Экстендер e теперь по умолчанию пытается обработать title поле

## 2.1.0 (30.11.2014)
* [Add] Добавлен класс \Helpers\Collection для работы с коллекциями
* [Add] Добавлена поддержка callback'a в методе MODxAPI::toJson()
* [Fix] Игнорирование флагов учета GET параметров из CSV файла при импорте (Issue #63)
* [Fix] Баг в \Helpers\FS::getInexistantFilename() (Issue #106)
* [Fix] Отсутствие ID записи в modResource::toArray() (Issue #104)
* [Fix] Исправлена проверка возникновения ошибок при запаковке данных в методе MODxAPI::toJson()
* [Fix] Параметры по умолчанию для сниппета getPageID
* [Refactor] Обработка дат в modResource (Issue #64)
* [Refactor] Доработан алгоритм метода \Helpers\FS::takeFileMIME()
* [Refactor] Загрузка конфигов из произвольной папки (Issue #103)

## 2.0.20 (28.11.2014)
* [Add] Поддержка виртуальных полей (Issue #100)
* [Add] Экстендер e (Iusse #95)
* [Add] Добавлено приведение символа тильда к HTML сущности в методе APIHelpers::sanitarTag()
* [Add] Добавлен новый метод APIHelpers::e()
* [Fix] Использование класса DLTemplate без предварительной загрузки класса APIHelpers
* [Refactor] В классе DLphx экранирование символов происходит через класс APIHelpers
* [Refactor] Проверки isset заменены на метод APIHelpers::getkey()
* [Refactor] Экранирование символов в методе MODx::list_log()

## 2.0.19 (24.11.2014)
* [Refactor] Поддержка prepare в контроллерах onetable и site_content при запуске DocLister с параметром api
* [Add] Добавлен класс \Helpers\FS для работы с файловой структурой
* [Add] Добавлен класс \Helpers\Video для работы с видео на видео-хостингах

## 2.0.18 (23.11.2014)
* [Fix] Некорректная работа методов getChildrenList в контроллерах при изменении значения параметров groupBy или selectFields
* [Fix] Вшитый в ТВ параметр префикс таблицы (Issue #96)

## 2.0.17 (21.11.2014)
* [Add] Запись результатирующего ответа от DocLister::render и DocLister::getJSON в переменную DocLister::$outData;
* [Add] Схранение объекта DocLister в плейсхолдер с именем указанным в параметре saveDLObject
* [Add] Задание произвольных имен полей в сниппете DLValueList
* [Add] Отправка стека отладки в лог MODX из сниппета DLValueList
* [Add] Возможность добавить нулевое значение для списка в DLValueList
* [Add] В сниппете DLBuildMenu добавлена поддержка задания различных значений параметров orderBy и addWhereList для разных уровней вложенности меню
* [Fix] trim для uri в сниппете getPageID (Issue #97)
* [Fix] Подстановка $manager_theme для инокни на кнопке в ТВ параметре для RedirectMap (Issue #85)
* [Fix] Опечатка в условиях поиска ID модуля из файла tv.RedirectMap.php (Issue #84)
* [Refactor] Возможность начать построение меню из сниппета DLBuildMenu не с parents, а documents параметра
* [Refactor] Классы с методами штатных сниппетов для prepare объединены в класс DLFixedPrepare, который вынесен в отдельный файл

## 2.0.16 (14.11.2014)
* [Fix] Совместимость метода DocLister::uniformPrepare с php 5.4 при передаче переменной по ссылке
* [Add] Добавлен метод DLTemplate::getTemplate для получение содержимого определенного шаблона
* [Add] Добавлен новый тип для шаблонизатора @TEMPLATE
* [Refactor] Рендер документа с произвольным шаблоном
* [Refactor] Рендер строки с выполнением некешируемых сниппетов

## 2.0.15 (13.11.2014)
* [Add] Добавлен новый метод DocLister::uniformPrepare с однообразными для всех контроллеров подготовками плейсхолдеров
* [Add] Добавлены параметры lastClass, currentClass, firstClass, oddClass, evenClass для подмены имен классов для плейсхолдера [+dl.class+]
* [Add] Метод MODxAPI::fieldPKName для получения имени PK поля в таблице
* [Add] Метод autoTable::tableName для получения оригинального имени таблицы из protected переменной autoTable::$table
* [Fix] Исправлена ошибка SQL запроса в контроллере onetable

## 2.0.14 (12.11.2014)
* [Add] Метод DLTemplate::renderDoc() для получения результатирующего html кода любой страницы вместе с шаблоном и выполнением сниппетов
* [Add] Добавлены новые типы для шаблонизатора @RENDERPAGE и @LOADPAGE

## 2.0.13 (11.11.2014)
* [Add] Добавлен метод DLdebug::updateMessage для обнвления сообщения в логе по ключу
* [Add] Добавлены публичные методы DLdebug::getLog и DLdebug::countLog для работы с приватным массивом DLdebug::$_log
* [Add] Добавлена поддеркжа prepareWrap сниппета для предварительной обработки данных и самого шаблона ownerTPL
* [Add] Возможность подменять шаблон обертку ownerTPL из prepare сниппетов
* [Add] Вывод шаблона ownerTPL в лог вместе с передаваемыми в него плейсхолдерами
* [Refactor] Работа с шаблоном оберткой ownerTPL в DocLister вынесена из контроллеров в публичный метод renderWrap

## 2.0.12 (31.10.2014)
* [Add] Добавлена выборка документов по типу idType = parents в контроллере onetable
* [Fix] Некорректая проверка списка ID и параметра ignoreEmpty в методе getChildrenList контроллера site_content
* [Fix] Исправлено получение значения параметра noChildrenRowTPL в сниппете DLBuldMenu
* [Refactor] Обязательный парметр prepare в сниппетах DLBuildMenu, DLFirstChar обернут двумя новыми необязательными параметрами BeforePrepare, AfterPrepare

## 2.0.11 (17.10.2014)
* [Fix] Исправлен путь к файлам сниппетов для инсталлятора
* [Add] Добавлен параметр makeUrl к контроллеру site_content, который позволяет отключить создание url плейсхолдеров
* [Refactor] Доработан prepare параметр у сниппета DLBuildMenu

## 2.0.10 (10.10.2014)
* [Fix] Отсутствие значений дефолтных полй в modUsers

## 2.0.9 (03.10.2014)
* [Refactor] Кеширование имен ТВ параметров при использовании DocLister экстендера tv
* [Refactor] Кеширование имен ТВ параметров при использовании modResource

## 2.0.8 (30.09.2014)
* [Fix] Два вызова сниппета DLFirstChar на странице
* [Add] Правила для установки через Extras.Evolution и PackageManager

## 2.0.7 (03.09.2014)
* [Fix] Ошибочный запрос в методах autoTable::delete() и modResource::delete() при удалении записей по пустому списку
* [Refactor] При наличии массива default_field при инициализации autoTable модели в MODxAPI не происходит перезапись
* [Refactor] Добавлено экранирование PKField в методе autoTable::edit()
* [Refactor] Пропуск неполного SQL запроса в случае обновления записи через MODxAPI без PKField
* [Refactor] Многие protected методы из абстрактного класса MODxAPI стали публичными
* [Refactor] MODxAPI::sanitarIn метод теперь возвращает пустую строку вместо двойных кавычек при пустом списке значений
* [Refactor] Метод MODxAPI::eraseField возвращает false в случае отсутсвия искомого поля в редактируемой записи и значение самой записи в случае успешного удаления поля.

## 2.0.6 (30.08.2014)
* [Fix] Обработка экранирующих слешей в MODxAPI на хостингах с включеными магическими кавычками

## 2.0.5 (28.08.2014)
* [Fix] Ошибка в SQL запросе MODxAPI при создании новых записей со значениями по умолчанию
* [Fix] Пути к css и js файлам модуля redirectMap2 (Iusse #83)
* [Add] Добавлена возможность получить список полей по умолчанию при помощи метода MODxAPI::getDefaultFields()
* [Add] Добавлена возможность работы MODxAPI в режиме отладки (коллекционирование SQL запросов)
* [Add] Удаление документов из корзины через modResource
* [Add] Поддержка @bindings в классе modResource для методов modResource::toArray(), modResource::toArrayTV(), modResource::renderTV()
* [Add] Метод APIhelpers::renameKeyArr теперь работает с многомерными массивами и склеивает ключи по заданому разделителю
* [Refactor] Выгрузка значений ТВ параметров вместе со значениями по умолчанию через методы modResource::get(), modResource::toArrayTV() и modResource::toArray()
* [Refactor] Замена несуществующего плейсхолдера на пустое значение при парсинге шаблона с использованием phx

## 2.0.4 (20.08.2014)
* [Fix] Валидация значения parent в modResource классе
* [Fix] Исправлена SQL-injection в методе modResource::setTemplate
* [Fix] Игнорирование пропускаемых документов в множественных prepare вызовах
* [Fix] Обращение к имени таблицы в методе getChildernFolder контроллера onetable
* [Refactor] Уровень доступа к методу modResource::setTemplate изменен на public

## 2.0.3 (18.08.2014)
* [Fix] наследование методов MODxAPI в классе autoTable

## 2.0.2 (15.08.2014)
* [Add] Поддержка сокращения текста по словам, а не предложениям
* [Add] Добавлены новые методы в класс APIhelpers
* [Add] Передача параметров вызова сниппета в @ оператор шаблонизации @SNIPPET
* [Refactor] Избавление от DLHelper в пользу класса APIhelpers со static методами. Оригинальный класс APIhelpers из комплекта MODxAPI переименован в MODxAPIhelpers
* [Refactor] Экстендер и сниппет summary в зависимости от одного класса
* [Refactor] Пересмотр доступности методов в классе SummaryText
* [Fix] Исправлен вызов экстендера summary в контроллерах onetable и site_content
* [Fix] Обработка ошибки разбора JSON в модуле RedirectMap

## 2.0.1 (14.08.2014)
* [Add] Добавлен параметр urlScheme к контроллерам DocLister
* [Add] Добавлен новый фильтр notin

## 2.0.0 (12.08.2014)
* [Add] Добавлен сниппет DLBuildMenu
* [Add] Добавлен сниппет DLPrevNext
* [Add] Добавлен хелпер класс DLHelper
* [Add] Добавлена возможность подмены шаблона для обрабатываемого документа через переменную DocLister::renderTPL
* [Refactor] Библиотека MODxAPI перенесена в репозиторий DocLister
* [Refactor] Модуль RelativeTVList перемещен в репозиторий DocLister
* [Refactor] Модуль RedirectMap2 перемещен в репозиторий DocLister
* [Refactor] Плагин getPageID перемещен добавлен DocLister
* [Refactor] Добавлена поддержка микроформата Breadcrumb schema.org
* [Refactor] Пересмотрена структура каталогов
* [Refactor] Перенос сниппета Summary в репозиторий DocLister
* [Refactor] Обновление JS библиотек FileAPI и jeditable
* [Refactor] Перенос методов из класса APIhelpers в DLHelper
