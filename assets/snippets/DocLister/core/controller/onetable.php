<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

/**
 * all controller for show info from all table
 *
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 *
 * @TODO add controller for construct tree from table
 * @param introField =`` //introtext
 * @param contentField =`description` //content
 * @param table =`` //table name
 */

class onetableDocLister extends DocLister
{
    protected $table = 'site_content';
	protected $idField = 'id';
	protected $parentField = 'parent';
    /**
     * @absctract
     */

    public function getUrl($id = 0)
    {
        $id = $id > 0 ? $id : $this->modx->documentIdentifier;
        $link = $this->checkExtender('request') ? $this->extender['request']->getLink() : $this->getRequest();
        if($id == $this->modx->config['site_start']){
            $url = $this->modx->config['site_url'].($link != '' ? "?{$link}" : "");
        }else{
            $url = $this->modx->makeUrl($id, '', $link, $this->getCFGDef('urlScheme', ''));
        }
        return $url;
    }

    /**
     * @absctract
     */
    public function getDocs($tvlist = '')
    {
        if ($this->checkExtender('paginate')) {
            $pages = $this->extender['paginate']->init($this);
        } else {
            $this->setConfig(array('start' => 0));
        }
        $type = $this->getCFGDef('idType', 'parents');
        $this->_docs = ($type == 'parents') ? $this->getChildrenList() : $this->getDocList();

        return $this->_docs;
    }

    public function _render($tpl = '')
    {
        $out = '';
        if ($tpl == '') {
            $tpl = $this->getCFGDef('tpl', '');
        }
        if ($tpl != '') {
            $this->toPlaceholders(count($this->_docs), 1, "display"); // [+display+] - сколько показано на странице.

            $i = 1;
            $sysPlh = $this->renameKeyArr($this->_plh, $this->getCFGDef("sysKey", "dl"));
            $noneTPL = $this->getCFGDef("noneTPL", "");
            if (count($this->_docs) == 0 && $noneTPL != '') {
                $out = $this->parseChunk($noneTPL, $sysPlh);
            } else {
                /**
                 * @var $extUser user_DL_Extender
                 */
                if ($extUser = $this->getExtender('user')) {
                    $extUser->init($this, array('fields' => $this->getCFGDef("userFields", "")));
                }

                /**
                 * @var $extSummary summary_DL_Extender
                 */
                $extSummary = $this->getExtender('summary');

                /**
                 * @var $extPrepare prepare_DL_Extender
                 */
                $extPrepare = $this->getExtender('prepare');

                foreach ($this->_docs as $item) {
					$this->renderTPL = $tpl;
                    if ($extUser) {
                        $item = $extUser->setUserData($item); //[+user.id.createdby+], [+user.fullname.publishedby+], [+dl.user.publishedby+]....
                    }

                    $item[$this->getCFGDef("sysKey", "dl") . '.summary'] = $extSummary ? $this->getSummary($item, $extSummary) : '';

                    $item = array_merge($item, $sysPlh); //inside the chunks available all placeholders set via $modx->toPlaceholders with prefix id, and with prefix sysKey
                    $item[$this->getCFGDef("sysKey", "dl") . '.iteration'] = $i; //[+iteration+] - Number element. Starting from zero

                    $date = $this->getCFGDef('dateSource', 'pub_date');
                    $date = isset($item[$date]) ? $item[$date] + $this->modx->config['server_offset_time'] : '';
                    if ($date != '' && $this->getCFGDef('dateFormat', '%d.%b.%y %H:%M') != '') {
                        $item[$this->getCFGDef("sysKey", "dl") . '.date'] = strftime($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M'), $date);
                    }
					
					$findTpl = $this->renderTPL;
                    extract($this->uniformPrepare($item, $i), EXTR_SKIP);
					if ($this->renderTPL == '') {
                        $this->renderTPL = $findTpl;
                    }
					
                    if ($extPrepare) {
                        $item = $extPrepare->init($this, array('data' => $item));
                        if (is_bool($item) && $item === false) {
                            continue;
                        }
                    }
                    $tmp = $this->parseChunk($this->renderTPL, $item);
                    if ($this->getCFGDef('contentPlaceholder', 0) !== 0) {
                        $this->toPlaceholders($tmp, 1, "item[" . $i . "]"); // [+item[x]+] – individual placeholder for each iteration documents on this page
                    }
                    $out .= $tmp;
                    $i++;
                }
            }
            $out = $this->renderWrap($out);
        } else {
            $out = 'none TPL';
        }

        return $this->toPlaceholders($out);
    }

    public function getJSON($data, $fields, $array = array())
    {
        $out = array();
        $fields = is_array($fields) ? $fields : explode(",", $fields);
        $date = $this->getCFGDef('dateSource', 'pub_date');

        /**
         * @var $extSummary summary_DL_Extender
         */
        $extSummary = $this->getExtender('summary');

        foreach ($data as $num => $item) {
            switch (true) {
                case ((array('1') == $fields || in_array('summary', $fields)) && $extSummary):
                {
                    $out[$num]['summary'] = (mb_strlen($this->_docs[$num]['introtext'], 'UTF-8') > 0) ? $this->_docs[$num]['introtext'] : $this->getSummary($this->_docs[$num], $extSummary);
                    //without break
                }
                case ((array('1') == $fields || in_array('date', $fields)) && $date != 'date'):
                {
                    $tmp = (isset($this->_docs[$num][$date]) && $date != 'createdon' && $this->_docs[$num][$date] != 0 && $this->_docs[$num][$date] == (int)$this->_docs[$num][$date]) ? $this->_docs[$num][$date] : $this->_docs[$num]['createdon'];
                    $out[$num]['date'] = strftime($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M'), $tmp + $this->modx->config['server_offset_time']);
                    //without break
                }
            }
        }

        return parent::getJSON($data, $fields, $out);
    }

    protected function getDocList()
    {
        $out = array();
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
            $where = $this->getCFGDef('addWhereList', '');
            if ($where != '') {
                $where = array($where);
            }
            if ($sanitarInIDs != "''") {
                $where[] = "`{$this->getPK()}` IN ({$sanitarInIDs})";
            }

            if (!empty($where)) {
                $where = "WHERE " . implode(" AND ", $where);
            }
            $limit = $this->LimitSQL($this->getCFGDef('queryLimit', 0));
            $fields = $this->getCFGDef('selectFields', '*');
            $group = $this->getGroupSQL($this->getCFGDef('groupBy', ''));
            $rs = $this->dbQuery("SELECT {$fields} FROM {$this->table} {$where} {$group} {$this->SortOrderSQL($this->getPK())} {$limit}");

            $rows = $this->modx->db->makeArray($rs);
            $out = array();
            foreach ($rows as $item) {
                $out[$item[$this->getPK()]] = $item;
            }
        }
        return $out;
    }

	protected function getChildrenList()
    {
        $where = array();

        $tmpWhere = $this->getCFGDef('addWhereList', '');
        $tmpWhere = sqlHelper::trimLogicalOp($tmpWhere);
        if (!empty($tmpWhere)) {
            $where[] = $tmpWhere;
        }
        $sanitarInIDs = $this->sanitarIn($this->IDs);

        $tmpWhere = null;
        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
            $tmpWhere = "`{$this->getParentField()}` IN (" . $sanitarInIDs . ")";
		    $tmpWhere .= (($this->getCFGDef('showParent', '0')) ? "" : " AND {$this->getPK()} NOT IN(" . $sanitarInIDs . ")");
        }
        if (($addDocs = $this->getCFGDef('documents', '')) != '') {
            $addDocs = $this->sanitarIn($this->cleanIDs($addDocs));
            $tmpWhere = "((" . $tmpWhere . ") OR {$this->getPK()} IN({$addDocs}))";
        }
        if (!empty($tmpWhere)) {
            $where[] = $tmpWhere;
        }
        if (!empty($where)) {
            $where = "WHERE " . implode(" AND ", $where);
        } else {
            $where = '';
        }
        $fields = $this->getCFGDef('selectFields', '*');
        $sql = $this->dbQuery("SELECT DISTINCT {$fields} FROM " . $this->table . " " . $where . " " .
            $this->SortOrderSQL($this->getPK()) . " " .
            $this->LimitSQL($this->getCFGDef('queryLimit', 0))
        );
        $rows = $this->modx->db->makeArray($sql);
        $out = array();
        foreach ($rows as $item) {
            $out[$item[$this->getPK()]] = $item;
        }
        return $out;
    }
	
