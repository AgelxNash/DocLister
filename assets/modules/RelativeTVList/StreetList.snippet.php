<?php
$cityID = isset($cityID) ? (int)$cityID : 0;
$pageID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selfName = isset($selfName) ? $selfName : '';
$out = $wrap = $cityValue = '';

if (!empty($cityID) && $pageID > 0) {
    $cityValue = $modx->getTemplateVar($cityID, "id", $pageID);
    $cityValue = isset($cityValue['value']) ? $cityValue['value'] : '';
}

if (!empty($selfName)) {
    if ($pageID > 0) {
        $streetValue = $modx->getTemplateVar($selfName, "id", $pageID);
        $streetID = isset($streetValue['id']) ? $streetValue['id'] : '';
        $streetValue = isset($streetValue['value']) ? $streetValue['value'] : '';
    } else {
        $q = $modx->db->select("id", $modx->getFullTableName('site_tmplvars'), "name='" . $modx->db->escape($selfName) . "'");
        $streetID = $modx->db->getValue($q);
        $streetValue = '';
    }
    if (!empty($cityValue)) {
        $wrap = $modx->runSnippet("DocLister", array(
            "controller" => "onetable",
            "table" => "street",
            "idType" => "documents",
            "documents" => "",
            "ignoreEmpty" => "1",
            "addWhereList" => "hide=0 AND parent_id=" . (int)$cityValue,
            "streetDefault" => $streetValue,
            "prepare" => function (array $data = array(), DocumentParser $modx, onetableDocLister $_DocLister) {
                    $data['selected'] = ($_DocLister->getCFGDef('streetDefault') == $data['id']) ? 'selected="selected"' : '';
                    return $data;
                },
            "tpl" => '@CODE: <option value="[+id+]" [+selected+]>[+name+]</option>',
        ));
    }
}
$js = "<script>
	function showStreet(request) {
		var elm = document.getElementById('tv" . $streetID . "');
		console.log(elm);
		if (elm) {
		  	elm.innerHTML = request;
		  	elm.style.display = request!='' ? 'inline' :  'none';
       	}
   	}
</script>";
$out = $js;
$field_html = '<select id="tv[+field_id+]" name="tv[+field_id+]" size="1" onchange="documentDirty=true;">' . $wrap . '</select>';
return $out . $field_html;
?>