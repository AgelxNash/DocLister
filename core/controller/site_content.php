<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

/**
 * site_content controller
 * @see http://modx.im/blog/addons/374.html
 *
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author Agel_Nash <Agel_Nash@xaker.ru>, kabachello <kabachnik@hotmail.com>
 *
 * @TODO add parameter showFolder - include document container in result data whithout children document if you set depth parameter.
 * @TODO st placeholder [+dl.title+] if menutitle not empty
 */

class site_contentDocLister extends DocLister
{
    /**
     * Экземпляр экстендера TV
     *
     * @var null|xNop|tv_DL_Extender
     */
    protected $extTV = null;

    /**
     * Экземпляр экстендера пагинации
     * @var null|paginate_DL_Extender
     */

    protected $extPaginate = null;
    
    function __construct($modx, $cfg = array(), $startTime = null){
        parent::__construct($modx,$cfg, $startTime);
        $this->extTV = $this->getExtender('tv', true, true);
    }
    /**
     * @absctract
     */
    public function getUrl($id = 0)
    {
        $id = $id > 0 ? $id : $this->modx->documentIdentifier;
        /**
         * Экземпляр экстендера REQUEST
         *
         * @var $request null|request_DL_Extender
         */
        $request = $this->getExtender('request');
        $link = $request ? $request : $this->getRequest();
        $url = ($id == $this->modx->config['site_start']) ? $this->modx->config['site_url'] . ($link != '' ? "?{$link}" : "") : $this->modx->makeUrl($id, '', $link, 'full');
        return $url;
    }

