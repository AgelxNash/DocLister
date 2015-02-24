<?php
if (IN_MANAGER_MODE != "true" || empty($modx) || !($modx instanceof DocumentParser)) {
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
if (!$modx->hasPermission('exec_module')) {
    header("location: " . $modx->getManagerPath() . "?a=106");
}

$ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

include_once(MODX_BASE_PATH.'assets/lib/Module/Action.php');
include_once(MODX_BASE_PATH.'assets/lib/Module/Helper.php');
include_once(MODX_BASE_PATH.'assets/lib/Module/Template.php');
include_once(MODX_BASE_PATH.'assets/lib/Helpers/FS.php');
include_once(MODX_BASE_PATH.'assets/lib/MODxAPI/modResource.php');

include_once(dirname(__FILE__) . "/Helper.class.php");
\DLCity\Helper::init($modx, isset($_REQUEST['mode']) ? $_REQUEST['mode'] : 'city');

include_once(dirname(__FILE__) . "/Template.class.php");
$TPL = new \DLCity\Template($modx, $ajax, dirname(__FILE__));

include_once(dirname(__FILE__) . "/Action.class.php");
\DLCity\Action::init($modx, $TPL, new modResource($modx));

if (!empty($action) && method_exists('\DLCity\Action', $action)) {
	$data = call_user_func_array(array('\DLCity\Action', $action), array());
	if (!is_array($data)) {
		$data = array();
	}
} else {
	$data = array();
}

$tpl = \DLCity\Action::$TPL;
if(!is_null($tpl)){
	$out = $TPL->showHeader();
	$out .= $TPL->showBody($tpl, $data);
	$out .= $TPL->showFooter();
}else{
	header('Content-type: application/json');
	$out = json_encode($data);
}
echo $out;