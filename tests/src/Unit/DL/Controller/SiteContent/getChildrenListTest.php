<?php namespace DocLister\Tests\Unit\DL\Controller\SiteContent;

use DocLister\Tests\Unit\DL\DLAbstract;

class getChildrenListTest extends DLAbstract
{
    public function testShowParent()
    {
        $out = array(
            "SELECT count(*) FROM (SELECT count(*) FROM site_content as `c`  WHERE c.deleted=0 AND c.published=1 AND (c.parent IN ('5') OR c.id IN('5')) GROUP BY `c`.`id` ) as `tmp`",
            "SELECT c.* FROM site_content as `c`  WHERE (c.parent IN ('5') OR c.id IN('5')) AND c.deleted=0 AND c.published=1 GROUP BY `c`.`id` ORDER BY if(c.pub_date=0,c.createdon,c.pub_date) DESC LIMIT 0,10"
        );
        $DL = $this->mockDocLister('site_content', array(
            'debug'      => 1,
            'idType'     => 'parents',
            'parents'    => 5,
            'showParent' => 1,
            'paginate'   => 'pages',
            'display'    => 10,
        ));

        $DL->debug->clearLog();
        $DL->getChildrenCount();
        $this->getMethod($DL, "getChildrenList")->invoke($DL);

        $debug = new \Helpers\Collection($DL->debug->getLog());

        $getChildrenCount = $debug->get(0);
        $getChildrenList = $debug->get(1);

        $this->assertEquals($out[0], trim($getChildrenCount['msg']));
        $this->assertEquals($out[1], trim($getChildrenList['msg']));

    }

    public function testHideParent()
    {
        $out = array(
            "SELECT count(*) FROM (SELECT count(*) FROM site_content as `c`  WHERE c.deleted=0 AND c.published=1 AND c.parent IN ('5') AND c.id NOT IN('5') GROUP BY `c`.`id` ) as `tmp`",
            "SELECT c.* FROM site_content as `c`  WHERE c.parent IN ('5') AND c.id NOT IN('5') AND c.deleted=0 AND c.published=1 GROUP BY `c`.`id` ORDER BY if(c.pub_date=0,c.createdon,c.pub_date) DESC LIMIT 0,10"
        );

        $DL = $this->mockDocLister('site_content', array(
            'debug'      => 1,
            'idType'     => 'parents',
            'parents'    => 5,
            'showParent' => 0,
            'paginate'   => 'pages',
            'display'    => 10,
        ));

        $DL->debug->clearLog();
        $DL->getChildrenCount();
        $this->getMethod($DL, "getChildrenList")->invoke($DL);

        $debug = new \Helpers\Collection($DL->debug->getLog());

        $getChildrenCount = $debug->get(0);
        $getChildrenList = $debug->get(1);

        $this->assertEquals($out[0], trim($getChildrenCount['msg']));
        $this->assertEquals($out[1], trim($getChildrenList['msg']));
    }

    public function testIgnoreParent()
    {
        $out = array(
            "SELECT count(*) FROM (SELECT count(*) FROM site_content as `c`  WHERE c.deleted=0 AND c.published=1 AND c.parent IN ('5') GROUP BY `c`.`id` ) as `tmp`",
            "SELECT c.* FROM site_content as `c`  WHERE c.parent IN ('5') AND c.deleted=0 AND c.published=1 GROUP BY `c`.`id` ORDER BY if(c.pub_date=0,c.createdon,c.pub_date) DESC LIMIT 0,10"
        );
        $DL = $this->mockDocLister('site_content', array(
            'debug'      => 1,
            'idType'     => 'parents',
            'parents'    => 5,
            'showParent' => "-1",
            'paginate'   => 'pages',
            'display'    => 10,
        ));

        $DL->debug->clearLog();
        $DL->getChildrenCount();
        $this->getMethod($DL, "getChildrenList")->invoke($DL);

        $debug = new \Helpers\Collection($DL->debug->getLog());

        $getChildrenCount = $debug->get(0);
        $getChildrenList = $debug->get(1);

        $this->assertEquals($out[0], trim($getChildrenCount['msg']));
        $this->assertEquals($out[1], trim($getChildrenList['msg']));
    }

