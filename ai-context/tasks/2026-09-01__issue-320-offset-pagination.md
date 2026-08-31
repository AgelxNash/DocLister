# Задача: Issue #320 — offset не учитывается при подсчёте страниц

Дата создания: 2026-09-01
Приоритет: High
ID: DL-ISSUE-320
Parent: None
Статус: [DONE]
Классификация: FIXED

## Описание

Пагинатор не вычитал `offset` из total, из-за чего появлялась лишняя пустая страница.

Источник: https://github.com/AgelxNash/DocLister/issues/320
PR: https://github.com/AgelxNash/DocLister/pull/401

## Решение

В `paginate.extender.inc` `getListPages()` уменьшает count на `offset`, если `maxDocs` не задан.
`getChildrenCount()` не менялся — сырой подсчёт нужен DLBeforeAfter.

## Ход выполнения

- [x] Research
- [x] Implementation
- [x] Verification — harness 4/4 PASS на PHP 8.4
- [x] Pull request #401
- [x] Merge в master (`6159eb5`)
