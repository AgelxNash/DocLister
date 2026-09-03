<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class MbLcfirstTest extends \PHPUnit\Framework\TestCase
{
    public function testLatin()
    {
        $this->assertSame('hELLO', APIhelpers::mb_lcfirst('HELLO'));
        $this->assertSame('hello', APIhelpers::mb_lcfirst('Hello'));
        $this->assertSame('b', APIhelpers::mb_lcfirst('B'));
    }

    public function testCyrillic()
    {
        $this->assertSame('мОСКВА', APIhelpers::mb_lcfirst('МОСКВА'));
        $this->assertSame('привет', APIhelpers::mb_lcfirst('Привет'));
    }

    public function testFirstCharOnly()
    {
        $this->assertSame('sECOND Third', APIhelpers::mb_lcfirst('SECOND Third'));
    }

    public function testEmptyString()
    {
        $this->assertSame('', APIhelpers::mb_lcfirst(''));
    }
}
