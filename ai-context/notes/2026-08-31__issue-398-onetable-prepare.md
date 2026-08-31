# Диагностика issue #398 — prepare не работает в onetable

Дата: 2026-08-31
Статус: BLOCKED на воспроизведении

## TL;DR

Issue #398 проверен read-only. В коде `onetableDocLister::getJSON()` prepare-экстендер вызывается, поэтому проблема не подтверждается как очевидное отсутствие вызова `prepare` в `onetable`. Для окончательной верификации нужно воспроизвести пример issue на рабочем окружении, но локально нет `vendor/`, проект использует PHPUnit 4.2.*, а локальный PHP — 8.4.1; установка зависимостей и адаптация окружения требуют отдельного разрешения.

## Данные issue

- URL: https://github.com/AgelxNash/DocLister/issues/398
- Заголовок: `Prepare не работает в onetable?`
- Создано: 2026-01-21T10:36:34Z
- Обновлено: 2026-01-21T10:36:34Z
- Комментарии: 0
- RAW: `ai-context/artifacts/github-2026-08-31/issue-398/issue.json`

Пример из issue использует:

- `controller => onetable`
- `idType => documents`
- `table => commerce_orders`
- `ignoreEmpty => 1`
- `api => 1`
- `selectFields => id,status_id,fields,hash`
- `prepare => function($data, $modx, $DL, $_ext) { $data['aaaaa'] = 'AAAAAAAA'; return $data; }`

## Проверенные участки кода

- `assets/snippets/DocLister/core/controller/onetable.php`
  - `_render()` вызывает `$this->getExtender('prepare')` и затем `$extPrepare->init(... 'nameParam' => 'prepare')`.
  - `getJSON()` также вызывает `$this->getExtender('prepare')`, затем `$extPrepare->init($this, array('data' => $row))`, и только после этого передаёт данные в `parent::getJSON($out, $fields, $out)`.
- `assets/snippets/DocLister/core/controller/site_content.php`
  - Поведение аналогично: `getJSON()` вызывает prepare перед `parent::getJSON()`.
- `assets/snippets/DocLister/core/extender/prepare.extender.inc`
  - Closure и callable поддерживаются через `is_callable($name)` / `call_user_func(...)`.
  - Prepare-функция получает аргументы: `$data`, `$modx`, `$_DocLister`, `$_extDocLister`.
- `assets/snippets/DocLister/core/DocLister.abstract.php`
  - `checkDL()` загружает prepare-экстендер, если `getCFGDef('prepare', '') != ''`.
  - `parent::getJSON()` фильтрует выходные поля по параметру `api`: если `api=1`, возвращаются все поля; если `api` — список, возвращаются только перечисленные поля.

## Гипотезы

### H1: prepare вообще не вызывается в `onetable`

Статус: не подтверждено read-only анализом.

В `onetableDocLister::getJSON()` prepare вызывается на строках `241–246` перед финальным `parent::getJSON()`.

### H2: prepare добавляет поле, но оно отфильтровывается API-параметром

Статус: возможно для случаев, где `api` — список полей.

`parent::getJSON()` оставляет только поля из `api`, кроме специального режима `api=1`. Если пользователь добавляет `$data['aaaaa']`, но вызывает `api=id,status_id,fields,hash`, поле `aaaaa` будет отброшено после prepare. В примере issue указан `api=1`, поэтому эта гипотеза не объясняет конкретный пример полностью, но важна для диагностики похожих обращений.

### H3: проблема связана с окружением или версией PHP/MODX

Статус: требует воспроизведения.

Локальный PHP: 8.4.1. Проект использует PHPUnit 4.2.* и legacy-подход без установленного `vendor/`. Без подготовки совместимого окружения подтвердить поведение тестом нельзя.

## Почему задача BLOCKED

Для строгой проверки нужен один из вариантов:

1. Разрешить установку зависимостей / подготовку тестового окружения.
2. Дать готовое окружение MODX Evolution + Commerce, где можно воспроизвести сниппет.
3. Разрешить написать минимальный unit/regression test и, если он упадёт, отдельно решить по правилу проекта: исправлять код, тест или игнорировать.

До этого момента production-код не изменялся.

## Следующая безопасная точка входа

Если владелец разрешит подготовку окружения, минимальный следующий шаг: создать отдельный regression test для `onetableDocLister::getJSON()` с closure в `prepare` и `api=1`, затем проверить, попадает ли поле `aaaaa` в JSON.

Если окружение не готовим, conveyor может перейти к read-only диагностике issue #386.
