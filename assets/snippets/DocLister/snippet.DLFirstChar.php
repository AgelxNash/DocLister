<?php
/**
 * Группировка документов по первой букве
 *
 * [[DLFirstChar?
 * &documents=`2,4,23,3`
 * &idType=`documents`
 * &tpl=`@CODE:[+CharSeparator+][+OnNewChar+]<span class="brand_name"><a href="[+url+]">[+pagetitle+]</a></span><br />`
 * &tplOnNewChar=`@CODE:<div class="block"><strong class="bukva">[+char+]</strong> ([+total+])</div>`
 * &tplCharSeparator=`@CODE:</div>`
 * &orderBy=`BINARY pagetitle ASC`
 * ]]
 */

class FirstChar
{
    public static function get(array $data = array(), DocumentParser $modx, $_DocLister, prepare_DL_Extender $_extDocLister)
    {
        $char = mb_substr($data['pagetitle'], 0, 1, 'UTF-8');
        $oldChar = $_extDocLister->getStore('char');
        if ($oldChar !== $char) {
            $sanitarInIDs = $_DocLister->sanitarIn($_DocLister->getIDs());
            $where = sqlHelper::trimLogicalOp($_DocLister->getCFGDef('addWhereList', ''));
            $where = sqlHelper::trimLogicalOp(($where ? $where . ' AND ' : '') . $_DocLister->filtersWhere());
            $where = sqlHelper::trimLogicalOp(($where ? $where . ' AND ' : '') . "SUBSTRING(c.pagetitle,1,1) = '" . $modx->db->escape($char) . "'");

            if ($_DocLister->getCFGDef('idType', 'parents') == 'parents') {

                if ($where != '') {
                    $where .= " AND ";
                }
                $where = "WHERE {$where} c.parent IN (" . $sanitarInIDs . ")";
                if (!$_DocLister->getCFGDef('showNoPublish', 0)) {
                    $where .= " AND c.deleted=0 AND c.published=1";
                }
            } else {
                if ($sanitarInIDs != "''") {
                    $where .= ($where ? " AND " : "") . "c.id IN ({$sanitarInIDs}) AND";
                }
                $where = sqlHelper::trimLogicalOp($where);
                if ($_DocLister->getCFGDef('showNoPublish', 0)) {
                    if ($where != '') {
                        $where = "WHERE {$where}";
                    }
                } else {
                    if ($where != '') {
                        $where = "WHERE {$where} AND ";
                    } else {
                        $where = "WHERE {$where} ";
                    }
                    $where .= "c.deleted=0 AND c.published=1";
                }
            }
            $q = $_DocLister->dbQuery("SELECT count(c.id) as total FROM " . $_DocLister->getTable('site_content', 'c') . " " . $where);
            $total = $modx->db->getValue($q);
            $data['OnNewChar'] = $_DocLister->parseChunk($_DocLister->getCFGDef('tplOnNewChar'), compact("char", "total"));
            $_extDocLister->setStore('char', $char);

            if ($oldChar !== null) {
                $data['CharSeparator'] = $_DocLister->parseChunk($_DocLister->getCFGDef('tplCharSeparator'), compact("char", "total"));
            }
        }

        return $data;
    }
}

$config = $modx->event->params;
$prepare = isset($config['prepare']) ? explode(",", $config['prepare']) : array();
$prepare[] = 'FirstChar::get';
$config['prepare'] = implode(",", $prepare);
return $modx->runSnippet('DocLister', $config);