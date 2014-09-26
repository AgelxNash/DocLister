<?php
if (IN_MANAGER_MODE != 'true') {
    die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');
}
$docID = !empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

$q = $modx->db->query("SELECT * FROM " . $modx->getFullTableName("site_modules") . " WHERE `modulecode` LIKE '%/RedirectMap/init.php%'");
$module = $modx->db->getRow($q);
$showLink = false;
if (!empty($module['id']) && $module['disabled'] == 0) {
    $q = $modx->db->query("SELECT * FROM " . $modx->getFullTableName("site_module_access") . " WHERE `module`='" . $module['id'] . "'");
    $q = $modx->db->makeArray($q);
    $group = array();
    foreach ($q as $item) {
        $group[] = $item['usergroup'];
    }
    if (!empty($group)) {

        $showLink = (bool)$modx->db->getValue("SELECT count(*) FROM evo_member_groups WHERE member='" . $modx->getLoginUserID('mgr') . "' AND user_group IN (" . implode(',', $group) . ")");
    } else {
        $showLink = true;
    }

    if ($showLink) {
        $showLink = $module['id'];
    }
}
if ($showLink && $docID > 0) {
    $params = array(
        'mode' => 'list',
        'a' => 112,
        'action' => 'filter',
        'id' => $showLink,
        'method' => 'doc',
        'doc' => $docID
    );
    $out = '<div class="actionButtons" style="margin:5px 0px">
        <a href="' . MODX_MANAGER_URL . '?' . http_build_query($params) . '">
            <img src="media/style/' . $manager_theme . '/images/icons/table.gif">
            Управление редиректами
        </a>
    </div>';
} else {
    $out = '';
}
if ($docID > 0) {
    $out .= $modx->runSnippet('DocLister', array(
        'controller' => 'onetable',
        'table' => 'redirect_map',
        'addWhereList' => "`page`='" . $docID . "'",
        'tpl' => '@CODE: <li class="[+class+]">[+uri+]</li>',
        'ownerTPL' => '@CODE: <style>.inactive{color: #aaa}</style><ul>[+dl.wrap+]</ul>',
        'id' => 'dl',
        'debug' => 0,
        'noneTPL' => '@CODE: Нет правил для перенаправления',
        'noneWrapOuter' => 0,
        'paginate' => 'pages',
        'idType' => 'documents',
        'ignoreEmpty' => 1,
        'prepare' => function (array $data = array()) {
                $data['class'] = $data['active'] ? 'active' : 'inactive';
                return $data;
            }
    ));
}
echo '<input name="tv' . $row['id'] . '" type="hidden" value="" />' . $out;