<?php
/**
 * DocLister snippet
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 */
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}
$time = $modx->getMicroTime();
$dir = realpath(MODX_BASE_PATH . (isset($dir) ? $dir : 'assets/snippets/DocLister/'));

require_once($dir . "/core/DocLister.abstract.php");
require_once($dir . "/core/extDocLister.abstract.php");
require_once($dir . "/core/filterDocLister.abstract.php");

if (isset($controller)) {
    preg_match('/^(\w+)$/iu', $controller, $controller);
    $controller = $controller[1];
} else {
    $controller = "site_content";
}
$classname = $controller . "DocLister";
if ($classname != 'DocLister' && file_exists($dir . "/core/controller/" . $controller . ".php") && !class_exists($classname, false)) {
    require_once($dir . "/core/controller/" . $controller . ".php");
}

if (class_exists($classname, false) && $classname != 'DocLister') {
    $DocLister = new $classname($modx, $modx->Event->params);
    $DocLister->setTimeStart($time);
    $data = $DocLister->getDocs();
    $out = isset($modx->Event->params['api']) ? $DocLister->getJSON($data, $modx->Event->params['api']) : $DocLister->render();
    if(isset($_SESSION['usertype']) && $_SESSION['usertype']=='manager'){
        echo $DocLister->debug->showLog();
    }
    return $out;
}
?>