<?php

/**
 * RedirectMap
 * Управление редиректами
 *
 * @category    Module
 * @version    2.0.1
 * @author      Agel_Nash <modx@agel-nash.ru>
 * @internal    @category       SEO
 * @internal    @properties     &display=Правил на странице;input;20
 * @internal    @code           include_once(MODX_BASE_PATH."assets/modules/RedirectMap2/init.php");
 */

if (IN_MANAGER_MODE != "true" || empty($modx) || !($modx instanceof DocumentParser)) {
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
if (!$modx->hasPermission('exec_module')) {
    header("location: " . $modx->getManagerPath() . "?a=106");
}

if(!is_array($modx->event->params)){
	$modx->event->params = array();
}

$ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

include_once(dirname(__FILE__) . "/src/modRedirectMap.class.php");
include_once(MODX_BASE_PATH.'assets/lib/Module/Action.php');
include_once(MODX_BASE_PATH.'assets/lib/Module/Helper.php');
include_once(MODX_BASE_PATH.'assets/lib/Module/Template.php');
include_once(MODX_BASE_PATH.'assets/lib/Helpers/FS.php');

include_once(dirname(__FILE__) . "/src/Helper.class.php");
\RedirectMap\Helper::init($modx);

include_once(dirname(__FILE__) . "/src/Template.class.php");
$TPL = new \RedirectMap\Template($modx, $ajax, dirname(__FILE__));

include_once(dirname(__FILE__) . "/src/Action.class.php");
\RedirectMap\Action::init($modx, $TPL, new \RedirectMap\modRedirectMap($modx));

if (!empty($action) && method_exists('\RedirectMap\Action', $action)) {
    $data = call_user_func_array(array('\RedirectMap\Action', $action), array());
    if (!is_array($data)) {
        $data = array();
    }
} else {
    $data = array();
}

$tpl = \RedirectMap\Action::$TPL;
if(!is_null($tpl)){
	$out = $TPL->showHeader();
	$out .= $TPL->showBody($tpl, $data);
	$out .= $TPL->showFooter();
}else{
	header('Content-type: application/json');
	$out = json_encode($data);
}
echo $out;