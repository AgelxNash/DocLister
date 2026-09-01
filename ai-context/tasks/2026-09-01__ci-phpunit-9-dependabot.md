# Задача: живой CI и Dependabot #10

Дата создания: 2026-09-01
Приоритет: High
ID: DL-CI-001
Parent: None
Статус: [DONE]
Классификация: FIXED

## Описание

Красный «CI» не был связан с багфиксами #320/#386/#372. Travis мёртв, Scrutinizer падал на PHPUnit 4.2, Dependabot не мог прыгнуть с 4.2 на 8.5+. Alert #10 — CVE-2026-24765 в PHPUnit.

## Решение

- `phpunit/phpunit: ^9.6.33` (пропатченная линейка, PHP 7.4 сохранён).
- GitHub Actions: PHP 7.4 / 8.1 / 8.3 + MySQL 8.0.
- Тесты переведены на API PHPUnit 9.
- `.travis.yml` удалён, Dependabot не предлагает major PHPUnit.

## Ход выполнения

- [x] Research
- [x] Implementation
- [x] Verification — `vendor/bin/phpunit --testsuite Unit`: 106 tests, 303 assertions, 9 skipped (нет `shopkeeperDocLister`)
- [x] Pull request
