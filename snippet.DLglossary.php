<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}
/**
 * [[DLglossary? &fromget=`key` &loadfilter=`tv` &field=`tv.title` &tvList=`title` &setActive=`1`]]
 * Получить из GET массива значение по ключу key и использовать его в качестве источника буквы.
 * Если значение отсутствует, то пользователь будет перенаправлен на страницу 404.
 * Чтобы этого не произошло можно принудительно указать букву (значение по умолчанию) &char=`qwe`
 * Затем загрузить фильтры по TV параметрам и выбрать только те документы, у которых значение TV параметра title начинается выбранной буквы.
 * При фильтрации могут быть использованы сокращенные пулы символов:
 *  a-z||0-9||а-я (все буквы с a по z, либо все цифры с 0 по 9, либо все буквы с а по я)
 * Можно добавить свои пулы при помощи параметра regexp. Для примера &regexp=`её||ий` (восприятие букв "е","ё" и "и", "й" как одно целое).
 * Нужно учесть, что в таком случае в качестве источника буквы (из GET массива или из параметра char) должен быть указан именно этот набор
 * "её", а не по отдельности "е" или "ё".
 * Сменить разделитель || в наборах можно при помощи параметра regexpSep.
 *
 * По умолчанию сниппет не чувствителен к регистру. Т.е. что "А", что "а" - это одно и то же. Отключить это можно параметром &register=`1`
 *
 * Если фильтрация по tv параметрам не нужна, то можно убрать параметр loadfilter и изменить значение параметра field, например, на pagetitle
 * В таком случае условия выборки будут подставлены напрямую в WHERE часть SQL запроса минуая конструктор фильтров (см. описание параметра filters к docLister)
 *
 */
$dir = realpath(MODX_BASE_PATH . (isset($dir) ? $dir : 'assets/snippets/DocLister/'));
require_once($dir."/lib/sqlHelper.class.php");

switch(true){
    case (!empty($fromget)):{ /** Брать ли данные из GET */
        $data = $_GET;
        $from = $fromget;
        break;
    }
    case (!empty($frompost)):{ /** Брать ли данные из POST */
        $data = $_POST;
        $from = $frompost;
        break;
    }
    default:{
        $from = $data = null;
    }
}
if(!empty($from)){
    $char = isset($data[$from]) ? $data[$from] : '';
}
$char = !empty($char) ? $char : ''; /** С какого символа должен начинаться текст */

$field = !empty($field) ? $field : 'c.pagetitle'; /** Поле по которому фильтровать */

$setActive = !empty($setActive) ? true : false; /** Активировать наборы символов */

$regexpSep = !empty($regexpSep) ? $regexpSep : '||'; /** Разделитель в наборах регулярок */

$regexp = !empty($regexp) ? $regexp : 'a-z||0-9||а-я'; /** Наборы поддерживаемых регулярок */
$regexp = explode($regexpSep, $regexp);

$loadfilter = !empty($loadfilter) ? $loadfilter : ''; /** Какой фильтр загружать */

$register = empty($register) ? true : false; //Чувствительность к регистру.

if(preg_match("/\s+/", $field)){ /** SQL-injection protection :-)  */
    $char = '';
}

$out = $where = '';
$action = "like-r";

if(!empty($char)){
    if($register) $char = mb_strtolower($char, 'UTF-8');

    if(mb_strlen($char, 'UTF-8')==1){
		$char = preg_match('/^[а-яa-z0-9]/iu', $char) ? $char : '';
    }else{
        if($setActive && in_array($char, $regexp)){
            $action = "regexp";
            $char = "^[{$char}]";
        }else{
            $char = '';
        }
    }
}

if(!empty($char)){
    $_options = !empty($modx->event->params) ? $modx->event->params : array();
    if(!empty($loadfilter)){
        $field = end(explode(".", $field));
        if(!empty($_options['filters'])){
            $_options['filters'] = rtrim(trim($_options['filters']),";").";";
        }
        $_options['filters'] = "AND({$loadfilter}:{$field}:{$action}:{$char})";
    }else{
        $field = sqlHelper::tildeField($field);
        if($action=='regexp'){
            $where = $field." REGEXP '".$modx->db->escape($char)."'";
        }else{
            $where = sqlHelper::LikeEscape($modx, $field, $char, '=', '[+value+]%');
        }
        $_options['addWhereList'] = empty($_options['addWhereList']) ? $where : (sqlHelper::trimLogicalOp($_options['addWhereList'])." AND ".$where);
    }

	$out = $modx->runSnippet("DocLister", $_options);
}else{
	$modx->sendErrorPage();
}
return $out;