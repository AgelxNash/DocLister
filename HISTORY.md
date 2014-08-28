## 2.0.5 (28.08.2014)
* [Fix] Ошибка в SQL запросе MODxAPI при создании новых записей со значениями по умолчанию 
* [Add] Добавлена возможность получить список полей по умолчанию при помощи метода MODxAPI::getDefaultFields()
* [Add] Добавлена возможность работы MODxAPI в режиме отладки (коллекционирование SQL запросов)

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