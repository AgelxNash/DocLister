# Отчёт о стабилизации DocLister

Дата: 2026-09-01  
Репозиторий: [AgelxNash/DocLister](https://github.com/AgelxNash/DocLister)  
База: `origin/master` @ `6d7b9f4`  
Открытых GitHub issues: **0**  
Открытых PR: **0**

## TL;DR

Этап stabilization закрыт. Все открытые issues репозитория получили классификацию и действие: либо минимальный production-fix с regression test, либо закрытие как cannot-reproduce / obsolete / upstream / wontfix. Публичный API не ломался. Следующий этап — не дописывать DocLister «на будущее», а сверять extras Evolution CMS с ядровыми сервисами.

---

## 1. Исходный инвентарь и итог

Стартовый снимок 2026-08-31: 20 открытых issues (`ai-context/docs/2026-08-31__doclister-issue-inventory.md`). Часть закрылась до этой сессии (#349 и другие). В сессии 2026-08-31/09-01 закрыт остаток.

| Issue | Заголовок | Классификация | Действие |
|---|---|---|---|
| #398 | Prepare не работает в onetable? | CANNOT_REPRODUCE | Regression test, issue закрыт |
| #386 | pub_date не попадает в item['date'] в api | FIXED | PR #402 |
| #372 | TagSaver: Doclister calls not working | FIXED | PR #403; часть репорта — ошибка вызова `get:` |
| #367 | bLang + eFilter пагинация | UPSTREAM | Закрыт: язык даёт `makeUrl` / `lang_content` |
| #349 | idType=parents в onetable | ALREADY_FIXED | Закрыт ранее (фича в HISTORY 2.5.0) |
| #345 | дата без учёта &locale | WONTFIX / OBSOLETE | `date()` + отказ от `strftime` на PHP 8 |
| #342 | Фильтр `>`, `>=`, `<`, `<=` | FIXED | PR #400 |
| #320 | offset и число страниц | FIXED | PR #401 |
| #319 | DLMenu openIds | ALREADY_FIXED / CLOSED | Закрыт 2026-08-31 до фикс-цикла |
| #305 | Рефакторинг пагинатора | WONTFIX | Архитектура, нужен ADR/major |
| #264 | Пользовательские настройки | WONTFIX | Enhancement без подтверждённой потребности |
| #212 | Хэш паролей в modUsers | OBSOLETE | Pause, Not confirmed, Evo 3 users |
| #209 | Работа с событиями | WONTFIX | Слой ядра, не DL |
| #208 | ID реального пользователя | OBSOLETE / UPSTREAM | evolution-cms/evolution#157 |
| #162 | Заготовка для custom tv | WONTFIX | Enhancement 2016, не баг |
| #111 | Автозагрузчик классов | WONTFIX | composer classmap уже есть |
| #102 | Конструктор SQL | WONTFIX | Автор сам отменил затею |
| #76 | Рефакторинг логирования | WONTFIX | Ломает публичный debug-контракт |
| #75 | Журнал MODX | WONTFIX | Решение maintainers: не нужно |
| #74 | Проверка прав доступа | OBSOLETE / WONTFIX | Модель прав Evo 1.x |

---

## 2. Коммиты и PR этой кампании

| PR | Commit | Суть |
|---|---|---|
| [#400](https://github.com/AgelxNash/DocLister/pull/400) | `eef7cc2` | Сравнение в фильтрах: даты не через `floatval()` |
| [#401](https://github.com/AgelxNash/DocLister/pull/401) | `5fce9dd` | `offset` уменьшает total пагинатора |
| [#402](https://github.com/AgelxNash/DocLister/pull/402) | `85cb9fe` | `getJSON()` не протекает `createdon` между строками |
| [#403](https://github.com/AgelxNash/DocLister/pull/403) | `8e4e8ad` | Теги через запятую в `site_content_tags` |
| — | `515dce6` | Regression: `prepare` в onetable + api |

Merge-коммиты: `985cba3`, `6159eb5`, `2e28ea0`, `6d7b9f4`.

ADR в этом этапе **не принимались**. Крупный refactoring сознательно не начинался.

---

## 3. Regression tests

| Файл | Issue |
|---|---|
| `tests/src/Unit/DL/Controller/Onetable/getJSONTest.php` | #398 |
| `tests/src/Unit/DL/Filters/Issue342Test.php` | #342 |
| `tests/src/Unit/DL/Extender/Paginate/Issue320Test.php` | #320 |
| `tests/src/Unit/DL/Controller/SiteContent/Issue386Test.php` | #386 |
| `tests/src/Unit/DL/Controller/SiteContentTags/Issue372Test.php` | #372 |

Ограничение на момент багфиксов: PHPUnit 4.2 не бежал на PHP 8.4. После отдельного CI-PR раннер — PHPUnit ^9.6.33, GitHub Actions (PHP 7.4 / 8.1 / 8.3). Travis удалён. Dependabot alert #10 (CVE-2026-24765) закрывается обновлением PHPUnit.

---

## 4. Обнаруженные публичные контракты

Нельзя менять без major / явного ADR:

- параметры сниппетов (`offset`, `start`, `dateSource`, `dateFormat`, `locale`, `tagsData`, `tagsSeparator`, `paginate`, `api`, `prepare`);
- контроллеры `site_content`, `onetable`, `site_content_tags`;
- `getChildrenCount()` = сырой SQL-count (DLBeforeAfter);
- пагинатор считает страницы по visible count (`count − offset`, кроме `maxDocs`);
- `getDocs()` без `getJSON()` не содержит вычисляемого `date`;
- HTML/debug сбрасывает `dateSource` на каждой строке; API теперь так же;
- `tagsSeparator` по умолчанию `||`, запятая — fallback;
- `get:KEY` читает только `$_GET[KEY]`, не `$_GET['tag']` автоматически;
- debug/DLdebug и string-SQL filters — часть API extras.

---

## 5. Известные consumers

Из README и экосистемы Pathologic / webber12:

SimpleGallery, SimpleTube, SimpleFiles, SimplePolls, LikeDislike, FormLister, FastImageTV, DLRequest, evoSearch, eFilter, Selector.

Плюс кастомные вызовы `runSnippet('DocLister')` на сайтах Evo 1.x–3.x.

---

## 6. Риски совместимости

- `offset` теперь уменьшает `[+count+]`, `totalPages`, `from`/`to`. Сырой `getChildrenCount()` не менялся.
- Списки тегов с запятой внутри имени тега могут разрезаться fallback-сплиттером.
- Исторический PHPUnit 4.2 / Travis заменены PHPUnit 9.6 и GitHub Actions.
- `strftime` больше не используется; `&locale` не локализует `date()`.

---

## 7. Сознательно не исправленный долг

- PHPUnit 9.6 закрывает CVE-2026-24765; переход на PHPUnit 10+ потребует атрибутов и PHP 8.1+.
- Scrutinizer всё ещё PHP 7.4; после живого GitHub Actions он не является source of truth.
- `getUrl()` для `site_start` минует `makeUrl` (мультиязычие — зона ядра/bLang).
- Контроллер тегов не умеет «вывести теги текущего документа» — он фильтрует документы.
- Нет Intl-форматирования дат.
- Нет своего SQL builder / PSR-4 rewrite / Exception-иерархии логов.

---

## 8. Кандидаты на следующий major

1. PHPUnit 10+ (после отказа от PHP 7.4 в CI).  
2. Опциональный `IntlDateFormatter` при заданном `locale`.  
3. Единый `getUrl()` через `makeUrl` (после проверки bLang/eFilter).  
4. PSR-4 только вместе с ядром Evo, не отдельным include-cleanup.

Не тащить в DL: Eloquent, свой ORM, журнал MODX, ACL пользователей.

---

## 9. Рекомендации для Evolution CMS Community

1. Держать DocLister как стабильный extra: багфиксы + tests, без архитектурных rewrite.  
2. Язык URL, пользователи, cache, log, lexicon — контракт ядра, не DL.  
3. Issue template: PHP, Evo, вызов сниппета, SQL из debug.  
4. Stale-политика для Pause/Not confirmed старше двух лет.  
5. Consumers (FormLister, SimpleGallery, eFilter) гонять на одном Docker-образе Evo 3.x после любого изменения пагинации, filters или `getJSON`.

Этот документ — вход в этап работы с `evocms-community`, не лицензия переписывать DocLister.
