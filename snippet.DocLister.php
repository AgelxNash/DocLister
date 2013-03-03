<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Agel_Nash
 * Date: 19.12.12
 * Time: 0:19
 * To change this template use File | Settings | File Templates.
 */
$dir = MODX_BASE_PATH. (isset($dir) ? $dir : 'assets/snippets/DocLister/');
$cfg=array();

// @TODO: параметр showFolder - включать ли в выборку контейнеры (отличается от showParent тем, что если showFolder включен - то доки на заданой глубине не отображаются). Когда будут фильтры можно будет удалить параметр showFolder
// @TODO: поиск какие-TV переменные используются

$cfg=$modx->event->params;

if(!isset($dir)){
    die('Check param dir');
}else{
    $cfg['snippetFolder']=$dir;
}
include_once($dir . "core/DocLister.class.php");

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
   return isset($cfg['api']) ? json_encode($data) : $DocLister->render();
}
?>