<?php namespace DocLister\Tests\Unit\DL\Extender;

use DocLister\Tests\Unit\DL\DLAbstract;

abstract class TVAbstract extends DLAbstract
{
    protected $TVList = array(
        'price' => 1,
        'image' => 2,
        'other' => 3
    );

    public function getTVExtender(&$DL)
    {
        $extTV = $this->getMock('tv_DL_Extender', array('getTVnames'), array($DL, 'tv'));

        $extTV->expects($this->any())
            ->method('getTVnames')
            ->will($this->returnValue($this->TVList));

        $this->setProperty($DL, 'extTV', $extTV);

        return $extTV;
    }
}
