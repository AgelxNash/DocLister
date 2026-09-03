<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class MbUcfirstTest extends \PHPUnit\Framework\TestCase
{
    public function testLatin()
    {
        $this->assertSame('Hello world', APIhelpers::mb_ucfirst('hello world'));
        $this->assertSame('B', APIhelpers::mb_ucfirst('b'));
    }

    public function testCyrillic()
    {
        $this->assertSame('Привет', APIhelpers::mb_ucfirst('привет'));
        $this->assertSame('Москва', APIhelpers::mb_ucfirst('москва'));
    }

    public function testLeadingSpacesAreStripped()
    {
        $this->assertSame('Hello', APIhelpers::mb_ucfirst('  hello'));
        $this->assertSame('Привет', APIhelpers::mb_ucfirst(' привет'));
    }

    public function testEmptyString()
    {
        $this->assertSame('', APIhelpers::mb_ucfirst(''));
    }
}
