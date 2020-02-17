<?php namespace DocLister\Tests\Unit\DL\Extender\TV;

use DocLister\Tests\Unit\DL\Extender\TVAbstract;

class injectSortByTVTest extends TVAbstract
{
    public function testNoTableAndExistTVPrice()
    {
        $out = array(
            'site_content as c LEFT JOIN site_tmplvar_contentvalues as `dltv_price_1` on `dltv_price_1`.`contentid`=`c`.`id` AND `dltv_price_1`.`tmplvarid`=1',
            'ORDER BY `dltv_price_1`.`value` ASC'
        );

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($this->mockDocLister('site_content', array(
            'debug' => 1,
        )));

        $TVs = $extTV->injectSortByTV("site_content as c", "ORDER BY price ASC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }

    public function testNoTableAndExistTVPriceAndImage()
    {
        $out = array(
            'site_content as c LEFT JOIN site_tmplvar_contentvalues as `dltv_price_1` on `dltv_price_1`.`contentid`=`c`.`id` AND `dltv_price_1`.`tmplvarid`=1 LEFT JOIN site_tmplvar_contentvalues as `dltv_image_3` on `dltv_image_3`.`contentid`=`c`.`id` AND `dltv_image_3`.`tmplvarid`=2',
            'ORDER BY `dltv_price_1`.`value` ASC,id DESC,`dltv_image_3`.`value` ASC'
        );

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($this->mockDocLister('site_content', array(
            'debug' => 1,
        )));

        $TVs = $extTV->injectSortByTV("site_content as c", "ORDER BY price ASC, id DESC, image ASC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }

    public function testNoTableAndNoExistTV()
    {
        $out = array(
            'site_content as c',
            'ORDER BY example ASC'
        );

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($this->mockDocLister('site_content', array(
            'debug' => 1,
        )));

        $TVs = $extTV->injectSortByTV("site_content as c", "ORDER BY example ASC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }

    public function testChangeTypeInNoExistsTv()
    {
        $out = array(
            "site_content as c",
            "ORDER BY example ASC"
        );

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($this->mockDocLister('site_content', array(
            'debug'      => 1,
            'tvSortType' => 'BINARY'
        )));


        $TVs = $extTV->injectSortByTV('site_content as c', "ORDER BY example ASC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }

    public function testTableExistsAndExistTVOther()
    {
        $out = array(
            'site_content as c LEFT JOIN site_tmplvar_contentvalues as `dltv_other_1` on `dltv_other_1`.`contentid`=`c`.`id` AND `dltv_other_1`.`tmplvarid`=3',
            'ORDER BY `dltv_other_1`.`value` ASC'
        );

        /** @var \site_contentDocLister $DL */
        $DL = $this->mockDocLister('site_content', array(
            'debug' => 1,
        ));
        $DL->TableAlias('other', 'site_tmplvar_contentvalues', 'dltv_other_1');

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($DL);


        $TVs = $extTV->injectSortByTV($out[0], "ORDER BY other ASC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }

    public function testNoTableAndExistDoubleTVOtherWithChangeTypeSkipFirst()
    {
        $out = array(
            'site_content as c LEFT JOIN site_tmplvar_contentvalues as `dltv_other_1` on `dltv_other_1`.`contentid`=`c`.`id` AND `dltv_other_1`.`tmplvarid`=3',
            'ORDER BY `dltv_other_1`.`value` ASC,CAST(`dltv_other_1`.`value` as DECIMAL(10,2)) DESC'
        );

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($this->mockDocLister('site_content', array(
            'debug'      => 1,
            'tvSortType' => ',DECIMAL',
            //'tvSortWithDefault' => ','
        )));


        $TVs = $extTV->injectSortByTV('site_content as c', "ORDER BY other ASC, other DESC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }

    public function testNoTableAndExistTVOtherWithFirstChangeTypeAndDefaultValue()
    {
        $out = array(
            'site_content as c LEFT JOIN site_tmplvar_contentvalues as `dltv_other_1` on `dltv_other_1`.`contentid`=`c`.`id` AND `dltv_other_1`.`tmplvarid`=3 LEFT JOIN site_tmplvars as `d_dltv_other_1` on `d_dltv_other_1`.`id` = 3 LEFT JOIN site_tmplvar_contentvalues as `dltv_price_2` on `dltv_price_2`.`contentid`=`c`.`id` AND `dltv_price_2`.`tmplvarid`=1',
            'ORDER BY CAST(IFNULL(`dltv_other_1`.`value`, `d_dltv_other_1`.`default_text`) as UNSIGNED) ASC,`dltv_price_2`.`value` DESC'
        );

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($this->mockDocLister('site_content', array(
            'debug'             => 1,
            'tvSortType'        => 'UNSIGNED',
            'tvSortWithDefault' => 'other'
        )));


        $TVs = $extTV->injectSortByTV('site_content as c', "ORDER BY other ASC, price DESC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }

    public function testTvOtherTypeTVDATETIME()
    {
        $out = array(
            "site_content as c LEFT JOIN site_tmplvar_contentvalues as `dltv_other_1` on `dltv_other_1`.`contentid`=`c`.`id` AND `dltv_other_1`.`tmplvarid`=3 LEFT JOIN site_tmplvars as `d_dltv_other_1` on `d_dltv_other_1`.`id` = 3",
            "ORDER BY STR_TO_DATE(IFNULL(`dltv_other_1`.`value`, `d_dltv_other_1`.`default_text`),'%d-%m-%Y %H:%i:%s') ASC"
        );

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($this->mockDocLister('site_content', array(
            'debug'             => 1,
            'tvSortType'        => 'TVDATETIME',
            'tvSortWithDefault' => 'other'
        )));


        $TVs = $extTV->injectSortByTV('site_content as c', "ORDER BY other ASC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }

    public function testTvOtherTypeDATETIME()
    {
        $out = array(
            "site_content as c LEFT JOIN site_tmplvar_contentvalues as `dltv_other_1` on `dltv_other_1`.`contentid`=`c`.`id` AND `dltv_other_1`.`tmplvarid`=3 LEFT JOIN site_tmplvars as `d_dltv_other_1` on `d_dltv_other_1`.`id` = 3",
            "ORDER BY CAST(IFNULL(`dltv_other_1`.`value`, `d_dltv_other_1`.`default_text`) as DATETIME) ASC"
        );

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($this->mockDocLister('site_content', array(
            'debug'             => 1,
            'tvSortType'        => 'DATETIME',
            'tvSortWithDefault' => 'other'
        )));


        $TVs = $extTV->injectSortByTV('site_content as c', "ORDER BY other ASC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }

    public function testTvOtherTypeBINARY()
    {
        $out = array(
            "site_content as c LEFT JOIN site_tmplvar_contentvalues as `dltv_other_1` on `dltv_other_1`.`contentid`=`c`.`id` AND `dltv_other_1`.`tmplvarid`=3 LEFT JOIN site_tmplvars as `d_dltv_other_1` on `d_dltv_other_1`.`id` = 3",
            "ORDER BY CAST(IFNULL(`dltv_other_1`.`value`, `d_dltv_other_1`.`default_text`) as BINARY) ASC"
        );

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($this->mockDocLister('site_content', array(
            'debug'             => 1,
            'tvSortType'        => 'BINARY',
            'tvSortWithDefault' => 'other'
        )));


        $TVs = $extTV->injectSortByTV('site_content as c', "ORDER BY other ASC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }

    public function testTvOtherErrorType()
    {
        $out = array(
            "site_content as c LEFT JOIN site_tmplvar_contentvalues as `dltv_other_1` on `dltv_other_1`.`contentid`=`c`.`id` AND `dltv_other_1`.`tmplvarid`=3 LEFT JOIN site_tmplvars as `d_dltv_other_1` on `d_dltv_other_1`.`id` = 3",
            "ORDER BY IFNULL(`dltv_other_1`.`value`, `d_dltv_other_1`.`default_text`) ASC"
        );

        /** @var \tv_DL_Extender $extTV */
        $extTV = $this->getTVExtender($this->mockDocLister('site_content', array(
            'debug'             => 1,
            'tvSortType'        => 'EXAMPLE_ERROR_TYPE',
            'tvSortWithDefault' => 'other'
        )));


        $TVs = $extTV->injectSortByTV('site_content as c', "ORDER BY other ASC");

        $this->assertSame($out, $this->fixDebugSQL($TVs));
    }
}
