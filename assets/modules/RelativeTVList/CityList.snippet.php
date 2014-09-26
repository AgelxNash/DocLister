<?php
$selfName = isset($selfName) ? $selfName : '';
if (!empty($selfName)) {
    if ($pageID > 0) {
        $CityValue = $modx->getTemplateVar($selfName, "id", $pageID);
        $CityID = isset($CityValue['id']) ? $CityValue['id'] : '';
        $CityValue = isset($CityValue['value']) ? $CityValue['value'] : '';
    } else {
        $q = $modx->db->select("id", $modx->getFullTableName('site_tmplvars'), "name='" . $modx->db->escape($selfName) . "'");
        $CityID = $modx->db->getValue($q);
        $CityValue = '';
    }
}

$wrap = $modx->runSnippet("DocLister", array(
    "controller" => "onetable",
    "table" => "city",
    "idType" => "documents",
    "documents" => "",
    "ignoreEmpty" => "1",
    "addWhereList" => "hide=0",
    "cityDefault" => (int)$CityValue,
    "prepare" => function (array $data = array(), DocumentParser $modx, onetableDocLister $_DocLister) {
            $data['selected'] = ($_DocLister->getCFGDef('cityDefault') == $data['id']) ? 'selected="selected"' : '';
            return $data;
        },
    "tpl" => '@CODE: <option value="[+id+]" [+selected+]>[+name+]</option>',
));
return "<script>
	function loadStreet(el){
		var getStreet = new Ajax('/assets/modules/DLCity/StreetList.php', {method:'post', postBody:'city='+el.value, onComplete:showStreet});
		getStreet.request();
	}
</script><select id=\"tv[+field_id+]\" name=\"tv[+field_id+]\" size=\"1\" onchange=\"loadStreet(this);documentDirty=true;\">" . $wrap . "</select>";
?>