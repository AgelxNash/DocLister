# Задача: Issue #372 — TagSaver / site_content_tags

Дата создания: 2026-09-01
Приоритет: High
ID: DL-ISSUE-372
Parent: None
Статус: [DONE]
Классификация: FIXED

## Диагноз

Debug из issue показывает:
`t.name = 'Blue,Dark Grey,Green,...'`

TV/TagSaver отдаёт список через запятую, а `tagsSeparator` по умолчанию `||`, поэтому искалось одно несуществующее имя тега.

`get:myTags` при URL `?tag=Blue` — ошибка вызова: ключ GET должен быть `tag`.

Контроллер фильтрует документы по тегам, а не выводит список тегов документа.
