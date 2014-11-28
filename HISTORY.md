## 2.0.20 (28.11.2014)
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