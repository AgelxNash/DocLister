# Задача: Диагностика issue #398 — prepare не работает в onetable

Дата создания: 2026-08-31
Приоритет: High
ID: DL-ISSUE-398
Parent: None
Статус: [BLOCKED]

## Описание

Проверить GitHub issue #398: `Prepare не работает в onetable?`.

Источник: https://github.com/AgelxNash/DocLister/issues/398

RAW issue сохранён в `ai-context/artifacts/github-2026-08-31/issue-398/issue.json`.

## Ход выполнения

- [x] Research (Status: [DONE]) — issue прочитан через публичный GitHub API, комментариев нет.
- [x] Code analysis (Status: [DONE]) — проверены `onetable`, `site_content`, `prepare.extender.inc`, `DocLister.abstract.php`.
- [ ] Reproduction (Status: [BLOCKED]) — штатные тесты не запускались: `vendor/` отсутствует, проект требует PHPUnit 4.2.*, локальный PHP 8.4.1.
- [ ] Implementation (Status: [TODO]) — код не изменялся.
- [x] Documentation (Status: [DONE]) — результат диагностики сохранён в `ai-context/notes/2026-08-31__issue-398-onetable-prepare.md`.

## Заметки и решения

Фактический пример issue использует `controller=onetable`, `api=1`, `selectFields=id,status_id,fields,hash` и `prepare` как closure, который добавляет `$data['aaaaa'] = 'AAAAAAAA'`.

Read-only анализ показывает, что `onetableDocLister::getJSON()` действительно вызывает prepare-экстендер перед передачей результата в `parent::getJSON()`. Поэтому на уровне кода issue выглядит не как «prepare вообще не вызывается», а как проблема проверки результата или окружения.

Вероятная причина: если `api` содержит конкретный список полей, `parent::getJSON()` оставляет только поля из `api`; поле, добавленное prepare, пропадёт, если его не добавить в `api`. Однако в примере issue указано `api=1`, а для `api=1` базовый `getJSON()` должен вернуть все поля, включая добавленное prepare-поле.

BLOCKED: для подтверждения требуется воспроизведение на окружении проекта. Локально `vendor/` отсутствует, а `composer install` и возможная адаптация PHPUnit/PHP — мутабельные действия, требующие отдельного разрешения владельца.
