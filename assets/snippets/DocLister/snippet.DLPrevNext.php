<?php
$ID = $modx->documentObject['id'];
$params = is_array($modx->Event->params) ? $modx->Event->params : array();
$params = array_merge($params, array(
        'api' => 1,
        'debug' => '0'
    )
);

$json = $modx->runSnippet("DocLister", $params);
$children = jsonHelper::jsonDecode($json, array('assoc' => true));
$children = is_array($children) ? $children : array();

$self = $prev = $next = null;
foreach ($children as $key => $data) {
    if (!empty($self)) {
        $next = $key;
        break;
    }
    if ($key == $ID) {
        $self = $key;
        if (empty($prev)) {
            $prev = end($children);
            $prev = $prev['id'];
        }
    } else {
        $prev = $key;
    }
}
if (empty($next)) {
    reset($children);
    $next = current($children);
    $next = $next['id'];
}
$TPL = DLTemplate::getInstance($modx);

return $TPL->parseChunk($prevnextTPL, array(
    'prev' => $TPL->parseChunk($prevTPL, $children[$prev]),
    'next' => $TPL->parseChunk($nextTPL, $children[$next]),
));