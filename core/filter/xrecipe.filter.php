<?php
/**
 * Filters DocLister results using a custom filter implementation of the xrecipe plugin
 * @author kabachello
 *
 */
class xrecipe_DL_filter extends filterDocLister{
	
	function parseFilter($filter) {
		global $xrecipe;
		if (!$this->filters = $xrecipe->searcher->get_filters()) return false;
		return true;
	}
	
	function get_where(){
		global $xrecipe;
		if (!$this->filters) return '';
		foreach ($xrecipe->searcher->get_filters() as $join => $fields){
			foreach ($fields as $field => $values){
				foreach ($values as $op => $values){
					foreach ($values as $val){
						$where = $join . '.' . $field;
						switch ($op){
							case '=': case 'eq': $where .= ' = ' . $val; break;
							case 'gt': $where .= ' > ' . $val; break;
							case 'lt': $where .= ' < ' . $val; break;
							case 'elt': $where .= ' <= ' . $val; break;
							case 'egt': $where .= ' >= ' . $val; break;
							case 'like': $where .= " LIKE '%" . $val . "%'"; break;
							case 'notlike': $where .= " NOT LIKE '%" . $val . "%'"; break;
							case 'is': $where .= " = '" . $val . "'"; break;
							default: continue;
						}
						$wheres[] = $where;
					}
				}
			}
		}
		return is_array($wheres) ? implode(' AND ', $wheres) : '';
	}
	
	function get_join(){
		global $xrecipe;
		if (!$this->filters) return '';
		foreach ($xrecipe->searcher->get_filters() as $join => $fields){
			$joins[] = 'LEFT JOIN xrecipe_' . $join . ' ' . $join . ' ON ' . $this->main_table_alias . '.id = ' . $join . '.document_id';
		}
		
		if (!empty($joins)){
			return implode(' ', $joins);
		}
		
		return '';
	}
}
?>