<?php namespace DocLister\Tests;

abstract class ModxAbstract extends TestAbstract {
	protected $modx = null;

	public function setUp(){
		$this->modx = $this->mockMODX();
		$this->assertTrue($this->modx instanceof \DocumentParser);
		$this->assertTrue($this->modx->db instanceof \DBAPI);
	}

	protected function mockDBAPI(){
		$DBAPI = $this->getMockBuilder('DBAPI')
			->setMethods(array('query', 'makeArray', 'escape', 'getValue'))
			->getMock();

		$DBAPI->expects($this->any())
			->method('makeArray')
			->will($this->returnValue(array()));

		$DBAPI->expects($this->any())
			->method('escape')
			->will($this->returnArgument(0));

		$DBAPI->expects($this->any())
			->method('getValue')
			->will($this->returnValue('db_value'));

		return $DBAPI;
	}
	protected function mockMODX(array $config = array()){
		$modx = $this->getMockBuilder('DocumentParser')
			->setMethods(array('getFullTableName'))
			->getMock();

		$modx->expects($this->any())
			->method('getFullTableName')
			->will($this->returnArgument(0));

		$modx->db = $this->mockDBAPI();
		$modx->config = array_merge(array(
			'manager_language' => 'russian-UTF8',
			'site_start' => 1
		), $config);

		return $modx;

	}
}