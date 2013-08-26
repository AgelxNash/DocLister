<?php
/**
 * site_content controller
 * @see http://modx.im/blog/addons/374.html
 *
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>
 * @date 26.08.2013
 * @version 1.0.23
 *
 * @TODO add parameter showFolder - include document container in result data whithout children document if you set depth parameter.
 * @TODO st placeholder [+dl.title+] if menutitle not empty
 */

class site_contentDocLister extends DocLister
{
    function __construct($modx, $cfg = array()){
        parent::__construct($modx,$cfg);
        if(!$this->_loadExtender('tv')){
            die('error');
        }
    }
    /**
     * @absctract
	 * @todo link maybe include other GET parameter with use pagination. For example - filter
     */
    public function getUrl($id = 0)
    {
        $id = $id > 0 ? $id : $this->modx->documentIdentifier;
        $link = $this->checkExtender('request') ? $this->extender['request']->getLink() : "";
        $url = ($id == $this->modx->config['site_start']) ? $this->modx->config['site_url'] . ($link != '' ? "?{$link}" : "") : $this->modx->makeUrl($id, '', $link, 'full');
        return $url;
    }

    /**
    * @absctract
    */
    public function getDocs($tvlist = '')
    {
        $this->extender['tv']->getAllTV_Name();

        if ($this->checkExtender('paginate')) {
            $this->extender['paginate']->init($this);
        } else {
            $this->setConfig(array('start' => 0));
        }
        $type = $this->getCFGDef('idType', 'parents');
        $this->_docs = ($type == 'parents') ? $this->getChildrenList() : $this->getDocList();

        if ($tvlist == '') {
            $tvlist = $this->getCFGDef('tvList', '');
        }
        if ($tvlist != '' && $this->checkIDs()) {

            $tv = $this->extender['tv']->getTVList(array_keys($this->_docs),$tvlist);

            foreach ($tv as $docID => $TVitem) {
                if (isset($this->_docs[$docID]) && is_array($this->_docs[$docID])) {
                    $this->_docs[$docID] = array_merge($this->_docs[$docID], $TVitem);
                } else {
                    unset($this->_docs[$docID]);
                }
            }
        }
        if (1 == $this->getCFGDef('tree', '0')) {
            $this->treeBuild('id', 'parent');
        }
        return $this->_docs;
    }


