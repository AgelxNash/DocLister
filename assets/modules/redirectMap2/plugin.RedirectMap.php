<?php
/**
 * RedirectMap
 * Плагин редиректов для MODX Evolution
 *
 * @category    plugin
 * @version    2.0.1
 * @author      Agel_Nash <modx@agel-nash.ru>
 * @internal    @events         OnPageNotFound
 * @internal    @category       API
 * @internal    @code           include MODX_BASE_PATH."assets/modules/RedirectMap2/plugin.RedirectMap.php";
 */
$fullUri = $_SERVER['REQUEST_URI'];
$uri = parse_url($fullUri, PHP_URL_PATH);
$params = '';

$sql = "SELECT * FROM " . $modx->getFullTableName('redirect_map') . " WHERE `active`=1 AND `full_request`=1 AND `uri`='" . $modx->db->escape($fullUri) . "'";
$q = $modx->db->query($sql);
if ($modx->db->getRecordCount($q) == 0) {
    $sql = "SELECT * FROM " . $modx->getFullTableName('redirect_map') . " WHERE `active`=1 AND `full_request`=0 AND `uri`='" . $modx->db->escape($uri) . "'";
    $q = $modx->db->query($sql);
}
$rowRule = $modx->db->getRow($q);
if (!empty($rowRule['page'])) {
    if ($rowRule['save_get']) {
        $params = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    }
    $url = $modx->makeUrl($rowRule['page'], '', $params, 'full');
    $modx->sendRedirect($url, 0, 'REDIRECT_HEADER', 'HTTP/1.1 301 Moved Permanently');
}