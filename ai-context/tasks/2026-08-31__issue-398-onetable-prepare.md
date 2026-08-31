# Задача: Диагностика issue #398 — prepare не работает в onetable

Дата создания: 2026-08-31
Приоритет: High
ID: DL-ISSUE-398
Parent: None
Статус: [DONE]
Классификация: CANNOT_REPRODUCE

## Описание

Проверить GitHub issue #398: `Prepare не работает в onetable?`.

Источник: https://github.com/AgelxNash/DocLister/issues/398

RAW issue сохранён в `ai-context/artifacts/github-2026-08-31/issue-398/issue.json`.

## Ход выполнения

- [x] Research (Status: [DONE]) — issue прочитан через публичный GitHub API.
- [x] Code analysis (Status: [DONE]) — проверены `onetable`, `site_content`, `prepare.extender.inc`, `DocLister.abstract.php`.
- [x] Reproduction (Status: [DONE]) — минимальный runtime-harness подтвердил вызов closure и наличие поля `aaaaa` в JSON при `api=1`.
- [x] Implementation (Status: [DONE]) — production-код не изменялся; добавлен regression test текущего корректного поведения.
- [x] Verification (Status: [DONE]) — test прошёл в изолированном legacy-окружении PHP 7.0 + PHPUnit 4.8.36: `OK (1 test, 4 assertions)`.
- [x] Documentation (Status: [DONE]) — результаты и acceptance-артефакты сохранены в `ai-context/`.

## Артефакты

- `ai-context/artifacts/github-2026-08-31/issue-398/issue.json`
- `ai-context/artifacts/github-2026-08-31/issue-398/reproduction-output.txt`
- `ai-context/artifacts/github-2026-08-31/issue-398/phpunit-legacy-test.txt`

## Заметки и решения

Фактический пример issue использует `controller=onetable`, `api=1`, `selectFields=id,status_id,fields,hash` и `prepare` как closure, который добавляет `$data['aaaaa'] = 'AAAAAAAA'`.

Текущее поведение `master` корректно:

- `onetableDocLister::getJSON()` вызывает prepare-экстендер;
- closure вызывается;
- добавленное поле присутствует в JSON при `api=1`.

Если `api` содержит конкретный список полей, поля prepare должны быть явно включены в этот список; это ожидаемая фильтрация базового `getJSON()`.

Production-исправление не требуется. Добавлен regression test `getJSONTest::testPrepareClosureCanAddFieldInApiMode`, закрепляющий текущий контракт.
