<?php
/**
 * DocLister snippet
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @date 08.03.2013
 * @version 1.0.3
 */
$dir = MODX_BASE_PATH. (isset($dir) ? $dir : 'assets/snippets/DocLister/');

if(!isset($dir)){
    die('Check param dir');
}else{
    $cfg['snippetFolder']=$dir;
}
include_once($dir . "core/DocLister.class.php");

$cfg=array();
$cfg=$modx->event->params;

if(isset($cfg['documents'])){
    $cfg['idType'] = "documents";
    $IDs = $cfg['documents'];
}else{
    $cfg['idType'] = "parents";
    if(!isset($cfg['parents'])){
        $cfg['parents']=$modx->documentIdentifier;
    }
    $IDs = $cfg['parents'];
}

$cfg['extender'] = isset($cfg['extender']) ? $cfg['extender'] : "";

if(!(isset($cfg['display']) && (int)$cfg['display']>0)){
    unset($cfg['paginate']);
    if(stristr($cfg['extender'],'paginate')){
        $cfg['extender']=str_replace("paginate","",$cfg['extender']);
    }
}

if(isset($cfg['paginate']) && $cfg['paginate']!=''){
    $cfg['extender']=implode(",",array($cfg['extender'],"paginate"));
}

if(isset($cfg['summary']) && $cfg['summary']!=''){
    $cfg['extender']=implode(",",array($cfg['extender'],"summary"));
}

if(isset($cfg['requestActive']) && $cfg['requestActive']!=''){
    $cfg['extender']=implode(",",array($cfg['extender'],"request"));
}

if(isset($controller)){
    preg_match('/^(\w+)$/iu', $controller, $controller);
    $controller=$controller[1];
}else{
    $controller="site_content";
}
$classname=$controller."DocLister";
if($classname!='DocLister' && file_exists($dir."core/controller/".$controller.".php") && !class_exists($classname,false)){
    include_once($dir."core/controller/".$controller.".php");
}
if(class_exists($classname,false) && $classname!='DocLister'){
   $DocLister=new $classname($modx,$cfg);
   $DocLister->setIDs($IDs);
   $data=$DocLister->getDocs();
   $DocLister->render();
   return isset($cfg['api']) ? $DocLister->getJSON($data,$cfg['api']) : $DocLister->render();
}
?>