    public function testShowParentAndDocs()
    {
        $out = array(
            "SELECT count(*) FROM (SELECT count(*) FROM site_content as `c`  WHERE c.deleted=0 AND c.published=1 AND (((c.parent IN ('5') OR c.id IN('5'))) OR c.id IN('10','12')) GROUP BY `c`.`id` ) as `tmp`",
            "SELECT c.* FROM site_content as `c`  WHERE (((c.parent IN ('5') OR c.id IN('5'))) OR c.id IN('10','12')) AND c.deleted=0 AND c.published=1 GROUP BY `c`.`id` ORDER BY if(c.pub_date=0,c.createdon,c.pub_date) DESC LIMIT 0,10"
        );
        $DL = $this->mockDocLister('site_content', array(
            'debug'      => 1,
            'idType'     => 'parents',
            'parents'    => 5,
            'documents'  => '10,12',
            'showParent' => 1,
            'paginate'   => 'pages',
            'display'    => 10,
        ));

        $DL->debug->clearLog();
        $DL->getChildrenCount();
        $this->getMethod($DL, "getChildrenList")->invoke($DL);

        $debug = new \Helpers\Collection($DL->debug->getLog());

        $getChildrenCount = $debug->get(0);
        $getChildrenList = $debug->get(1);

        $this->assertEquals($out[0], trim($getChildrenCount['msg']));
        $this->assertEquals($out[1], trim($getChildrenList['msg']));
    }

    public function testHideParentAndDocs()
    {
        $out = array(
            "SELECT count(*) FROM (SELECT count(*) FROM site_content as `c`  WHERE c.deleted=0 AND c.published=1 AND ((c.parent IN ('5') AND c.id NOT IN('5')) OR c.id IN('10','12')) GROUP BY `c`.`id` ) as `tmp`",
            "SELECT c.* FROM site_content as `c`  WHERE ((c.parent IN ('5') AND c.id NOT IN('5')) OR c.id IN('10','12')) AND c.deleted=0 AND c.published=1 GROUP BY `c`.`id` ORDER BY if(c.pub_date=0,c.createdon,c.pub_date) DESC LIMIT 0,10"
        );

        $DL = $this->mockDocLister('site_content', array(
            'debug'      => 1,
            'idType'     => 'parents',
            'parents'    => 5,
            'documents'  => '10,12',
            'showParent' => 0,
            'paginate'   => 'pages',
            'display'    => 10,
        ));

        $DL->debug->clearLog();
        $DL->getChildrenCount();
        $this->getMethod($DL, "getChildrenList")->invoke($DL);

        $debug = new \Helpers\Collection($DL->debug->getLog());

        $getChildrenCount = $debug->get(0);
        $getChildrenList = $debug->get(1);

        $this->assertEquals($out[0], trim($getChildrenCount['msg']));
        $this->assertEquals($out[1], trim($getChildrenList['msg']));
    }

    public function testIgnoreParentAndDocs()
    {
        $out = array(
            "SELECT count(*) FROM (SELECT count(*) FROM site_content as `c`  WHERE c.deleted=0 AND c.published=1 AND ((c.parent IN ('5')) OR c.id IN('10','12')) GROUP BY `c`.`id` ) as `tmp`",
            "SELECT c.* FROM site_content as `c`  WHERE ((c.parent IN ('5')) OR c.id IN('10','12')) AND c.deleted=0 AND c.published=1 GROUP BY `c`.`id` ORDER BY if(c.pub_date=0,c.createdon,c.pub_date) DESC LIMIT 0,10"
        );
        $DL = $this->mockDocLister('site_content', array(
            'debug'      => 1,
            'idType'     => 'parents',
            'parents'    => 5,
            'documents'  => '10,12',
            'showParent' => "-1",
            'paginate'   => 'pages',
            'display'    => 10,
        ));

        $DL->debug->clearLog();
        $DL->getChildrenCount();
        $this->getMethod($DL, "getChildrenList")->invoke($DL);

        $debug = new \Helpers\Collection($DL->debug->getLog());

        $getChildrenCount = $debug->get(0);
        $getChildrenList = $debug->get(1);

        $this->assertEquals($out[0], trim($getChildrenCount['msg']));
        $this->assertEquals($out[1], trim($getChildrenList['msg']));
    }

