<?php namespace DocLister\Tests\Unit\DL\Filters;

use DocLister\Tests\Unit\DL\DLAbstract;

class Issue276Test extends DLAbstract
{
    /**
     * @see: https://github.com/AgelxNash/DocLister/issues/276
     * @see: https://github.com/AgelxNash/DocLister/issues/322
     */
    public function testA()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1 LEFT JOIN site_tmplvars as `d_dltv_testA_1` on `d_dltv_testA_1`.`id` = 1",
            'where' => "IFNULL(`dltv_testA_1`.`value`, `d_dltv_testA_1`.`default_text`) = 'x(Y)z'"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'tvd:testA:is:x(Y)z');

        $this->assertSame($out, $filters);
    }


    public function testB()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1 LEFT JOIN site_tmplvars as `d_dltv_testA_1` on `d_dltv_testA_1`.`id` = 1",
            'where' => "(IFNULL(`dltv_testA_1`.`value`, `d_dltv_testA_1`.`default_text`) = 'x(Y)z')"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'AND(tvd:testA:is:x(Y)z)');

        $this->assertSame($out, $filters);
    }

    public function testC()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1 LEFT JOIN site_tmplvars as `d_dltv_testA_1` on `d_dltv_testA_1`.`id` = 1",
            'where' => "(IFNULL(`dltv_testA_1`.`value`, `d_dltv_testA_1`.`default_text`) = 'x(Y))z')"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'AND(tvd:testA:is:x(Y))z)');

        $this->assertSame($out, $filters);
    }

    public function testD()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_2` ON `dltv_testA_2`.`contentid`=`c`.`id` AND `dltv_testA_2`.`tmplvarid`=1 LEFT JOIN site_tmplvars as `d_dltv_testA_2` on `d_dltv_testA_2`.`id` = 1 LEFT JOIN site_tmplvar_contentvalues as `dltv_testB_1` ON `dltv_testB_1`.`contentid`=`c`.`id` AND `dltv_testB_1`.`tmplvarid`=2",
            'where' => "(IFNULL(`dltv_testA_2`.`value`, `d_dltv_testA_2`.`default_text`) = 'x(Y)z' AND `dltv_testB_1`.`value` = 'q(W)e))')"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'AND(tvd:testA:is:x(Y)z;tv:testB:is:q(W)e)))');

        $this->assertSame($out, $filters);
    }

    public function testE()
    {
        $out = array(
            'join'  => "LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1",
            'where' => "(`dltv_testA_1`.`value` = 'x(Y)z)' OR `dltv_testA_1`.`value` = 'a(B)c)')"
        );

        $method = $this->getMethod($this->DL, "getFilters");
        $filters = $method->invoke($this->DL, 'OR(tv:testA:is:x(Y)z);tv:testA:is:a(B)c))');

        $this->assertSame($out, $filters);
    }
}