    /**
    * @absctract
    */
    public function getDocs($tvlist = '')
    {
        if ($tvlist == '') {
            $tvlist = $this->getCFGDef('tvList', '');
        }

        $this->extTV->getAllTV_Name();

        if ($this->extPaginate = $this->getExtender('paginate')) {
            $this->extPaginate->init($this);
        } else {
            $this->setConfig(array('start' => 0));
        }
        $type = $this->getCFGDef('idType', 'parents');
        $this->_docs = ($type == 'parents') ? $this->getChildrenList() : $this->getDocList();
        if ($tvlist != '' && count($this->_docs)>0) {
            $tv = $this->extTV->getTVList(array_keys($this->_docs),$tvlist);
            if(!is_array($tv)){
                $tv = array();
            }
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

                /**
                 * @var $extJotCount jotcount_DL_Extender
                 */
                $extJotCount = $this->getCFGdef('jotcount',0) ? $this->getExtender('jotcount',true) : NULL;

				if ($extJotCount) {
					$comments = $extJotCount->countComments(array_keys($this->_docs));
				}
				
                foreach ($this->_docs as $item) {
                    $subTpl = '';
                    if ($extUser){
                        $item = $extUser->setUserData($item); //[+user.id.createdby+], [+user.fullname.publishedby+], [+dl.user.publishedby+]....
                    }

                    if ($extSummary) {
                        if (mb_strlen($item['introtext'], 'UTF-8') > 0) {
                            $item['summary'] = $item['introtext'];
                        } else {
                            $item['summary'] = $extSummary->init($this, array("content" => $item['content'], "summary" => $this->getCFGDef("summary", "")));
                        }
                    }
                    if ($extJotCount) {
						$item['jotcount'] = isset($comments[$item['id']]) ? $comments[$item['id']] : 0;
                    }


                    $item = array_merge($item, $sysPlh); //inside the chunks available all placeholders set via $modx->toPlaceholders with prefix id, and with prefix sysKey
                    $item['title'] = ($item['menutitle'] == '' ? $item['pagetitle'] : $item['menutitle']);

                    $item['iteration'] = $i; //[+iteration+] - Number element. Starting from zero
                    $item[$this->getCFGDef("sysKey", "dl") . '.full_iteration'] = ($this->extPaginate) ? ($i + $this->getCFGDef('display', 0) * ($this->extPaginate->currentPage()-1)) : $i;

                    $item['url'] = ($item['type'] == 'reference') ? (is_numeric($item['content']) ? $this->modx->makeUrl($item['content']) : $item['content']) : $this->modx->makeUrl($item['id']);

                    $item['date'] = (isset($item[$date]) && $date != 'createdon' && $item[$date] != 0 && $item[$date] == (int)$item[$date]) ? $item[$date] : $item['createdon'];
                    $item['date'] = $item['date'] + $this->modx->config['server_offset_time'];
                    if ($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M') != '') {
                        $item['date'] = strftime($this->getCFGDef('dateFormat', '%d.%b.%y %H:%M'), $item['date']);
                    }

                    $class = array();
					
					$subTpl = $this->getCFGDef('tplId'.$i, $tpl);

                    $iterationName = ($i % 2 == 0) ? 'Odd' : 'Even';
					$class[] = strtolower($iterationName);
					
					$subTpl = $this->getCFGDef('tpl'.$iterationName, $subTpl);
					
					if ($i == 1) {
                        $subTpl = $this->getCFGDef('tplFirst', $subTpl);
                        $class[] = 'first';
                    }
                    if ($i == count($this->_docs)) {
                        $subTpl = $this->getCFGDef('tplLast', $subTpl);
                        $class[] = 'last';
                    }
                    if ($this->modx->documentIdentifier == $item['id']) {
                        $subTpl = $this->getCFGDef('tplCurrent', $subTpl);
                        $item[$this->getCFGDef("sysKey", "dl") . '.active'] = 1; //[+active+] - 1 if $modx->documentIdentifer equal ID this element
                        $class[] = 'current';
                    } else {
                        $item[$this->getCFGDef("sysKey", "dl") . '.active'] = 0;
                    }
                    $class = implode(" ", $class);
                    $item[$this->getCFGDef("sysKey", "dl") . '.class'] = $class;
                    if($subTpl==''){
                        $subTpl = $tpl;
                    }
                    if($extPrepare){
                        $item = $extPrepare->init($this, $item);
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

        /**
         * @var $extSummary summary_DL_Extender
         */
        $extSummary = $this->getExtender('summary');

        foreach ($data as $num => $item) {
            switch (true) {
                case ((array('1') == $fields || in_array('summary', $fields)) && $extSummary):
                {
                    $out[$num]['summary'] = (mb_strlen($this->_docs[$num]['introtext'], 'UTF-8') > 0) ? $this->_docs[$num]['introtext'] : $extSummary->init($this, array("content" => $this->_docs[$num]['content'], "summary" => $this->getCFGDef("summary", "")));
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
        $out = 0;
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')){
            $where = $this->getCFGDef('addWhereList', '');
			$where = sqlHelper::trimLogicalOp($where);
            $where = ($where ? $where . ' AND ' : '') . $this->_filters['where'];
            if ($where != '' && $this->_filters['where'] != '') {
                $where .= " AND ";
            }
			$where = sqlHelper::trimLogicalOp($where);
			
            $where = "WHERE {$where}";
            $whereArr = array();
            if(!$this->getCFGDef('showNoPublish', 0)){
                $whereArr[]="c.deleted=0 AND c.published=1";
            }

            $tbl_site_content = $this->getTable('site_content','c');

            if($sanitarInIDs != "''"){
                switch($this->getCFGDef('idType', 'parents')){
                    case 'parents':{
                        if($this->getCFGDef('showParent', '0')) {
                            $whereArr[]="(c.parent IN ({$sanitarInIDs}) OR c.id IN({$sanitarInIDs}))";
                        }else{
                            $whereArr[]="c.parent IN ({$sanitarInIDs}) AND c.id NOT IN({$sanitarInIDs})";
                        }
                        break;
                    }
                    case 'documents':{
                        $whereArr[]="c.id IN({$sanitarInIDs})";
                        break;
                    }
                }
            }
            $fields = $this->getCFGDef('selectFields', 'c.*');
            $from = $tbl_site_content . " " . $this->_filters['join'];
            $where = sqlHelper::trimLogicalOp($where);

            if(trim($where) != 'WHERE'){
                $where .=" AND ";
            }

            $where .= implode(" AND ", $whereArr);
            $where = sqlHelper::trimLogicalOp($where);

            if(trim($where)=='WHERE'){
                $where = '';
            }
            $group = $this->getGroupSQL($this->getCFGDef('groupBy', 'c.id'));
            $sort = $this->SortOrderSQL("if(c.pub_date=0,c.createdon,c.pub_date)");
            list($from, $sort) = $this->injectSortByTV($from, $sort);

            $rs = $this->dbQuery("SELECT count(*) FROM (SELECT count(*) FROM {$from} {$where} {$group}) as `tmp`");
            $out = $this->modx->db->getValue($rs);
        }
        return $out;
    }

    protected function getDocList()
    {
        $out = array();
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''" || $this->getCFGDef('ignoreEmpty', '0')) {
            $where = $this->getCFGDef('addWhereList', '');
			$where = sqlHelper::trimLogicalOp($where);
            
			$where = ($where ? $where . ' AND ' : '') . $this->_filters['where'];
            $where = sqlHelper::trimLogicalOp($where);

            $tbl_site_content = $this->getTable('site_content','c');
            if($sanitarInIDs != "''"){
				$where .= ($where ? " AND " : "") . "c.id IN ({$sanitarInIDs}) AND";
			}
            $where = sqlHelper::trimLogicalOp($where);

            if($this->getCFGDef('showNoPublish', 0)){
                if($where!=''){
                    $where = "WHERE {$where}";
                }else{
                    $where = '';
                }
            }else{
                if($where!=''){
                    $where = "WHERE {$where} AND ";
                }else{
                    $where = "WHERE {$where} ";
                }
                $where .= "c.deleted=0 AND c.published=1";
            }


            $fields = $this->getCFGDef('selectFields', 'c.*');
            $group = $this->getGroupSQL($this->getCFGDef('groupBy', 'c.id'));
            $sort = $this->SortOrderSQL("if(c.pub_date=0,c.createdon,c.pub_date)");
            list($tbl_site_content, $sort) = $this->injectSortByTV($tbl_site_content.' '.$this->_filters['join'], $sort);

            $limit = $this->LimitSQL($this->getCFGDef('queryLimit', 0));

            $rs = $this->dbQuery("SELECT {$fields} FROM {$tbl_site_content} {$where} {$group} {$sort} {$limit}");

            $rows = $this->modx->db->makeArray($rs);

            foreach ($rows as $item) {
                $out[$item['id']] = $item;
            }
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
		$where = sqlHelper::trimLogicalOp($where);
        if ($where != '') {
            $where .= " AND ";
        }
		
        $tbl_site_content = $this->getTable('site_content','c');
        $sanitarInIDs = $this->sanitarIn($id);
        if($this->getCFGDef('showNoPublish', 0)){
            $where = "WHERE {$where} c.parent IN ({$sanitarInIDs}) AND c.isfolder=1";
        }else{
            $where = "WHERE {$where} c.parent IN ({$sanitarInIDs}) AND c.deleted=0 AND c.published=1 AND c.isfolder=1";
        }

        $rs = $this->dbQuery("SELECT id FROM {$tbl_site_content} {$where}");

        $rows = $this->modx->db->makeArray($rs);
        $out = array();
        foreach ($rows as $item) {
            $out[] = $item['id'];
        }
        return $out;
    }

    protected function injectSortByTV($table, $sort){
        $out = $this->getExtender('tv', true, true)->injectSortByTV($table, $sort);
        if(!is_array($out) || empty($out)){
            $out = array($table, $sort);
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
		$where = sqlHelper::trimLogicalOp($where);
		
        $where = ($where ? $where . ' AND ' : '') . $this->_filters['where'];
		$where = sqlHelper::trimLogicalOp($where);
		
        if ($where != '') {
            $where .= " AND ";
        }

        $tbl_site_content = $this->getTable('site_content','c');

        $sort = $this->SortOrderSQL("if(c.pub_date=0,c.createdon,c.pub_date)");
        list($from, $sort) = $this->injectSortByTV($tbl_site_content.' '.$this->_filters['join'], $sort);

        $where = "WHERE {$where} c.parent IN (" . $this->sanitarIn($this->IDs) . ")";
        if(!$this->getCFGDef('showNoPublish', 0)){
            $where .= " AND c.deleted=0 AND c.published=1";
        }
        $sql = $this->dbQuery("SELECT DISTINCT c.* FROM ".$from." ".$where." ".
                (($this->getCFGDef('showParent', '0')) ? "" : "AND c.id NOT IN(" . $this->sanitarIn($this->IDs) . ") ") .
                $sort . " " .
                $this->LimitSQL($this->getCFGDef('queryLimit', 0))
        );
        $rows = $this->modx->db->makeArray($sql);
        $out = array();
        foreach ($rows as $item) {
            $out[$item['id']] = $item;
        }
        return $out;
    }

    public function changeSortType($field, $type){
        $type = trim($type);
        switch(strtoupper($type)){
            case 'TVDATETIME':{
                $field = "STR_TO_DATE(".$field.",'%d-%m-%Y %H:%i:%s')";
                break;
            }
            default:{
                $field = parent::changeSortType($field, $type);
            }
        }
        return $field;
    }
}