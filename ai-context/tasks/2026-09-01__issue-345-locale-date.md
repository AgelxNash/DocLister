# Задача: Issue #345 — дата без учёта &locale

Дата создания: 2026-09-01
Приоритет: Medium
ID: DL-ISSUE-345
Parent: None
Статус: [DONE]
Классификация: WONTFIX / OBSOLETE

## Описание

https://github.com/AgelxNash/DocLister/issues/345 — пустое тело. Заголовок: контроллер `site_content` формирует дату без учёта `&locale`.

## Предварительный анализ

- `DocLister::setLocate()` уже вызывает `setlocale(LC_ALL, $locale)` из параметра `locale`.
- Дата собирается через `date($dateFormat, $_date)`.
- `date()` игнорирует locale; локаль влияла только на `strftime()`.
- В `getCFGDef('dateFormat')` форматы с `%` уже конвертируются в токены `date()` — это путь совместимости с PHP 8, где `strftime()` deprecated/удалён.

## Гипотеза

Issue описывает потерю локализованных имён месяцев/дней после отказа от `strftime()`. Для `d.m.Y` locale не нужен. Для `%B`/`F` нужен `IntlDateFormatter`, а не `setlocale`.

Не чинить вслепую: это потенциальный breaking change и PHP 8 compatibility.

## Решение

WONTFIX / OBSOLETE.

1. `&locale` уже применяется в конструкторе через `setLocate()` → `setlocale(LC_ALL, $locale)`.
2. Дата в `site_content` собирается через `date()`, который не читает locale.
3. Форматы `%` специально конвертируются в токены `date()` ради PHP 8 (`strftime` deprecated/удалён).
4. Локализованные имена месяцев — отдельный enhancement на `IntlDateFormatter`, не баг текущего контракта.
5. Тело issue пустое, воспроизведения нет, 7 лет без активности.

Production-код не менять. Закрыть issue с пояснением.