    /**
     * @todo set correct active placeholder if you work with other table. Because $item['id'] can differ of $modx->documentIdentifier (for other controller)
     * @todo set author placeholder (author name). Get id from Createdby OR editedby AND get info from extender user
     * @todo set filter placeholder with string filtering for insert URL
     */
    public function _render($tpl = '')
    {
        $out = '';
        if ($tpl == '') {
            $tpl = $this->getCFGDef('tpl', '@CODE:<a href="[+url+]">[+pagetitle+]</a><br />');
        }
        if ($tpl != '') {
            $date = $this->getCFGDef('dateSource', 'pub_date');

            $this->toPlaceholders(count($this->_docs), 1, "display"); // [+display+] - сколько показано на странице.

            $i = 1;
            $sysPlh = $this->renameKeyArr($this->_plh, $this->getCFGDef("sysKey", "dl"));
            if (count($this->_docs) > 0) {
                if ($this->checkExtender('user')) {
                    $this->extender['user']->init($this, array('fields' => $this->getCFGDef("userFields", "")));
                }
                foreach ($this->_docs as $item) {
                    $subTpl = '';
                    if ($this->checkExtender('user')) {
                        $item = $this->extender['user']->setUserData($item); //[+user.id.createdby+], [+user.fullname.publishedby+], [+dl.user.publishedby+]....
                    }

                    if ($this->checkExtender('summary')) {
                        if (mb_strlen($item['introtext'], 'UTF-8') > 0) {
                            $item['summary'] = $item['introtext'];
                        } else {
                            $item['summary'] = $this->extender['summary']->init($this, array("content" => $item['content'], "summary" => $this->getCFGDef("summary", "")));
                        }
                    }

                    $item = array_merge($item, $sysPlh); //inside the chunks available all placeholders set via $modx->toPlaceholders with prefix id, and with prefix sysKey
                    $item['title'] = ($item['menutitle'] == '' ? $item['pagetitle'] : $item['menutitle']);
                    $item['iteration'] = $i; //[+iteration+] - Number element. Starting from zero

                    $item['url'] = ($item['type'] == 'reference') ? $item['content'] : $this->getUrl($item['id']);

                    $item['date'] = (isset($item[$date]) && $date != 'createdon' && $item[$date] != 0 && $item[$date] == (int)$item[$date]) ? $item[$date] : $item['createdon'];
                    $item['date'] = $item['date'] + $this->modx->config['server_offset_time'];
                    if ($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M') != '') {
                        $item['date'] = strftime($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M'), $item['date']);
                    }

                    $class = array();
                    $class[] = ($i % 2 == 0) ? 'odd' : 'even';
                    if ($i == 0) {
                        $subTpl = $this->getCFGDef('tplFirst', $tpl);
                        $class[] = 'first';
                    }
                    if ($i == count($this->_docs)) {
                        $subTpl = $this->getCFGDef('tplLast', $tpl);
                        $class[] = 'last';
                    }
                    if ($this->modx->documentIdentifier == $item['id']) {
                        $subTpl = $this->getCFGDef('tplCurrent', $tpl);
                        $item[$this->getCFGDef("sysKey", "dl") . '.active'] = 1; //[+active+] - 1 if $modx->documentIdentifer equal ID this element
                        $class[] = 'current';
                    } else {
                        $item['active'] = 0;
                    }
                    $class = implode(" ", $class);
                    $item[$this->getCFGDef("sysKey", "dl") . '.class'] = $class;
                    if($subTpl==''){
                        $subTpl = $tpl;
                    }
                    if($this->checkExtender('prepare')){
                        $item = $this->extender['prepare']->init($this, $item);
                    }
                    $tmp = $this->parseChunk($subTpl, $item);

                    if ($this->getCFGDef('contentPlaceholder', 0) !== 0) {
                        $this->toPlaceholders($tmp, 1, "item[" . $i . "]"); // [+item[x]+] – individual placeholder for each iteration documents on this page
                    }
                    $out .= $tmp;
                    $i++;
                }
            } else {
                $noneTPL = $this->getCFGDef("noneTPL", "");
                $out = ($noneTPL != '') ? $this->parseChunk($noneTPL, $sysPlh) : '';
            }
            if (($this->getCFGDef("noneWrapOuter", "1") && count($this->_docs) == 0) || count($this->_docs) > 0) {
                $ownerTPL = $this->getCFGDef("ownerTPL", "");
                if ($ownerTPL != '') {
                    $out = $this->parseChunk($ownerTPL, array($this->getCFGDef("sysKey", "dl") . ".wrap" => $out));
                }
            }
        } else {
            $out = 'no template';
        }

        return $this->toPlaceholders($out);
    }

    public function getJSON($data, $fields, $array = array())
    {
        $out = array();
        $fields = is_array($fields) ? $fields : explode(",", $fields);
        $date = $this->getCFGDef('dateSource', 'pub_date');

        foreach ($data as $num => $item) {
            switch (true) {
                case ((array('1') == $fields || in_array('summary', $fields)) && $this->checkExtender('summary')):
                {
                    $out[$num]['summary'] = (mb_strlen($this->_docs[$num]['introtext'], 'UTF-8') > 0) ? $this->_docs[$num]['introtext'] : $this->extender['summary']->init($this, array("content" => $this->_docs[$num]['content'], "summary" => $this->getCFGDef("summary", "")));
                    //without break
                }
                case (array('1') == $fields || in_array('date', $fields)):
                {
                    $tmp = (isset($this->_docs[$num][$date]) && $date != 'createdon' && $this->_docs[$num][$date] != 0 && $this->_docs[$num][$date] == (int)$this->_docs[$num][$date]) ? $this->_docs[$num][$date] : $this->_docs[$num]['createdon'];
                    $out[$num]['date'] = strftime($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M'), $tmp + $this->modx->config['server_offset_time']);
                    //without break
                }
            }
        }

        return parent::getJSON($data, $fields, $out);
    }

    /**
     * document
     */

    // @abstract
    public function getChildrenCount()
    {
        $where = $this->getCFGDef('addWhereList', '');
        if ($where != '') {
            $where .= " AND ";
        }
        $tbl_site_content = $this->getTable('site_content','c');
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        $getCFGDef = $this->getCFGDef('showParent', '0') ? '' : "AND c.id NOT IN({$sanitarInIDs})";
        $fields = 'count(c.`id`) as `count`';
        $where = "{$where} c.parent IN ({$sanitarInIDs}) AND c.deleted=0 AND c.published=1 {$getCFGDef}";
        $rs = $this->modx->db->select($fields, $tbl_site_content, $where);
        return $this->modx->db->getValue($rs);
    }

    protected function getDocList()
    {
        $where = $this->getCFGDef('addWhereList', '');
        if ($where != '') {
            $where .= " AND ";
        }

        $tbl_site_content = $this->getTable('site_content','c');
        $where = "WHERE {$where} c.deleted=0 AND c.published=1";
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''") {
            $where .= " AND c.id IN ({$sanitarInIDs})";
        }

        $limit = $this->LimitSQL($this->getCFGDef('queryLimit', 0));
        $select = "c.*";
        $sort = $this->SortOrderSQL("if(c.pub_date=0,c.createdon,c.pub_date)");
        if (preg_match("/^ORDER BY (.*) /", $sort, $match)) {
            $TVnames = $this->extender['tv']->getTVnames();
            if (isset($TVnames[$match[1]])) {
                $tbl_site_content .= " LEFT JOIN " . $this->getTable("site_tmplvar_contentvalues") . " as tv
                    on tv.contentid=c.id AND tv.tmplvarid=" . $TVnames[$match[1]];
                $sort = str_replace("ORDER BY " . $match[1], "ORDER BY tv.value", $sort);
            }
        }
        $rs = $this->modx->db->query("SELECT {$select} FROM {$tbl_site_content} {$where} GROUP BY c.id {$sort} {$limit}");

        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[$item['id']] = $item;
        }
        return $out;
    }

