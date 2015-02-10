<?php namespace DocLister\Tests;

abstract class DLAbstract extends ModxAbstract {
	protected $DL = null;

	public function setUp(){
		parent::setUp();

		$this->DL = $this->mockDocLister();
		$this->assertTrue($this->DL instanceof \DocLister);
	}

	protected function mockDocLister(){

		/** @var \DocLister $DL */
		$DL = $this->getMockForAbstractClass('DocLister', array($this->modx, array(
			'debug' => 0
		)), '', true);
		$DL->expects($this->any())
			->method('getUrl')
			->will($this->returnValue(FALSE));

		return $DL;
	}
}