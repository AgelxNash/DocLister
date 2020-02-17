<?php namespace DocLister\Tests\Unit;


abstract class ModxAbstract extends TestAbstract
{
    protected $modx = null;

    public function setUp()
    {
        $this->modx = $this->mockMODX();
        $this->assertTrue($this->modx instanceof \DocumentParser);
        $this->assertTrue($this->modx->db instanceof \DBAPI);
    }

    protected function mockDBAPI()
    {
        $DBAPI = $this->getMockBuilder('DBAPI')
            ->setMethods(array('query', 'makeArray', 'getRow', 'escape', 'getValue'))
            ->getMock();

        $DBAPI->expects($this->any())
            ->method('makeArray')
            ->will($this->returnValue(array()));

        $DBAPI->expects($this->any())
            ->method('getRow')
            ->will($this->returnValue([]));

        $DBAPI->expects($this->any())
            ->method('escape')
            ->will($this->returnArgument(0));

        $DBAPI->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue('db_value'));

        return $DBAPI;
    }

    protected function mockMODX(array $config = array())
    {
        $modx = $this->getMockBuilder('\DocumentParser')
            ->setMethods(array('getFullTableName'))
            ->getMock();

        $modx->expects($this->any())
            ->method('getFullTableName')
            ->will($this->returnArgument(0));

        $modx->db = $this->mockDBAPI();

        $modx->documentObject = array(
            'id'              => 1,
            'type'            => 'document',
            'contentType'     => 'text/html',
            'pagetitle'       => 'New document',
            'longtitle'       => '',
            'description'     => '',
            'alias'           => '',
            'link_attributes' => '',
            'published'       => 1,
            'pub_date'        => 0,
            'unpub_date'      => 0,
            'parent'          => 0,
            'isfolder'        => 0,
            'introtext'       => '',
            'content'         => '',
            'richtext'        => 1,
            'template'        => 0,
            'menuindex'       => 0,
            'searchable'      => 1,
            'cacheable'       => 1,
            'createdon'       => 0,
            'createdby'       => 0,
            'editedon'        => 0,
            'editedby'        => 0,
            'deleted'         => 0,
            'deletedon'       => 0,
            'deletedby'       => 0,
            'publishedon'     => 0,
            'publishedby'     => 0,
            'menutitle'       => '',
            'donthit'         => 0,
            'haskeywords'     => 0,
            'hasmetatags'     => 0,
            'privateweb'      => 0,
            'privatemgr'      => 0,
            'content_dispo'   => 0,
            'hidemenu'        => 0,
            'alias_visible'   => 1
        );
        $modx->documentIdentifier = 1;
        $modx->config = array_merge(array(
            'manager_language' => 'russian-UTF8',
            'site_start'       => 1,
            'site_url'         => 'http://example.com/',
        ), $config);

        $modx->_TVnames = array(
            'testA' => array(
                'id'   => 1,
                'type' => 'string',
                'default' => '',
                'display' => '',
                'display_params' => ''
            ),
            'testB' => array(
                'id'   => 2,
                'type' => 'string',
                'default' => '',
                'display' => '',
                'display_params' => ''
            )
        );

        return $modx;

    }
}
