<?php namespace DocLister\Tests\Unit\MODxAPI;

use DocLister\Tests\Unit\ModxAbstract;

abstract class apiAbstract extends ModxAbstract
{
    /** @var \MODxAPI */
    protected $api = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = $this->mockMODxAPI();
    }

    protected function mockMODxAPI()
    {
        /** @var \MODxAPI|\PHPUnit\Framework\MockObject\MockObject $api */
        $api = $this->getMockForAbstractClass('MODxAPI', array($this->modx));
        $api->expects($this->any())
            ->method('edit')
            ->will($this->returnValue($api));

        $api->expects($this->any())
            ->method('save')
            ->will($this->returnValue($api->getID())); //$this->id || false

        $api->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(true));

        return $api;
    }

    protected function mockModUsers()
    {
        $obj = $this->getMockBuilder('modUsers')
            ->setConstructorArgs(array($this->modx))
            ->onlyMethods(array())
            ->getMock();

        return $obj;
    }
}