    public function testShowParentAndDocsAndWhere()
    {
        $out = array(
            "SELECT count(*) FROM (SELECT count(*) FROM site_content as `c`  WHERE `price`>0 AND c.deleted=0 AND c.published=1 AND (((c.parent IN ('5') OR c.id IN('5'))) OR c.id IN('10','12')) GROUP BY `c`.`id` ) as `tmp`",
            "SELECT c.* FROM site_content as `c`  WHERE `price`>0 AND (((c.parent IN ('5') OR c.id IN('5'))) OR c.id IN('10','12')) AND c.deleted=0 AND c.published=1 GROUP BY `c`.`id` ORDER BY if(c.pub_date=0,c.createdon,c.pub_date) DESC LIMIT 0,10"
        );

        $DL = $this->mockDocLister('site_content', array(
            'debug'        => 1,
            'idType'       => 'parents',
            'parents'      => 5,
            'documents'    => '10,12',
            'showParent'   => 1,
            'addWhereList' => '`price`>0',
            'paginate'     => 'pages',
            'display'      => 10,
        ));

        $DL->debug->clearLog();
        $DL->getChildrenCount();
        $this->getMethod($DL, "getChildrenList")->invoke($DL);

        $debug = new \Helpers\Collection($DL->debug->getLog());

        $getChildrenCount = $debug->get(0);
        $getChildrenList = $debug->get(1);

        $this->assertEquals($out[0], trim($getChildrenCount['msg']));
        $this->assertEquals($out[1], trim($getChildrenList['msg']));
    }

    public function testHideParentAndDocsAndWhere()
    {
        $out = array(
            "SELECT count(*) FROM (SELECT count(*) FROM site_content as `c`  WHERE `price`>0 AND c.deleted=0 AND c.published=1 AND ((c.parent IN ('5') AND c.id NOT IN('5')) OR c.id IN('10','12')) GROUP BY `c`.`id` ) as `tmp`",
            "SELECT c.* FROM site_content as `c`  WHERE `price`>0 AND ((c.parent IN ('5') AND c.id NOT IN('5')) OR c.id IN('10','12')) AND c.deleted=0 AND c.published=1 GROUP BY `c`.`id` ORDER BY if(c.pub_date=0,c.createdon,c.pub_date) DESC LIMIT 0,10"
        );

        $DL = $this->mockDocLister('site_content', array(
            'debug'        => 1,
            'idType'       => 'parents',
            'parents'      => 5,
            'documents'    => '10,12',
            'showParent'   => 0,
            'addWhereList' => '`price`>0',
            'paginate'     => 'pages',
            'display'      => 10,
        ));

        $DL->debug->clearLog();
        $DL->getChildrenCount();
        $this->getMethod($DL, "getChildrenList")->invoke($DL);

        $debug = new \Helpers\Collection($DL->debug->getLog());

        $getChildrenCount = $debug->get(0);
        $getChildrenList = $debug->get(1);

        $this->assertEquals($out[0], trim($getChildrenCount['msg']));
        $this->assertEquals($out[1], trim($getChildrenList['msg']));
    }

    public function testIgnoreParentAndDocsAndWhere()
    {
        $out = array(
            "SELECT count(*) FROM (SELECT count(*) FROM site_content as `c`  WHERE `price`>0 AND c.deleted=0 AND c.published=1 AND ((c.parent IN ('5')) OR c.id IN('10','12')) GROUP BY `c`.`id` ) as `tmp`",
            "SELECT c.* FROM site_content as `c`  WHERE `price`>0 AND ((c.parent IN ('5')) OR c.id IN('10','12')) AND c.deleted=0 AND c.published=1 GROUP BY `c`.`id` ORDER BY if(c.pub_date=0,c.createdon,c.pub_date) DESC LIMIT 0,10"
        );

        $DL = $this->mockDocLister('site_content', array(
            'debug'        => 1,
            'idType'       => 'parents',
            'parents'      => 5,
            'documents'    => '10,12',
            'showParent'   => "-1",
            'addWhereList' => '`price`>0',
            'paginate'     => 'pages',
            'display'      => 10,
        ));

        $DL->debug->clearLog();
        $DL->getChildrenCount();
        $this->getMethod($DL, "getChildrenList")->invoke($DL);

        $debug = new \Helpers\Collection($DL->debug->getLog());

        $getChildrenCount = $debug->get(0);
        $getChildrenList = $debug->get(1);

        $this->assertEquals($out[0], trim($getChildrenCount['msg']));
        $this->assertEquals($out[1], trim($getChildrenList['msg']));
    }
}
