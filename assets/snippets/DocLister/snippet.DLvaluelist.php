<?php
$params = array_merge($modx->event->params, array(
    'idType' => 'parents',
    'controller' => 'site_content',
    'api' => 'id,pagetitle',
    'debug' => '0'
));

$json = $modx->runSnippet("DocLister", $params);
$json = jsonHelper::jsonDecode($json);
$out = array();
foreach ($json as $item) {
    $out[] = $item->pagetitle . '==' . $item->id;
}
return implode("||", $out);