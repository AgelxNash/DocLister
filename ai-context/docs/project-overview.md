# Обзор проекта DocLister

Дата: 2026-09-01

## TL;DR

DocLister — legacy PHP-пакет для MODX Evolution / Evolution CMS. Основной функционал расположен в `assets/snippets/DocLister/` и `assets/lib/`. Тесты — PHPUnit 9.6 (патч CVE-2026-24765), CI — GitHub Actions на PHP 7.4 / 8.1 / 8.3. PHPCS остаётся PSR-2.

## Назначение

По README проект предоставляет класс и набор сниппетов для вывода информации из таблиц по предопределённым правилам. Главный контроллер — `site_content`, который связывает документы MODX `site_content` с TV-параметрами.

## Состав

- `assets/snippets/DocLister/` — основной сниппет DocLister, производные сниппеты, контроллеры, экстендеры, фильтры, языковые файлы и служебные библиотеки.
- `assets/lib/` — вспомогательные классы, MODxAPI, форматтеры, модульные helpers и SimpleTab.
- `install/assets/snippets/` — шаблоны `.tpl` для установки.
- `tests/` — PHPUnit-тесты, SQL-фикстуры MODX Evolution и мок `DocumentParser`.
- `composer.json` — classmap autoload для legacy-кода и dev-зависимости `phpunit/phpunit:^9.6.33`, `agelxnash/modx-evo-database:1.4.*`.
- `.github/workflows/ci.yml` — PHPUnit + MySQL 8.0.
- `AGENTS.md` — правила сопровождения: один issue за цикл, без breaking change без ADR.
- `phpunit.xml` — suites `Unit` и `Real`.
- `phpcs.xml` — PSR-2 с исключением отсутствующих namespace для legacy-классов.

## Контур ai-context

- `ai-context/docs/` — проектная документация (в git).
- `ai-context/docs-index.md` — индекс документации (в git).
- `ai-context/notes/`, `ai-context/tasks/`, `ai-context/artifacts/` — локальный рабочий контур (вне git).
