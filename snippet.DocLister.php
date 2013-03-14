<?php
/**
 * DocLister snippet
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @date 12.03.2013
 * @version 1.0.7
 */
 if(!defined('MODX_BASE_PATH')) {die('HACK???');}
 
$dir = realpath(MODX_BASE_PATH. (isset($dir) ? $dir : 'assets/snippets/DocLister/'));

require_once($dir . "/core/DocLister.class.php");

if(isset($controller)){
    preg_match('/^(\w+)$/iu', $controller, $controller);
    $controller=$controller[1];
}else{
    $controller="site_content";
}
$classname=$controller."DocLister";
if($classname!='DocLister' && file_exists($dir."/core/controller/".$controller.".php") && !class_exists($classname,false)){
    require_once($dir."/core/controller/".$controller.".php");
}

if(class_exists($classname,false) && $classname!='DocLister'){
   $DocLister=new $classname($modx,$modx->Event->params);
   $data=$DocLister->getDocs();
   return isset($cfg['api']) ? $DocLister->getJSON($data,$cfg['api']) : $DocLister->render();
}
?>