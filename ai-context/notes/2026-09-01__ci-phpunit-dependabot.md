# CI DocLister и Dependabot #10

Дата: 2026-09-01

## Почему падало

1. GitHub Actions для тестов не было. Красные runs — job Dependabot Updates (4.2 → 8.5+ без переписи тестов).
2. Travis (`.travis.yml`, бейдж README) больше не собирает репозиторий.
3. Scrutinizer: PHP 7.4 + `phpunit/phpunit: 4.2.*` → `Tests: errored` (процесс умер, не ассерты).

## Alert #10

[GHSA-vvj3-c3rp-c85p](https://github.com/advisories/GHSA-vvj3-c3rp-c85p) / CVE-2026-24765: `unserialize` PHPT coverage. Патч с 8.5.52 / 9.6.33. В дереве нет `.phpt`; пакет только `require-dev`. Алерт закрывается обновлением, не dismiss.

## Решение

PHPUnit 9.6, не 10: сохраняет PHP 7.4 и аннотации. Тестовый `DocumentParser` дополнен (`getLocale`, `toPlaceholder`, `evolutionCMS()`). `shopkeeperDocLister` в пакете нет — тесты skipped. `composer.lock` по-прежнему в `.gitignore`.
