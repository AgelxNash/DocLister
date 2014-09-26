<?php
if (IN_MANAGER_MODE != "true" || empty($modx) || !($modx instanceof DocumentParser)) {
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
if (!$modx->hasPermission('exec_module')) {
    header("location: " . $modx->getManagerPath() . "?a=106");
}

$ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

include_once(dirname(__FILE__) . "/Helper.class.php");
\DLCity\Helper::init($modx, isset($_REQUEST['mode']) ? $_REQUEST['mode'] : 'city');

include_once(dirname(__FILE__) . "/Template.class.php");
$TPL = new \DLCity\Template($modx, $ajax);

include_once(dirname(__FILE__) . "/Action.class.php");
\DLCity\Action::init($modx, $TPL);

$out = $TPL->showHeader();

if (method_exists('\DLCity\Action', $action)) {
    $data = call_user_func_array(array('\DLCity\Action', $action), array());
} else {
    $data = array();
}
$tpl = \DLCity\Action::$TPL;

$out .= $TPL->showBody($tpl, $data);
$out .= $TPL->showFooter();

echo $out;