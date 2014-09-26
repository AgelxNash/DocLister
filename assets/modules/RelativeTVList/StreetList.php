<?php
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' &&
    $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['city']) && (int)$_POST['city'] > 0
) {
    define("MODX_API_MODE", true);
    include_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/index.php");
    $modx->db->connect();

    if (empty ($modx->config)) {
        $modx->getSettings();
    }
    echo $modx->runSnippet("DocLister", array(
        "controller" => "onetable",
        "table" => "street",
        "idType" => "documents",
        "documents" => "",
        "ignoreEmpty" => "1",
        "addWhereList" => "hide=0 AND parent_id=" . (int)$_POST['city'],
        "tpl" => '@CODE: <option value="[+id+]">[+name+]</option>',
    ));
}
