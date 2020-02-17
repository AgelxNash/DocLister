<?php namespace DocLister\Tests\Unit\DL;

use DocLister\Tests\Unit\ModxAbstract;

abstract class DLAbstract extends ModxAbstract
{
    /** @var \DocLister */
    protected $DL = null;


    public function setUp()
    {
        parent::setUp();

        $this->DL = $this->mockDocLister();
    }

    protected function mockDocLister($controller = null, array $cfg = array())
    {
        $cfg = array_merge(array('debug' => 0), $cfg);
        if (empty($controller)) {
            /** @var \DocLister|\PHPUnit_Framework_MockObject_MockObject $DL */
            $DL = $this->getMockForAbstractClass('DocLister', array($this->modx, $cfg));
            $DL->expects($this->any())
                ->method('getUrl')
                ->will($this->returnValue('url'));

            $DL->expects($this->any())
                ->method('getDocs')
                ->will($this->returnValue(array()));

            $DL->expects($this->any())
                ->method('_render')
                ->will($this->returnValue('example text'));

            $DL->expects($this->any())
                ->method('getChildrenCount')
                ->will($this->returnValue(0));

            $DL->expects($this->any())
                ->method('getChildrenFolder')
                ->will($this->returnValue(0));
        } else {
            /** @var \DocLister|\PHPUnit_Framework_MockObject_MockObject $DL */
            $DL = $this->getMock($controller . 'DocLister', array('sanitarData'), array($this->modx, $cfg));
        }

        /** Чтобы в debug хранились не экранированные результаты */
        $DL->expects($this->any())
            ->method('sanitarData')
            ->will($this->returnArgument(0));

        return $DL;
    }

    protected function fixDebugSQL($data)
    {
        if (is_array($data)) {
            foreach ($data as &$val) {
                $val = $this->fixDebugSQL($val);
            }
        } else {
            $data = preg_replace('/\s+/', ' ', $data);
        }

        return $data;
    }
}
