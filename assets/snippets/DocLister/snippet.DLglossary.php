<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

$dir = realpath(MODX_BASE_PATH . (isset($dir) ? $dir : 'assets/snippets/DocLister/'));
require_once($dir . "/lib/sqlHelper.class.php");

switch (true) {
    case (!empty($fromget)):
    {
        /** Брать ли данные из GET */
        $data = $_GET;
        $from = $fromget;
        break;
    }
    case (!empty($frompost)):
    {
        /** Брать ли данные из POST */
        $data = $_POST;
        $from = $frompost;
        break;
    }
    default:
        {
        $from = $data = null;
        }
}
if (!empty($from)) {
    $char = (isset($data[$from]) && is_scalar($data[$from])) ? $data[$from] : null;
}
$char = (!empty($char) || (isset($char) && $char == 0)) ? $char : '';
/** С какого символа должен начинаться текст */

$field = !empty($field) ? $field : 'c.pagetitle';
/** Поле по которому фильтровать */

$setActive = !empty($setActive) ? true : false;
/** Активировать наборы символов */

$regexpSep = !empty($regexpSep) ? $regexpSep : '||';
/** Разделитель в наборах регулярок */

$regexp = !empty($regexp) ? $regexp : 'a-z||0-9||а-я';
/** Наборы поддерживаемых регулярок */
$regexp = explode($regexpSep, $regexp);

$loadfilter = !empty($loadfilter) ? $loadfilter : '';
/** Какой фильтр загружать */

$register = empty($register) ? true : false; //Чувствительность к регистру.

if (preg_match("/\s+/", $field)) {
    /** SQL-injection protection :-)  */
    $char = '';
}

$out = $where = '';
$action = "like-r";

if (!is_null($char)) {
    if ($register) $char = mb_strtolower($char, 'UTF-8');

    if (mb_strlen($char, 'UTF-8') == 1) {
        $char = preg_match('/^[а-яa-z0-9]/iu', $char) ? $char : null;
    } else {
        if ($setActive && in_array($char, $regexp)) {
            $action = "regexp";
            $char = "^[{$char}]";
        } else {
            $char = null;
        }
    }
}

if (!is_null($char)) {
    $_options = !empty($modx->event->params) ? $modx->event->params : array();
    if (!empty($loadfilter)) {
        $field = end(explode(".", $field));
        if (!empty($_options['filters'])) {
            $_options['filters'] = rtrim(trim($_options['filters']), ";") . ";";
        }
        $_options['filters'] = "AND({$loadfilter}:{$field}:{$action}:{$char})";
    } else {
        $field = sqlHelper::tildeField($field);
        if ($action == 'regexp') {
            $where = $field . " REGEXP '" . $modx->db->escape($char) . "'";
        } else {
            $where = sqlHelper::LikeEscape($modx, $field, $char, '=', '[+value+]%');
        }
        $_options['addWhereList'] = empty($_options['addWhereList']) ? $where : (sqlHelper::trimLogicalOp($_options['addWhereList']) . " AND " . $where);
    }

    $out = $modx->runSnippet("DocLister", $_options);
} else {
    $modx->sendErrorPage();
}
return $out;