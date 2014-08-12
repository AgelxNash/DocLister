<?php
/**
 * getPageID
 * Получение id документа MODX по его URI
 *
 * @category    plugin
 * @version    0.01
 * @author      Agel_Nash <modx@agel-nash.ru>
 * @internal    @properties     &requestName=Имя GET переменной;input;getPageId
 * @internal    @events         OnPageNotFound, OnWebPageInit
 * @internal    @category       API
 * @internal    @code           include MODX_BASE_PATH."assets/plugins/getPageID/getPageID.plugin.php";
 */
$requestName = (!empty($requestName) && is_scalar($requestName)) ? (string)$requestName : 'getPageId';
$out = array();
switch ($modx->event->name) {
    case 'OnPageNotFound':
    {
        $out = array(
            'id' => 0,
            'method' => 'none'
        );
        break;
    }
    case 'OnWebPageInit':
    {
        $out = array(
            'id' => (int)$modx->documentIdentifier,
            'method' => (string)$modx->documentMethod
        );
        break;
    }
}

if (!empty($out) && isset($_GET[$requestName])) {
    header('Content-Type: application/json');
    echo json_encode($out);
    die();
}