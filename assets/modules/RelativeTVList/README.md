### Установка
---------
1) Устанавливаем [DocLister](https://github.com/AgelxNash/DocLister) последней версии

2) Создаем модуль с кодом
```php
include_once(MODX_BASE_PATH."assets/modules/RelativeTVList/init.php");
```
3) Создаем **сниппет CityList** с кодом
```php
<?php
return require MODX_BASE_PATH.'assets/modules/RelativeTVList/CityList.snippet.php';
```
4) Создаем **сниппет StreetList** с кодом
```php
<?php
return require MODX_BASE_PATH.'assets/modules/DLCity/StreetList.snippet.php';
```
5) Создаем **TV параметр City** с типом ввода Custom Input и возможными значениями 
```php
@EVAL return $modx->runSnippet('CityList', array('selfName'=>'City'));
```
Где в значении ключа selfName дублируется имя создаваемого TV параметра
6) Создаем **TV параметр Street** с типом ввода Custom Input и возможными значениями 
```php
@EVAL return $modx->runSnippet('StreetList', array(
	'cityID'=>1,
	'selfName'=>'Street'
));
```
Где в значении ключа cityID вместо 1 нужно указать ID TV параметра City созданного на 5 этапе установки. В значении ключа selfName дублируется имя создаваемого TV параметра.
7) Выполняем SQL комманду
```sql
CREATE TABLE `modx_city` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `hide` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `hide` (`hide`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `modx_street` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `hide` tinyint(1) DEFAULT '0',
  `parent_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hide` (`hide`),
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `name_parent` (`name`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
Не забыв заменить modx_ на префикс используемый вашей установкой CMS 

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
		<br />
		<strong>WMZ</strong>: Z762708026453<br />
		<strong>WMR</strong>: R203864025267<br />
		<strong>ЯД</strong>: 41001299480137<br />
		<strong>PayPal</strong>: agel_nash@xaker.ru<br />
	</td>
  </tr>
</table>