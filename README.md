### DocLister for MODX Evolution
[![Build Status](https://travis-ci.org/laravel/framework.svg)](https://travis-ci.org/laravel/framework)

Класс для вывода информации из таблиц по предопределенным правилам.
Если нет правил, то данные отображаются без дополнительной обработки и связи. Т.е. все поля и значения совпадают с базой данных.

Правила для обработки информации описаны в контроллерах.
Главный контроллер - **site_content** который определяет связь основных документов site_content с данными в TV-параметрах

На базе класса DocLister сформировано 6 сниппетов:
* **DocLister** - основной сниппет для вывода информации по принципу сниппетов Ditto и CatalogView
* **DLcrumbs** - для формирования хлебных крошек по принципу сниппета Breadcrumbs
* **DLglossary** - для фильтрации документов по первому символу в определенном поле
* **DLvaluelist** - для замены сниппета DropDownDocs
* **DLTemplate** - для замены $modx->parseChunk()
* **DLFirstChar** - выборка документов и группировках в блоках по первой букве
* **DLPrevNext** - цикличная навигация вперед/назад между соседними документами
* **DLBuildMenu** - Построение меню не ограниченой вложенности
* **DLReflect** - Построение списка дат
* **DLReflectFilter** - Фильтрация документов по датам
* **DLBeforeAfter** - Пагинация по прошедшим и предстоящим событиями с учетом текущей даты

### Полезные ссылки
---------
* **Обзор**: http://blog.agel-nash.ru/2013/9/doclister.html
* **Документация**: http://blog.agel-nash.ru/addon/doclister.html
* **Пример работы**: http://doclister.agelnash.ru

### Автор
---------
<table>
  <tr>
    <td><img src="http://www.gravatar.com/avatar/bf12d44182c98288015f65c9861903aa?s=220"></td>
	<td valign="top">
		<h4>Борисов Евгений
			<br />
			Agel Nash
		</h4>
		<a href="http://agel-nash.ru">http://agel-nash.ru</a><br />
		<br />
		<strong>ICQ</strong>: 8608196<br />
		<strong>Skype</strong>: agel.nash<br />
		<strong>Email</strong>: agel_nash@xaker.ru
	</td>
	<td valign="top">
		<h4>Реквизиты для доната<br /><br /></h4>
		<br /><br />
		<strong>WMZ</strong>: Z762708026453<br />
		<strong>WMR</strong>: R203864025267<br />
		<strong>PayPal</strong>: agel_nash@xaker.ru<br />
	</td>
  </tr>
</table>