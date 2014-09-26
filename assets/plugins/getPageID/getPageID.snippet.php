<?php
/**
 * getPageID
 * Получение id документа MODX по его URI
 *
 * @category    snippet
 * @version    0.01
 * @author      Agel_Nash <modx@agel-nash.ru>
 * @internal    @properties     &requestName=Имя GET переменной;input;getPageId
 * @internal    @category       API
 * @internal    @code           return require MODX_BASE_PATH.'assets/plugins/getPageID/getPageID.snippet.php';
 */
$requestName = (!empty($requestName) && is_scalar($requestName)) ? (string)$requestName : 'getPageId';
$uri = !empty($uri) && is_scalar($uri) ? (string)$uri : '';
$q = $parseUri = array();
$host = null;
$out = 0;
if (!empty($uri)) {
    $parseUri = parse_url($uri);
    $uri = isset($parseUri['path']) ? $parseUri['path'] : '/';
    $query = isset($parseUri['query']) ? $parseUri['query'] : '';
    if (!empty($query)) {
        parse_str($query, $q);
    }
}

if (!empty($remote)) {
    if (isset($parseUri['scheme']) && $parseUri['scheme'] == 'http') {
        $host = "http://";
    }
    if (!empty($host)) {
        $host = $host . $parseUri['host'] . '/';
    }
} else {
    $host = $modx->config['site_url'];
}
if (!empty($host)) {
    $url = $host . ltrim($uri, '/');
    $url .= '?';
    $url .= http_build_query(array_merge($q, array($requestName => 'dumpID')));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $out = 0;
    if ($json = curl_exec($ch)) {
        $json = json_decode($json, true);
        if (is_array($json) && isset($json['id'])) {
            $out = $json['id'];
        }
    }
}
return $out;