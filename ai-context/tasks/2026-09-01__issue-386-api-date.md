# Задача: Issue #386 — pub_date не попадает в item['date'] в режиме api

Дата создания: 2026-09-01
Приоритет: High
ID: DL-ISSUE-386
Parent: None
Статус: [DONE]
Классификация: FIXED

## Описание

В режиме `api=1` у документа с ненулевым `pub_date` поле `date` остаётся датой создания, хотя в HTML/debug оно верное.

Источник: https://github.com/AgelxNash/DocLister/issues/386

## Диагноз

В `site_contentDocLister::getJSON()` источник даты берётся один раз до цикла. Если у предыдущего документа `pub_date=0`, переменная `$date` переключается на `createdon` и протекает на все следующие строки.

HTML-рендер сбрасывает `$date` на каждой итерации — поэтому debug выглядит корректно.

`getDocs()` без `getJSON()` поле `date` не формирует — это ожидаемо.

## Ход выполнения

- [x] Research
- [x] Code tracing
- [x] Reproduction (утечка `$date` между строками)
- [x] Implementation — `$date` сбрасывается на каждой итерации `getJSON()`
- [x] Verification — harness PASS: 08.02.2024 и 03.01.2023
- [ ] Documentation
- [ ] GitHub resolution
