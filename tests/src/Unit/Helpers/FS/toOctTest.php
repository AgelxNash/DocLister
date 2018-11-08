<?php namespace DocLister\Tests\Unit\Helpers\FS;

use \Helpers\FS;

class toOctTest extends \PHPUnit_Framework_TestCase
{
    protected $FS = null;

    public function setUp()
    {
        $this->FS = FS::getInstance();
    }

    public function test8StringTo8Success644()
    {
        $this->assertSame(0644, $this->FS->toOct('0644'));
    }

    public function test8to8Success644()
    {
        $this->assertSame(0644, $this->FS->toOct(0644));
    }

    public function test10to8Error644()
    {
        $this->assertNotEquals(0644, $this->FS->toOct(644));
    }

    public function test10StringTo8Success644()
    {
        $this->assertSame(0644, $this->FS->toOct('644'));
    }

    public function test8StringTo8Success755()
    {
        $this->assertSame(0755, $this->FS->toOct('0755'));
    }

    public function test8to8Success755()
    {
        $this->assertSame(0755, $this->FS->toOct(0755));
    }

    public function test10to8Error755()
    {
        $this->assertNotEquals(0755, $this->FS->toOct(755));
    }

    public function test10StringTo8Success755()
    {
        $this->assertSame(0755, $this->FS->toOct('755'));
    }

    public function test8StringTo8Success400()
    {
        $this->assertSame(0400, $this->FS->toOct('0400'));
    }

    public function test8to8Success400()
    {
        $this->assertSame(0400, $this->FS->toOct(0400));
    }

    public function test10to8Error400()
    {
        $this->assertNotEquals(0400, $this->FS->toOct(400));
    }

    public function test10StringTo8Success400()
    {
        $this->assertSame(0400, $this->FS->toOct('400'));
    }
}
