<?php
/**
 * DLcrumbs snippet
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 */
if (!defined('MODX_BASE_PATH')) {
	die('HACK???');
}

$_parents=$modx->getParentIds($modx->documentObject['id']);
$_parents=array_reverse(array_values($_parents));
$_parents[] = $modx->documentObject['id'];

$_options = array_merge(array(
    'tpl'=>'@CODE:<li><a href="[+url+]" title="[+title+]">[+title+]</a></li>',
    'tplCurrent'=>'@CODE:<li><span>[+title+]</span></li>',
    'ownerTPL'=>'@CODE:<nav class="breadcrumbs"><ul>[+crumbs.wrap+]</ul></nav>',
    'sortType' => 'doclist',
    'sysKey'=>'crumbs',
    'documents'=>implode(",",$_parents)
), !empty($modx->event->params) ? $modx->event->params : array());

return $modx->runSnippet("DocLister", $_options);
