<?php
/**
 * DLcrumbs snippet
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 *
 * @todo: добавить поддержку отображения/скрытия главной страницы
 */
if (!defined('MODX_BASE_PATH')) {
	die('HACK???');
}
$_out = '';

$_parents=$modx->getParentIds($modx->documentObject['id']);
$_parents=array_reverse(array_values($_parents));

if(isset($showCurrent) && (int)$showCurrent>0){
    $_parents[] = $modx->documentObject['id'];
}

if(!empty($_parents)){
    $_options = array_merge(
        array(
            'config' => 'crumbs:core'
        ),
        !empty($modx->event->params) ? $modx->event->params : array(),
        array(
            'idType' => 'documents',
            'sortType' => 'doclist',
            'documents' => implode(",",$_parents)
        )
    );

    $_out = $modx->runSnippet("DocLister", $_options);
}
return $_out;