    // @abstract
    public function getChildrenCount()
    {
        $out = 0;
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
            $where = $this->getCFGDef('addWhereList', '');
            if ($where != '') {
                $where = array($where);
            }else{
				$where = array();
			}
            if ($sanitarInIDs != "''") {
				if ($sanitarInIDs != "''") {
					switch ($this->getCFGDef('idType', 'parents')) {
						case 'parents':
						{
							if ($this->getCFGDef('showParent', '0')) {
								$tmpWhere = "(`{$this->getParentField()}` IN ({$sanitarInIDs}) OR `{$this->getPK()}` IN({$sanitarInIDs}))";
							} else {
								$tmpWhere = "`{$this->getParentField()}` IN ({$sanitarInIDs}) AND `{$this->getPK()}` NOT IN({$sanitarInIDs})";
							}
							if (($addDocs = $this->getCFGDef('documents', '')) != '') {
								$addDocs = $this->sanitarIn($this->cleanIDs($addDocs));
								$whereArr[] = "((" . $tmpWhere . ") OR `{$this->getPK()}` IN({$addDocs}))";
							} else {
								$where[] = $tmpWhere;
							}

							break;
						}
						case 'documents':
						{
							$where[] = "`{$this->getPK()}` IN({$sanitarInIDs})";
							break;
						}
					}
				}
            }
            if (!empty($where)) {
                $where = "WHERE " . implode(" AND ", $where);
            }else{
				$where = '';
			}
			
            $group = $this->getGroupSQL($this->getCFGDef('groupBy', "`{$this->getPK()}`"));
            $rs = $this->dbQuery("SELECT count(*) FROM (SELECT count(*) FROM {$this->table} {$where} {$group}) as `tmp`");

            $out = $this->modx->db->getValue($rs);
        }
        return $out;
    }

    public function getChildrenFolder($id)
    {
		$sanitarInIDs = $this->sanitarIn($id);
		
        $tmp = $this->getCFGDef('addWhereFolder', '');
		$where = "`{$this->getParentField()}` IN ({$sanitarInIDs})";
        if (!empty($tmp)) {
            $where .= " AND " . $tmp;
        }
		 
        $rs = $this->dbQuery("SELECT `{$this->getPK()}` FROM {$this->table} WHERE {$where}");

        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[] = $item[$this->getPK()];
        }
        return $out;
    }
}