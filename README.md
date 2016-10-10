### DocLister for MODX Evolution
[![CMS MODX Evolution](https://img.shields.io/badge/CMS-MODX%20Evolution-brightgreen.svg)](https://github.com/modxcms/evolution) [![Build Status](https://img.shields.io/travis/AgelxNash/DocLister/master.svg?maxAge=2592000)](https://travis-ci.org/AgelxNash/DocLister) [![Issues](https://img.shields.io/github/issues-closed-raw/AgelxNash/DocLister.svg?maxAge=2592000)](https://github.com/AgelxNash/DocLister/issues) [![Code quality](https://img.shields.io/scrutinizer/g/AgelxNash/DocLister.svg?maxAge=2592000)](https://scrutinizer-ci.com/g/AgelxNash/DocLister/) [![Documentation](https://img.shields.io/badge/Documentation-processed-orange.svg)](https://github.com/evolution-cms/docs/tree/master/03_Extras/04_Snippets/DocLister) [![License](https://img.shields.io/github/license/AgelxNash/DocLister.svg?maxAge=2592000)](https://github.com/AgelxNash/DocLister/blob/master/license.txt)

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
* **Документация**: https://github.com/evolution-cms/docs/tree/master/03_Extras/04_Snippets/DocLister
* **Статьи про DocLister**: http://modx.im/tag/doclister/

### Компоненты на базе DocLister
---------
* [SimpleGallery](https://github.com/Pathologic/SimpleGallery)
* [SimpleTube](https://github.com/Pathologic/SimpleTube)
* [SimpleFiles](https://github.com/Pathologic/SimpleFiles)
* [FormLister](https://github.com/Pathologic/FormLister)
* [FastImageTV](https://github.com/Pathologic/FastImageTV)
* [DLRequest](https://github.com/Pathologic/DLRequest)
* [evoSearch](https://github.com/webber12/evoSearch)
* [eFilter](https://github.com/webber12/eFilter)
* [Selector](https://github.com/Pathologic/Selector)

### Авторы
---------
<table>
  <tr>
    <td valign="center" align="center"><img src="http://www.gravatar.com/avatar/bf12d44182c98288015f65c9861903aa?s=180"></td>
	<td valign="top">
		<h4>Борисов Евгений
			<br />
			Agel Nash
		</h4>
		<a href="http://agel-nash.ru">http://agel-nash.ru</a><br />
		<br />
		<strong>ICQ</strong>: 8608196<br />
		<strong>Email</strong>: modx@agel-nash.ru
	</td>
	<td valign="top">
		<h4>Реквизиты для доната<br /><br /></h4>
		<br /><br />
		<strong>WMZ</strong>: Z762708026453<br />
		<strong>WMR</strong>: R203864025267<br />
	</td>
  </tr>
  <tr>
    <td valign="center" align="center"><img src="http://www.gravatar.com/avatar/b91e37b9ae5b4869b4508e8a5326200a?s=160"></td>
	<td valign="top">
		<h4>Максим
			<br />
			Pathologic
		</h4>
		<a href="https://github.com/Pathologic">@Pathologic</a><br />
		<br />
		<strong>Email</strong>: m@xim.name
	</td>
	<td valign="top">
		<h4>Реквизиты для доната<br /><br /></h4>
		<br /><br />
		<strong>ЯД</strong>: 410011458897796<br />
	</td>
  </tr>
</table>

### Как прислать PullRequest
---------
#### 1. Сделайте ["форк"](http://help.github.com/fork-a-repo/) репозитория AgelxNash/DocLister, а затем клонируйте его в свою локальную среду разработки
```bash
git clone git@github.com:имя-вашего-пользователя/DocLister.git
```

#### 2. Добавьте основой репозиторий DocLister как удаленный (remote) с названием "upstream"
Перейдите в директорию куда вы сделали клон на первом шаге и выполните следующую команду:
```bash
git remote add upstream git://github.com/AgelxNash/DocLister.git
```

#### 3. Получите последние изменения кода из основного репозитория DocLister
```bash
git fetch upstream
```
Вы должны начинать с этого шага для каждого нового патча, чтобы быть уверенными, что работаете с кодом содержащим последние изменения.

#### 4. Создайте новую ветку основанную на текущей master ветке DocLister
```bash
git checkout upstream/master
git checkout -b 999-название-вашей-ветки
```

#### 5. Пишем код
Убеждаемся, что он работает :)

#### 6. Cделайте коммит изменений
Добавляем файлы c изменениями:
```bash
# один файл
git add путь/до/вашего/файла.php
# все измененные файлы
git add .
```
Если добавить в описание коммита номер тикета #XXX, тогда GitHub автоматически свяжет его с тикетом над которым вы работаете:
```bash
git commit -m "Описание коммита для тикета #42"
```

#### 7. Получите последние изменения кода из upstream (добавили на втором шаге)
```bash
git pull upstream master
```
Опять же таким образом убеждаемся, что ваша ветка содержит последние изменения. Если возникли конфликты, исправляем и снова комитим.

#### 8. Имея код без конфликтов отравьте изменения на github
```bash
git push -u origin 999-название-вашей-ветки
```

#### 9. Пришлите [pull request](http://help.github.com/send-pull-requests/) в основной репозиторий DocLister
Перейдите в свой репозиторий на GitHub'e и нажмите "Pull Request", выберите свою ветку справа и добавьте описание вашего "Pull Request'a", чтобы GitHub автоматически связал его с тикетом добавьте в комментарий номер тикета '#999'.

#### 10. Ожидайте рассмотрения вашего кода
Кто-то рассмотрит ваш код и может быть попросит внести изменения, если это произошло возвращайтесь к 5 шагу.

#### 11. Удаление ветки
После того как ваш код приняли или отклонили вы можете удалить ветку из локального репозитория и GitHub'a
```bash
git checkout master
git branch -D 999-название-вашей-ветки
git push origin --delete 999-название-вашей-ветки
```

#### Все шаги кратко
```bash
git clone git@github.com:ваше-имя-пользователя/DocLister.git
git remote add upstream git://github.com/AgelxNash/DocLister.git
git fetch upstream
git checkout upstream/master
git checkout -b 999-название-вашей-ветки
/* пишем код */
git add путь/до/вашего/файла.php
git commit -m "Описание коммита для тикета #42"
git pull upstream master
git push -u origin 999-название-вашей-ветки
```
