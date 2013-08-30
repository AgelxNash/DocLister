<?php
require_once 'content.filter.php';
/**
 * Filters DocLister results by value of a specified field in the xrecipe_nutrition_products custom table.
 * This is a good example, of how a simple filter over a custom table can be created.
 * @author kabachello
 *
 */
class nutrifacts_DL_filter extends content_DL_filter{
	
	function get_where(){
		return $this->build_sql_where('xnp', $this->field, $this->operator, $this->value);
	}
	
	function get_join(){
		return 'LEFT JOIN xrecipe_nutrition_products xnp ON ' . $this->main_table_alias . '.id = xnp.document_id';
	}
}
?>