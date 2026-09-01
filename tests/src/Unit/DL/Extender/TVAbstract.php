<?php namespace DocLister\Tests\Unit\DL\Extender;

use DocLister\Tests\Unit\DL\DLAbstract;

abstract class TVAbstract extends DLAbstract
{
    protected $TVList = array(
        'price' => 1,
        'image' => 2,
        'other' => 3
    );

    public function getTVExtender($DL)
    {
        $extTV = $this->getMockBuilder('tv_DL_Extender')
            ->onlyMethods(array('getTVnames'))
            ->setConstructorArgs(array($DL, 'tv'))
            ->getMock();

        $extTV->expects($this->any())
            ->method('getTVnames')
            ->will($this->returnValue($this->TVList));

        $this->setProperty($DL, 'extTV', $extTV);

        return $extTV;
    }
}
