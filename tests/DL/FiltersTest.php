<?php namespace DocLister\Tests\DL;

class FiltersTest extends DLAbstract {
	public function testEmptyFilterSuccess()
	{
		$out = null;

		$method = $this->getMethod($this->DL, "getFilters");
		$filters = $method->invoke($this->DL, '');

		$this->assertEquals($out, $filters);
	}
	public function testOneFilterSuccess(){
		$this->modx->_TVnames = array(
			'example' => 1
		);
		$out = array(
			'join' => "LEFT JOIN site_tmplvar_contentvalues as `dltv_example_1` ON `dltv_example_1`.`contentid`=`c`.`id` AND `dltv_example_1`.`tmplvarid`=1",
			'where' => "`dltv_example_1`.`value` != 'asd'"
		);

		$method = $this->getMethod($this->DL, "getFilters");
		$filters = $method->invoke($this->DL, 'tv:example:isnot:asd');

		$this->assertEquals($out, $filters);
	}
	public function testOneGroupFilterSuccess(){
		$this->modx->_TVnames = array(
			'example' => 1
		);
		$out = array(
			'join' => "LEFT JOIN site_tmplvar_contentvalues as `dltv_example_1` ON `dltv_example_1`.`contentid`=`c`.`id` AND `dltv_example_1`.`tmplvarid`=1",
			'where' => "(`dltv_example_1`.`value` != 'asd')"
		);

		$method = $this->getMethod($this->DL, "getFilters");
		$filters = $method->invoke($this->DL, 'AND(tv:example:isnot:asd)');

		$this->assertEquals($out, $filters);
	}
	public function testTwoFilterSuccess()
	{
		$this->modx->_TVnames = array(
			'testA' => 1,
			'testB' => 2
		);
		$out = array(
			'join' => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1 LEFT JOIN site_tmplvar_contentvalues as `dltv_testB_2` ON `dltv_testB_2`.`contentid`=`c`.`id` AND `dltv_testB_2`.`tmplvarid`=2",
			'where' => "(`dltv_testA_1`.`value` != 'asd' AND `dltv_testB_2`.`value` != 'qwe')"
		);

		$method = $this->getMethod($this->DL, "getFilters");
		$filters = $method->invoke($this->DL, 'AND(tv:testA:isnot:asd;tv:testB:isnot:qwe)');

		$this->assertEquals($out, $filters);
	}
	public function testOneLikeFilterSuccess(){
		$this->modx->_TVnames = array(
			'example' => 1
		);
		$out = array(
			'join' => "LEFT JOIN site_tmplvar_contentvalues as `dltv_example_1` ON `dltv_example_1`.`contentid`=`c`.`id` AND `dltv_example_1`.`tmplvarid`=1",
			'where' => "`dltv_example_1`.`value` LIKE '%asd=%qwe==%' ESCAPE '='"
		);

		$method = $this->getMethod($this->DL, "getFilters");
		$filters = $method->invoke($this->DL, 'tv:example:like:asd%qwe=');

		$this->assertEquals($out, $filters);
	}
	public function testORFilterFilterSuccess(){
		$this->modx->_TVnames = array(
			'color' => 1
		);
		$out = array(
			'join' => "LEFT JOIN site_tmplvar_contentvalues as `dltv_color_1` ON `dltv_color_1`.`contentid`=`c`.`id` AND `dltv_color_1`.`tmplvarid`=1",
			'where' => "(`dltv_color_1`.`value` = 'черный' OR `dltv_color_1`.`value` = 'белый' OR `dltv_color_1`.`value` = 'красный')"
		);

		$method = $this->getMethod($this->DL, "getFilters");
		$filters = $method->invoke($this->DL, 'OR(tv:color:eq:черный;tv:color:eq:белый;tv:color:eq:красный)');

		$this->assertEquals($out, $filters);
	}
	public function testContainsOneFilterFilterSuccess(){
		$this->modx->_TVnames = array(
			'color' => 1
		);
		$out = array(
			'join' => "LEFT JOIN site_tmplvar_contentvalues as `dltv_color_1` ON `dltv_color_1`.`contentid`=`c`.`id` AND `dltv_color_1`.`tmplvarid`=1",
			'where' => "(`dltv_color_1`.`value` LIKE '%белый%' ESCAPE '=' OR `dltv_color_1`.`value` LIKE '%синий%' ESCAPE '=' OR `dltv_color_1`.`value` LIKE '%красный%' ESCAPE '=')"
		);

		$method = $this->getMethod($this->DL, "getFilters");
		$filters = $method->invoke($this->DL, 'tv:color:containsOne:белый,синий,красный');

		$this->assertEquals($out, $filters);
	}
	public function testInFilterFilterSuccess(){
		$this->modx->_TVnames = array(
			'color' => 1
		);
		$out = array(
			'join' => "LEFT JOIN site_tmplvar_contentvalues as `dltv_color_1` ON `dltv_color_1`.`contentid`=`c`.`id` AND `dltv_color_1`.`tmplvarid`=1",
			'where' => "`dltv_color_1`.`value` IN('белый','синий','красный')"
		);

		$method = $this->getMethod($this->DL, "getFilters");
		$filters = $method->invoke($this->DL, 'tv:color:in:белый,синий,красный');

		$this->assertEquals($out, $filters);
	}
	public function testTVDFilterFilterSuccess(){
		$this->modx->_TVnames = array(
			'color' => 1
		);
		$out = array(
			'join' => "LEFT JOIN site_tmplvar_contentvalues as `dltv_color_1` ON `dltv_color_1`.`contentid`=`c`.`id` AND `dltv_color_1`.`tmplvarid`=1 LEFT JOIN site_tmplvars as `d_dltv_color_1` on `d_dltv_color_1`.`id` = 1",
			'where' => "IFNULL(`dltv_color_1`.`value`, `d_dltv_color_1`.`default_text`) = 'белый'"
		);

		$method = $this->getMethod($this->DL, "getFilters");
		$filters = $method->invoke($this->DL, 'tvd:color:is:белый');

		$this->assertEquals($out, $filters);
	}
	public function testContentFilterFilterSuccess(){
		$this->modx->_TVnames = array(
			'color' => 1
		);
		$out = array(
			'join' => "",
			'where' => "`hidemenu` = '1'"
		);

		$method = $this->getMethod($this->DL, "getFilters");
		$filters = $method->invoke($this->DL, 'content:hidemenu:is:1');

		$this->assertEquals($out, $filters);
	}
}