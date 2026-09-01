# Задача: FileAPI npm-шум и Scrutinizer

Дата создания: 2026-09-01
Приоритет: Medium
ID: DL-CI-002
Parent: DL-CI-001
Статус: [DONE]
Классификация: FIXED

## Описание

После живого GitHub Actions Dependabot нашёл `grunt ~0.4.0` в вендорном `assets/js/fileapi/package.json` (alerts #13–#15). Dependabot Updates для grunt упал. Scrutinizer продолжал валить PR из‑за MySQL + PHPUnit.

## Решение

- Удалены `package.json` и `Gruntfile.js` FileAPI. В поставку входят уже собранные `.js`.
- Scrutinizer: только `php-scrutinizer-run`, без MySQL и PHPUnit. Тесты — GitHub Actions.
- В репозиторий добавлен `AGENTS.md`.

## Ход выполнения

- [x] Research
- [x] Implementation
- [x] Pull request
