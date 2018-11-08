<?php namespace DocLister\Tests\Unit\DL\Filters;

use DocLister\Tests\Unit\DL\DLAbstract;

class Issue333Test extends DLAbstract
{
    /**
     * @see: https://github.com/AgelxNash/DocLister/issues/333
     */
    public function testA()
    {
        $method = $this->getMethod($this->DL, 'getFilters');

        $this->assertFalse($method->invoke($this->DL, 'testA:is:no'));
    }


    public function testB()
    {
        $out = array(
            'join'  => 'LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1',
            'where' => "(`dltv_testA_1`.`value` = 'no ')"
        );

        $method = $this->getMethod($this->DL, 'getFilters');
        $filters = $method->invoke($this->DL, 'AND( tv:testA:is:no )');

        $this->assertSame($out, $filters);
    }

    public function testC()
    {
        $out = array(
            'join'  => 'LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1',
            'where' => "(`dltv_testA_1`.`value` = 'no')"
        );

        $method = $this->getMethod($this->DL, 'getFilters');
        $filters = $method->invoke($this->DL, 'AND( tv:testA:is:no; )');

        $this->assertSame($out, $filters);
    }

    public function testD()
    {
        $out = array(
            'join'  => 'LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_2` ON `dltv_testA_2`.`contentid`=`c`.`id` AND `dltv_testA_2`.`tmplvarid`=1 LEFT JOIN site_tmplvars as `d_dltv_testA_2` on `d_dltv_testA_2`.`id` = 1 LEFT JOIN site_tmplvar_contentvalues as `dltv_testB_1` ON `dltv_testB_1`.`contentid`=`c`.`id` AND `dltv_testB_1`.`tmplvarid`=2',
            'where' => "(IFNULL(`dltv_testA_2`.`value`, `d_dltv_testA_2`.`default_text`) = 'no ' AND `dltv_testB_1`.`value` = 'yes')"
        );

        $method = $this->getMethod($this->DL, 'getFilters');
        $filters = $method->invoke($this->DL, 'AND( tvd:testA:is:no ; tv:testB:is:yes; )');

        $this->assertSame($out, $filters);
    }
}
