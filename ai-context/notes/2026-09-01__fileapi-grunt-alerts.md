# FileAPI grunt alerts и Scrutinizer

Дата: 2026-09-01

`assets/js/fileapi/` — вендорный jquery.fileapi 0.4.9. `package.json` описывал grunt 0.4 для сборки, которую DocLister не запускает. CVE grunt (2020–2022) не относятся к runtime на сайте.

Удаление манифеста закрывает Dependabot #13, #14, #15 без обновления grunt 0.4 → 1.5 (это чужой Gruntfile).

Scrutinizer `Tests: errored` шёл от MySQL 5.7 + `vendor/bin/phpunit` на их PHP 7.4. Покрытие и phpcs там больше не гоняем: источник истины — `.github/workflows/ci.yml`.
