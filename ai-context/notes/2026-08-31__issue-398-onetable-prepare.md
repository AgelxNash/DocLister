# Диагностика issue #398 — prepare не работает в onetable

Дата: 2026-08-31
Статус: DONE
Классификация: CANNOT_REPRODUCE

## TL;DR

На текущем `master` проблема #398 не воспроизводится. `onetableDocLister::getJSON()` вызывает closure из `prepare`, а добавленное поле `aaaaa` присутствует в JSON при `api=1`. Production-код не изменялся. Добавлен regression test текущего корректного поведения; он прошёл в изолированном legacy-окружении PHP 7.0 + PHPUnit 4.8.36: `OK (1 test, 4 assertions)`.

## Данные issue

- URL: https://github.com/AgelxNash/DocLister/issues/398
- Заголовок: `Prepare не работает в onetable?`
- Создано: 2026-01-21T10:36:34Z
- RAW: `ai-context/artifacts/github-2026-08-31/issue-398/issue.json`

Пример issue использует `controller=onetable`, `api=1` и closure `prepare`, добавляющий `$data['aaaaa'] = 'AAAAAAAA'`.

## Проверенные участки кода

- `assets/snippets/DocLister/core/controller/onetable.php`
  - `getJSON()` вызывает prepare-экстендер до передачи данных в `parent::getJSON()`.
- `assets/snippets/DocLister/core/extender/prepare.extender.inc`
  - Closure/callable поддерживаются через `is_callable()` и `call_user_func()`.
- `assets/snippets/DocLister/core/DocLister.abstract.php`
  - при `api=1` базовый `getJSON()` включает все поля;
  - при списке полей в `api` возвращаются только перечисленные поля.

## Воспроизведение

Минимальный runtime-harness на текущем коде показал:

- prepare-экстендер загрузился;
- closure был вызван;
- JSON содержит `"aaaaa":"AAAAAAAA"`.

Артефакт: `ai-context/artifacts/github-2026-08-31/issue-398/reproduction-output.txt`.

## Regression test

Добавлен:

- `tests/src/Unit/DL/Controller/Onetable/getJSONTest.php`
- `getJSONTest::testPrepareClosureCanAddFieldInApiMode`

Проверяет:

1. closure `prepare` вызывается;
2. поле, добавленное closure, присутствует в JSON при `$fields='1'`, что соответствует передаче конфигурационного `api=1` из `snippet.DocLister.php`.

## Legacy verification

Так как рабочий репозиторий не содержит `vendor/`, окружение подготовлено в одноразовой копии вне репозитория:

- Docker image: `php:7.0-cli`;
- PHPUnit: `4.8.36`;
- результат: `OK (1 test, 4 assertions)`;
- exit code: `0`.

Полный отчёт: `ai-context/artifacts/github-2026-08-31/issue-398/phpunit-legacy-test.txt`.

Composer-установка через Packagist не использована для финального запуска из-за сетевых TLS/reset ошибок; тест выполнен автономным PHPUnit PHAR и минимальным временным autoload/stub слоем, не изменяющим рабочий репозиторий.

## Вывод

Issue классифицируется как `CANNOT_REPRODUCE` на текущем `master`. Production fix не требуется. Regression test закрепляет текущий контракт и защищает от будущей регрессии.

Дополнение: если `api` задан списком, например `api=id,status_id,fields,hash`, поле `aaaaa` будет отфильтровано ожидаемым образом; его нужно явно включить в список либо использовать `api=1`.
