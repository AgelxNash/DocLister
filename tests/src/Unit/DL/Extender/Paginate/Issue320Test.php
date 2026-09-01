<?php namespace DocLister\Tests\Unit\DL\Extender\Paginate;

use DocLister\Tests\Unit\DL\DLAbstract;

class Issue320Test extends DLAbstract
{
    /**
     * @see: https://github.com/AgelxNash/DocLister/issues/320
     *
     * @dataProvider providerOffsetPaginationContract
     */
    public function testOffsetReducesPaginationTotal($childrenCount, $cfg, $expectedPages, $expectedDocs)
    {
        $DL = $this->getMockBuilder('site_contentDocLister')
            ->onlyMethods(array('sanitarData', 'getChildrenCount'))
            ->setConstructorArgs(array($this->modx, array_merge(array(
                'debug' => 0,
                'idType' => 'parents',
                'parents' => 5,
                'paginate' => 'pages',
                'noRedirect' => 1,
            ), $cfg)))
            ->getMock();
        $DL->expects($this->any())
            ->method('sanitarData')
            ->will($this->returnArgument(0));
        $DL->expects($this->any())
            ->method('getChildrenCount')
            ->will($this->returnValue($childrenCount));

        $DL->getDocs();

        $paginate = $DL->getExtender('paginate');

        $this->assertInstanceOf('paginate_DL_Extender', $paginate);
        $this->assertSame($expectedPages, $paginate->totalPage());
        $this->assertSame($expectedDocs, $paginate->totalDocs());
        $this->assertSame($childrenCount, $DL->getChildrenCount());
    }

    public function providerOffsetPaginationContract()
    {
        return array(
            'offset skips leftover page' => array(
                25,
                array('display' => 10, 'offset' => 5),
                2,
                20,
            ),
            'without offset keeps raw total' => array(
                25,
                array('display' => 10, 'offset' => 0),
                3,
                25,
            ),
            'offset greater than count yields empty listing' => array(
                5,
                array('display' => 10, 'offset' => 10),
                0,
                0,
            ),
            'maxDocs already includes offset in getChildrenCount' => array(
                20,
                array('display' => 10, 'offset' => 5, 'maxDocs' => 20),
                2,
                20,
            ),
        );
    }

    public function testOffsetChangesListLimitButNotCountQuery()
    {
        $out = array(
            "SELECT count(*) FROM (SELECT count(*) FROM site_content as `c`  WHERE c.deleted=0 AND c.published=1 AND (c.parent IN ('5') OR c.id IN('5')) GROUP BY `c`.`id` ) as `tmp`",
            "SELECT c.* FROM site_content as `c`  WHERE (c.parent IN ('5') OR c.id IN('5')) AND c.deleted=0 AND c.published=1 GROUP BY `c`.`id` ORDER BY if(c.pub_date=0,c.createdon,c.pub_date) DESC LIMIT 5,10"
        );

        $DL = $this->mockDocLister('site_content', array(
            'debug' => 1,
            'idType' => 'parents',
            'parents' => 5,
            'showParent' => 1,
            'paginate' => 'pages',
            'display' => 10,
            'offset' => 5,
            'noRedirect' => 1,
        ));

        $DL->debug->clearLog();
        $DL->getChildrenCount();
        $this->getMethod($DL, 'getChildrenList')->invoke($DL);

        $debug = new \Helpers\Collection($DL->debug->getLog());

        $getChildrenCount = $debug->get(0);
        $getChildrenList = $debug->get(1);

        $this->assertEquals($out[0], trim($getChildrenCount['msg']));
        $this->assertEquals($out[1], trim($getChildrenList['msg']));
    }
}