    public function getChildernFolder($id)
    {
        /**
        * @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
        * @TODO: 5) Добавить фильтрацию по основным параметрам документа
        */
        $where = $this->getCFGDef('addWhereFolder', '');
        if ($where != '') {
            $where .= " AND ";
        }

        $tbl_site_content = $this->getTable('site_content','c');
        $sanitarInIDs = $this->sanitarIn($id);
        $where = "{$where} c.parent IN ({$sanitarInIDs}) AND c.deleted=0 AND c.published=1 AND c.isfolder=1";
        $rs = $this->modx->db->select('id', $tbl_site_content, $where);

        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[] = $item['id'];
        }
        return $out;
    }

    /**
    * @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
    * @TODO: 5) Добавить фильтрацию по основным параметрам документа
    */
    protected function getChildrenList()
    {
        $where = $this->getCFGDef('addWhereList', '');
        if ($where != '') {
            $where .= " AND ";
        }

        $sql = $this->modx->db->query("
			SELECT c.* FROM " . $this->getTable('site_content','c') . "
			WHERE " . $where . "
				c.parent IN (" . $this->sanitarIn($this->IDs) . ")
				AND c.deleted=0 
				AND c.published=1 " .
                (($this->getCFGDef('showParent', '0')) ? "" : "AND c.id NOT IN(" . $this->sanitarIn($this->IDs) . ") ") .
                $this->SortOrderSQL('if(c.pub_date=0,c.createdon,c.pub_date)') . " " .
                $this->LimitSQL($this->getCFGDef('queryLimit', 0))
        );
        $rows = $this->modx->db->makeArray($sql);
        $out = array();
        foreach ($rows as $item) {
            $out[$item['id']] = $item;
        }
        return $out;
    }
}

?>