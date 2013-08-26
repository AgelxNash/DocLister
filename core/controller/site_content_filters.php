<?php
/**
 * site_content_filters controller for DocLister
 * 
 * @category controller
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author kabachello <kabachnik@hotmail.com>
 * @date 16.08.2013
 * @version 1.1.0
 * 
 * Adds flexible filters to DocLister. Filter types can be easily added using filter extenders (see filter subfolder).
 * To use filtering via snippet call add the "filters" parameter to the DocLister call like " ... &filters=`tv:tags:like:your_tag`
 * All filters adhere to the following syntax:
 * <logic_operator>(<filter_type>:<field>:<comparator>:<value>, <filter_type>:<field>:<comparator>:<value>, ...)
 * <logic_operator> - AND, OR, etc. - applied to a comma separated list of filters enclosed in parenthesis
 * <filter_type> - name of the filter extender to use (tv, content, etc.)
 * <field> - the field to filter (must be supported by the respecitve filter_type)
 * <comparator> - comparison operator (must be supported by the respecitve filter_type) - is, gt, lt, like, etc.
 * <value> - value to compare with
 * 
 * Examples:
 * AND(content:template:eq:5; tv:tags:like:my tag) - fetch all documents with template id 5 and the words "my tag" in the TV named "tags"
 *
 */

include_once(dirname(__FILE__)."/site_content.php");

class site_content_filtersDocLister extends site_contentDocLister{
	private $_filters;
	private $_logic_ops = array('AND'=>' AND ', 'OR' => ' OR '); // logic operators currently supported

    function __construct($modx, $cfg = array()){
        parent::__construct($modx,$cfg);
        $this->_filters = $this->getFilters($this->getCFGDef('filters', ''));
    }

    // @abstract
     public function getChildrenCount(){
		$where = $this->getCFGDef('addWhereList', '');
        $where = ($where && $this->_filters['where'] ? $where . ' AND ' : '') . $this->_filters['where'];

        if ($where != '') {
            $where .= " AND ";
        }
        $tbl_site_content = $this->getTable('site_content','c');
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        $getCFGDef = $this->getCFGDef('showParent', '0') ? '' : "AND c.id NOT IN({$sanitarInIDs})";
        $fields = 'count(c.`id`) as `count`';
        $from   = $tbl_site_content ." ". $this->_filters['join'];
        $where = "{$where} c.parent IN ({$sanitarInIDs}) AND c.deleted=0 AND c.published=1 {$getCFGDef}";
        $rs=$this->modx->db->select($fields, $from, $where);
        return $this->modx->db->getValue($rs);
	}

    protected function getDocList(){
		/*
		* @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
		* @TODO: 5) Добавить фильтрацию по основным параметрам документа
		*/
		// add the parameter addWhereList
		$where = $this->getCFGDef('addWhereList','');
		// add the filters
		$where =($where ? $where . ' AND ' : '') . $this->_filters['where'];

		if($where!=''){
			$where.=" AND ";
		}

        $tbl_site_content = $this->getTable('site_content','c');

        $where = "WHERE {$where} c.deleted=0 AND c.published=1";
        $sanitarInIDs = $this->sanitarIn($this->IDs);
        if ($sanitarInIDs != "''") {
            $where .= " AND c.id IN ({$sanitarInIDs})";
        }

        $limit   = $this->LimitSQL($this->getCFGDef('queryLimit',0));
        $sort = $this->SortOrderSQL("if(c.pub_date=0,c.createdon,c.pub_date)");
        $select = "c.*";
        if (preg_match("/^ORDER BY (.*) /", $sort, $match)) {
            $TVnames = $this->extender['tv']->getTVnames();
            if (isset($TVnames[$match[1]])) {
                $tbl_site_content .= " LEFT JOIN " . $this->getTable("site_tmplvar_contentvalues") . " as tv
                    on tv.contentid=c.id AND tv.tmplvarid=" . $TVnames[$match[1]];
                $sort = str_replace("ORDER BY " . $match[1], "ORDER BY tv.value", $sort);
            }
        }

		$rs=$this->modx->db->query("SELECT * FROM {$tbl_site_content} {$this->_filters['join']} {$where} GROUP BY c.id {$sort} {$limit}");

		$rows=$this->modx->db->makeArray($rs);
		$out=array();
		foreach($rows as $item){
			$out[$item['id']]=$item;
		}
		return $out;
	}

