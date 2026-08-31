<?php namespace DocLister\Tests\Unit\DL\Filters;

use DocLister\Tests\Unit\DL\DLAbstract;

class Issue342Test extends DLAbstract
{
    /**
     * @see: https://github.com/AgelxNash/DocLister/issues/342
     */
    public function testNumericComparisonValueKeepsLegacyFormat()
    {
        $out = array(
            'join'  => '',
            'where' => '`menuindex` > 10'
        );

        $method = $this->getMethod($this->DL, 'getFilters');
        $filters = $method->invoke($this->DL, 'content:menuindex:gt:10,5');

        $this->assertSame($out, $filters);
    }

    public function testTVDateComparisonValueIsNotConvertedToFloat()
    {
        $out = array(
            'join'  => 'LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1',
            'where' => "`dltv_testA_1`.`value` >= '2019-02-19'"
        );

        $method = $this->getMethod($this->DL, 'getFilters');
        $filters = $method->invoke($this->DL, 'tv:testA:egt:2019-02-19');

        $this->assertSame($out, $filters);
    }

    public function testTVDDateComparisonValueIsNotConvertedToFloat()
    {
        $out = array(
            'join'  => 'LEFT JOIN site_tmplvar_contentvalues as `dltv_testA_1` ON `dltv_testA_1`.`contentid`=`c`.`id` AND `dltv_testA_1`.`tmplvarid`=1 LEFT JOIN site_tmplvars as `d_dltv_testA_1` on `d_dltv_testA_1`.`id` = 1',
            'where' => "IFNULL(`dltv_testA_1`.`value`, `d_dltv_testA_1`.`default_text`) <= '19-02-2019 00:00:00'"
        );

        $method = $this->getMethod($this->DL, 'getFilters');
        $filters = $method->invoke($this->DL, 'tvd:testA:elt:19-02-2019 00:00:00');

        $this->assertSame($out, $filters);
    }

    public function testContentDateComparisonValueIsNotConvertedToFloat()
    {
        $out = array(
            'join'  => '',
            'where' => "`pub_date` < '2019-02-19'"
        );

        $method = $this->getMethod($this->DL, 'getFilters');
        $filters = $method->invoke($this->DL, 'content:pub_date:lt:2019-02-19');

        $this->assertSame($out, $filters);
    }
}
