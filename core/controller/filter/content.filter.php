<?php
/**
 * Filters DocLister results by value of a given MODx Template Variables (TVs).
 * Supported comparison operators:
 * - "="
 * - "IN"
 * - "LIKE" also "%LIKE" or "LIKE%"
 * @author aka
 * @param filter_tv tvname:operator:value
 *
 */
class content_DL_filter extends filterDocLister{
	protected  $field;
	protected  $operator;
	protected  $value;
	
	public function get_where(){
		return $this->build_sql_where($this->main_table_alias, $this->field, $this->operator, $this->value);
	}
	
	function build_sql_where($table_alias, $field, $operator, $value){
		$output = $table_alias . '.' . $field . ' ';
		switch ($operator){
			case '=': case 'eq': $output .= ' = ' . floatval($value); break;
			case 'gt': $output .= ' > ' . $value; break;
			case 'lt': $output .= ' < ' . $value; break;
			case 'elt': $output .= ' <= ' . $value; break;
			case 'egt': $output .= ' >= ' . $value; break;
			case 'like': $output .= " LIKE '%" . $value . "%'"; break;
			case 'is': $output .= " = '" . $value . "'"; break;
			case 'containsOne' :
				$words = explode($this->DocLister->getCFGDef('filter_delimiter', ','), $value);
				foreach ($words as $word){
					$word_arr[] = $table_alias . '.' . $field . "  LIKE '%" . trim($word) . "%'";
				}
				$output = '(' . implode(' OR ', $word_arr) . ')';
				break;
			default: return '';
		}
		return $output;
	}
	
	function get_join(){
		return '';
	}
}
?>