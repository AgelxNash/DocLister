<?php namespace DocLister\Tests\Unit\DL\Filters;

use DocLister\Tests\Unit\DL\DLAbstract;

class BaseTest extends DLAbstract
{
    public function testEmptyFilterSuccess()
    {
        $out = null;

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, '');

        $this->assertNull($filters);
    }

    public function testOneFilterSuccess()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testB_1` ON `dltv_testB_1`.`contentid`=`c`.`id` AND `dltv_testB_1`.`tmplvarid`=2",
            'where' => "(`dltv_testB_1`.`value` != 'asd' OR `dltv_testB_1`.`value` IS NULL)"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'tv:testB:isnot:asd');

        $this->assertEquals($out, $filters);
    }

    public function testOneGroupFilterSuccess()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testB_1` ON `dltv_testB_1`.`contentid`=`c`.`id` AND `dltv_testB_1`.`tmplvarid`=2",
            'where' => "((`dltv_testB_1`.`value` != 'asd' OR `dltv_testB_1`.`value` IS NULL))"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'AND(tv:testB:isnot:asd)');

        $this->assertSame($out, $filters);
    }

    public function testTwoFilterSuccess()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_2` ON `dltv_testA_2`.`contentid`=`c`.`id` AND `dltv_testA_2`.`tmplvarid`=1 LEFT JOIN site_tmplvar_contentvalues as `dltv_testB_1` ON `dltv_testB_1`.`contentid`=`c`.`id` AND `dltv_testB_1`.`tmplvarid`=2",
            'where' => "((`dltv_testA_2`.`value` != 'asd' OR `dltv_testA_2`.`value` IS NULL) AND (`dltv_testB_1`.`value` != 'qwe' OR `dltv_testB_1`.`value` IS NULL))"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'AND(tv:testA:isnot:asd;tv:testB:isnot:qwe)');

        $this->assertSame($out, $filters);
    }

    public function testOneLikeFilterSuccess()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testB_1` ON `dltv_testB_1`.`contentid`=`c`.`id` AND `dltv_testB_1`.`tmplvarid`=2",
            'where' => "`dltv_testB_1`.`value` LIKE '%asd=%qwe==%' ESCAPE '='"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'tv:testB:like:asd%qwe=');

        $this->assertSame($out, $filters);
    }

    public function testORFilterFilterSuccess()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1",
            'where' => "(`dltv_testA_1`.`value` = 'черный' OR `dltv_testA_1`.`value` = 'белый' OR `dltv_testA_1`.`value` = 'красный')"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'OR(tv:testA:eq:черный;tv:testA:eq:белый;tv:testA:eq:красный)');

        $this->assertSame($out, $filters);
    }

    public function testContainsOneEmptyFilter()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1",
            'where' => ""
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'tv:testA:containsOne:');

        $this->assertSame($out, $filters);
    }

    public function testContainsOneFilterFilterSuccess()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1",
            'where' => "(`dltv_testA_1`.`value` LIKE '%белый%' ESCAPE '=' OR `dltv_testA_1`.`value` LIKE '%синий%' ESCAPE '=' OR `dltv_testA_1`.`value` LIKE '%красный%' ESCAPE '=')"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'tv:testA:containsOne:белый,синий,красный');

        $this->assertSame($out, $filters);
    }

    public function testInFilterFilterSuccess()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1",
            'where' => "`dltv_testA_1`.`value` IN('белый','синий','красный')"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'tv:testA:in:белый,синий,красный');

        $this->assertSame($out, $filters);
    }

    public function testTVDFilterFilterSuccess()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1 LEFT JOIN site_tmplvars as `d_dltv_testA_1` on `d_dltv_testA_1`.`id` = 1",
            'where' => "IFNULL(`dltv_testA_1`.`value`, `d_dltv_testA_1`.`default_text`) = 'белый'"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'tvd:testA:is:белый');

        $this->assertSame($out, $filters);
    }

    public function testContentFilterFilterSuccess()
    {
        $out = array(
            'join'  => "",
            'where' => "`hidemenu` = '1'"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'content:hidemenu:is:1');

        $this->assertSame($out, $filters);
    }
}
