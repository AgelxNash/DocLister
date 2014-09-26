<?php
include_once(MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');
$modx->event->params['TplMainOwner'] = \APIhelpers::getkey($modx->event->params, 'TplMainOwner', '@CODE: <ul id="nav" class="menu level-1">[+dl.wrap+]</ul>');
$modx->event->params['TplSubOwner'] = \APIhelpers::getkey($modx->event->params, 'TplSubOwner', '@CODE: <ul class="sub-menu level-[+dl.currentDepth+]">[+dl.wrap+]</ul>');
$modx->event->params['TplOneItem'] = \APIhelpers::getkey($modx->event->params, 'TplOneItem', '@CODE: <li id="menu-item-[+id+]" class="menu-item [+dl.class+]"><a href="[+url+]" title="[+e.title+]">[+title+]</a>[+dl.submenu+]</li>');
$modx->event->params['addWhereList'] = \APIhelpers::getkey($modx->event->params, 'addWhereList', 'c.hidemenu = 0');

$currentDepth = \APIhelpers::getkey($modx->event->params, 'currentDepth', 1);
$currentTpl = \APIhelpers::getkey($modx->event->params, 'TplDepth' . $currentDepth);
if (empty($currentTpl)) {
    $currentTpl = \APIhelpers::getkey($modx->event->params, 'TplOneItem', '@CODE: <li>[+pagetitle+]</li>');
}
$currentNoChildrenTpl = \APIhelpers::getkey($modx->event->params, 'TplNoChildrenDepth' . $currentDepth);
if (empty($currentNoChildrenTpl)) {
    $currentNoChildrenTpl = \APIhelpers::getkey($modx->event->params, 'TplNochildrenItem', $currentTpl);
}

$currentOwnerTpl = \APIhelpers::getkey($modx->event->params, 'TplOwner' . $currentDepth);
if (empty($currentOwnerTpl)) {
    $currentOwnerTpl = '@CODE: [+dl.wrap+]';
    if ($currentDepth == 1) {
        $currentOwnerTpl = \APIhelpers::getkey($modx->event->params, 'TplMainOwner', $currentOwnerTpl);
    } else {
        $currentOwnerTpl = \APIhelpers::getkey($modx->event->params, 'TplSubOwner', $currentOwnerTpl);
    }
}

return $modx->runSnippet('DocLister', array_merge(array(
        'orderBy' => 'menuindex ASC, id ASC'
    ), $modx->event->params, array(
        'idType' => 'parents',
        'parents' => \APIhelpers::getkey($modx->event->params, 'parents', 0),
        'params' => $modx->event->params,
        'tpl' => '@CODE: [+dl.tpl+]',
        'ownerTPL' => $currentOwnerTpl,
        'mainRowTpl' => $currentTpl,
        'noChildrenRowTPL' => $currentNoChildrenTpl,
        'noneWrapOuter' => '0',
        'prepare' => function ($data, DocumentParser $modx, DocLister $_DL) {
                $params = $_DL->getCFGDef('params', array());
                if ($_DL->getCfgDef('currentDepth', 1) < $_DL->getCFGDef('maxDepth', 5)) {
                    $params['currentDepth'] = $_DL->getCfgDef('currentDepth', 1) + 1;
                    $params['parents'] = $data['id'];
                    $data['dl.submenu'] = $modx->runSnippet('DLBuildMenu', $params);
                } else {
                    $data['dl.submenu'] = '';
                }
                $data['dl.currentDepth'] = $_DL->getCfgDef('currentDepth', 1);

                $tpl = empty($data['dl.submenu']) ? 'noChildrenRowTPL' : 'mainRowTpl';
                $data['dl.tpl'] = $_DL->parseChunk($_DL->getCfgDef($tpl), $data);
                return $data;
            }
    ))
);