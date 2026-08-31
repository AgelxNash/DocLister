# Состояние сессии DocLister 2026-08-31

## TL;DR

Проект изучен, открытые GitHub issues верифицированы, локальный `ai-context/` создан, активирована одна объединённая ZCode-автоматизация по аналогии с `go-repo-orchestrator`: ежедневная проверка issues и безопасный локальный task conveyor.

## Выполнено

- Прочитан `README.md`.
- Подтверждено отсутствие локального `AGENTS.md` и старого `ai-context/`.
- Подтверждено отсутствие активных Cron-автоматизаций ZCode в workspace до настройки.
- Создана и обновлена одна активная Cron-автоматизация: `automation-c28dd104-739e-46b5-bddc-049899c6082d`, `каждый день в 09:00 DocLister issues и безопасный task conveyor`. Попытка создать вторую отдельную автоматизацию была отклонена ZCode с ограничением текущей scheduled-bound сессии, поэтому функции объединены в одной задаче.
- Через публичный GitHub API получен список 20 открытых issues.
- RAW snapshot сохранён: `ai-context/artifacts/github-2026-08-31/open-issues-snapshot.json`.
- Создана структура:
  - `ai-context/docs/`
  - `ai-context/notes/`
  - `ai-context/tasks/`
  - `ai-context/artifacts/github-2026-08-31/`
- Созданы индексы:
  - `ai-context/docs-index.md`
  - `ai-context/notes-index.md`
  - `ai-context/tasks-index.md`

## Технический профиль

- Legacy PHP / MODX Evolution.
- PHPUnit 4.2.* в `composer.json`.
- `vendor/` отсутствует, поэтому запуск PHPUnit требует отдельного решения по установке зависимостей.
- Старые CI-конфиги: Travis и Scrutinizer.
- Рабочая ветка на старте: `master`, рабочее дерево было чистым.

## Следующий зрелый item

Issue #398 — `Prepare не работает в onetable?` — завершён. Классификация: CANNOT_REPRODUCE. На текущем master closure `prepare` вызывается, добавленное поле присутствует в JSON при `api=1`. Production-код не изменялся; добавлен regression test, прошедший в изолированном PHP 7.0 + PHPUnit 4.8.36 (`OK (1 test, 4 assertions)`). Подробности: `ai-context/notes/2026-08-31__issue-398-onetable-prepare.md`.

Следующий кандидат после полного публичного сопровождения #398: GitHub issue #386 — `pub_date не попадает в item['date'] в режиме api`.

## Правила для автоматизаций

- Не выполнять `git commit`, `git push`, `git merge` без прямой просьбы владельца.
- Не закрывать, не комментировать и не менять GitHub issues без прямого разрешения владельца.
- Не менять конфиги зависимостей, CI/CD, `.env`, composer/npm/pip и аналоги без отдельного подтверждения.
- Не выполнять `composer install` без подтверждения владельца.
- Рабочие материалы вести на русском языке.
- Для крупных задач использовать `ai-context/tasks/` и `ai-context/tasks-index.md`.
