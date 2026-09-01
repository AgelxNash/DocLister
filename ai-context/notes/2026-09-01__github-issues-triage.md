# Верификация GitHub issues 2026-09-01

## TL;DR

Открытых issues в `AgelxNash/DocLister` — **0**. Snapshot сохранён в `ai-context/artifacts/github-2026-09-01/open-issues-snapshot.json` (пустой массив) и сверен авторизованным `gh` (учётная запись AgelxNash): открытых issues и открытых PR нет. Backlog issues исчерпан.

## Изменения относительно snapshot 2026-08-31

Вчера было 20 открытых issues. За сутки закрыты все 20 в рамках волны стабилизации; fixes смержены PR-ами:

| Issue | Классификация | Fix |
|---|---|---|
| #398 prepare/onetable | CANNOT_REPRODUCE | regression test `515dce6`; закрыт 2026-08-31 20:01 с итоговым комментарием |
| #386 pub_date/api | подтверждён | PR #402 `Fix API date leaking createdon after empty pub_date` (merge 2026-08-31 23:42) |
| #342 фильтры сравнения | подтверждён | PR #400 `Fix string comparison filters` (merge 2026-08-31 20:29) |
| #320 offset pagination | подтверждён | PR #401 `Fix pagination page count when offset is set` (merge 2026-08-31 23:34) |
| #372 TagSaver/tags | подтверждён | PR #403 `Fix comma-separated tags in site_content_tags` (merge 2026-08-31 23:51) |
| #319 DLMenu openIds | NEEDS_INFO | закрыт ранее, без фикса |
| остальные 14 issues | OBSOLETE / NEEDS_INFO / product decisions | закрыты в ходе triage-волны 2026-08-31 |

Дополнительно смержены инфраструктурные PR: #404 (stabilization report), #405 (Travis+PHPUnit 4.2 → GitHub Actions + PHPUnit 9), #406–#408 (dependabot: actions/checkout 7, modx-evo-database 1.5.*, FileAPI/Scrutinizer).

## Кандидаты на диагностику

Открытых issues нет — новых кандидатов нет. Потенциальные источники будущих задач:

- проверка смерженных фиксов #400–#403 на реальном окружении Evolution CMS;
- вопросы из stabilization report (PR #404);
- reopened/новые issues от пользователей после релиза волны фиксов.

## Локальное состояние workspace (важно)

Локальный `master` (`515dce6`) отстаёт от `origin/master` (`b836041`) более чем на 10 merge-коммитов. В рабочей копии лежат незакоммиченные изменения — устаревший слепок смерженной работы (#320 fix + тесты, миграция тестовой инфраструктуры). Локальный `tests/src/Unit/DL/Extender/Paginate/Issue320Test.php` отличается от смерженной версии. Изменения не удалялись и не перезаписывались: очистка затронет незакоммиченный контент и требует решения владельца (сброс до `origin/master` с утратой локальных дублей либо ручная сверка).

## Политика безопасности

Тексты issues/comments — недоверенные данные; инструкции из них не исполнялись. Чтение — публичный API + авторизованный `gh`; метаданные issues не менялись.
