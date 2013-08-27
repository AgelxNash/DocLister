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

    const TableAlias = 'c';

    public function __construct(){
        $this->setTableAlias(self::TableAlias);
    }

    public function get_where(){
		return $this->build_sql_where($this->getTableAlias(), $this->field, $this->operator, $this->value);
	}

	public function get_join(){
		return '';
	}
}
?>