	/*
	* @TODO: 3) Формирование ленты в случайном порядке (если отключена пагинация и есть соответствующий запрос)
	* @TODO: 5) Добавить фильтрацию по основным параметрам документа
	*/
	protected  function getChildrenList(){
		// add the parameter addWhereList
		$where = $this->getCFGDef('addWhereList','');
        // add the filters
		$where =($where ? $where . ' AND ' : '') . $this->_filters['where'];

		if($where!=''){
			$where.=" AND ";
		}
		
		$sql=$this->modx->db->query("
			SELECT DISTINCT c.* FROM ".$this->getTable('site_content','c') . " ". $this->_filters['join'] . "
			WHERE ".$where."
				c.parent IN (".$this->sanitarIn($this->IDs).") 
				AND c.deleted=0 
				AND c.published=1 ".
				(($this->getCFGDef('showParent','0')) ? "" : "AND c.id NOT IN(".$this->sanitarIn($this->IDs).") ").
			$this->SortOrderSQL('if(pub_date=0,createdon,pub_date)')." ".
			$this->LimitSQL($this->getCFGDef('queryLimit',0))
		);
		$rows=$this->modx->db->makeArray($sql);
		$out=array();
		foreach($rows as $item){
			$out[$item['id']]=$item;
		}
		return $out;
	}
	
	/**
	 * OR(AND(filter:field:operator:value;filter2:field:oerpator:value);(...)), etc.
	 * @param string $filter_string
	 */
	protected function getFilters($filter_string){
        // the filter parameter tells us, which filters can be used in this query
		$filter_string = trim($filter_string);
		if (!$filter_string) return;

		$logic_op_found = false;
		foreach ($this->_logic_ops as $op => $sql){
			if (strpos($filter_string, $op) === 0){
				$logic_op_found = true;
				$subfilters = substr($filter_string, strlen($op)+1, -1);
				$subfilters = explode(';', $subfilters);
				foreach ($subfilters as $subfilter){
					$subfilter = $this->getFilters(trim($subfilter));
					if (!$subfilter) continue;
					if ($subfilter['join']) $joins[] = $subfilter['join'];
					if ($subfilter['where']) $wheres[] = $subfilter['where'];
				}
				$output['join'] = !empty($joins) ? implode(' ', $joins) : '';
				$output['where'] = !empty($wheres) ? '(' . implode($sql, $wheres) . ')' : '';
			}
		}
		
		if (!$logic_op_found) {
			$filter = $this->loadFilter($filter_string);
			if (!$filter) {
                $this->modx->logEvent(0, 2, 'Error while loading DocLister filter "' . $filter_string . '": check syntax!');
				return;
			}
			$output['join'] = $filter->get_join();
			$output['where'] = $filter->get_where();
		}
		
		return $output;
	}
	
	protected function loadFilter($filter){
		$fltr_params = explode(':', $filter);
		$fltr = $fltr_params[0];
		// check if the filter is implemented
		if (file_exists(dirname(__FILE__) . '/filter/' . $fltr . '.filter.php')){
			require_once dirname(__FILE__) . '/filter/' . $fltr . '.filter.php';
			$fltr_class = $fltr . '_DL_filter';
			$fltr_obj = new $fltr_class();
			$fltr_obj->init($this, $filter);
			return $fltr_obj;
		} else {
			return false;
		}
	}
}

include_once(dirname(dirname(__FILE__))."/filterDocLister.abstract.php");