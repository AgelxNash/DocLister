<?php namespace DocLister\Tests\Helpers\FS;

use \Helpers\FS;

class toOctTest extends \PHPUnit_Framework_TestCase {
	protected $FS = null;

	public function setUp(){
		$this->FS = FS::getInstance();
	}
	public function test8StringTo8Success(){
		$this->assertNotEquals(0755, '0755');
		$this->assertEquals(0755, $this->FS->toOct('0755'));
	}

	public function test8to8Success(){
		$this->assertEquals(0755, 0755);
		$this->assertEquals(0755, $this->FS->toOct(0755));
	}

	public function test10to8Success(){
		$this->assertNotEquals(0755, 755);
		$this->assertEquals(0755, $this->FS->toOct(755));
	}

	public function test10StringTo8Success(){
		$this->assertNotEquals(0755, '755');
		$this->assertEquals(0755, $this->FS->toOct('755'));
	}
}