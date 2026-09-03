<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class CheckStringTest extends \PHPUnit\Framework\TestCase
{
    public function testLatin()
    {
        $this->assertTrue(APIhelpers::checkString('hello', 1, array('eng')));
        $this->assertFalse(APIhelpers::checkString('Hello World', 1, array('eng')));
        $this->assertTrue(APIhelpers::checkString('Hello World', 1, array('eng'), array(' ')));
    }

    public function testCyrillic()
    {
        $this->assertTrue(APIhelpers::checkString('Привет', 1, array('rus')));
        $this->assertTrue(APIhelpers::checkString('Ёлка', 1, array('rus')));
    }

    public function testDigits()
    {
        $this->assertTrue(APIhelpers::checkString('0123456789', 1, array('num')));
    }

    public function testForbiddenChars()
    {
        $this->assertFalse(APIhelpers::checkString('hello123', 1, array('eng')));
        $this->assertFalse(APIhelpers::checkString('ПриветHi', 1, array('rus')));
        $this->assertFalse(APIhelpers::checkString('hello!', 1, array('eng')));
    }

    public function testCombinedAlphabets()
    {
        $this->assertTrue(APIhelpers::checkString('hello123', 1, array('eng', 'num')));
        $this->assertTrue(APIhelpers::checkString('ПриветHi', 1, array('rus', 'eng')));
    }

    public function testAlphabetNameIsCaseInsensitive()
    {
        $this->assertTrue(APIhelpers::checkString('abc', 1, array('ENG')));
    }

    public function testMinLength()
    {
        $this->assertFalse(APIhelpers::checkString('abc', 4, array('eng')));
        $this->assertTrue(APIhelpers::checkString('abc', 3, array('eng')));
    }

    public function testEdgeCharactersWithMixArray()
    {
        $this->assertTrue(APIhelpers::checkString('01-23', 1, array('num'), array('-')));
        $this->assertTrue(APIhelpers::checkString('user-01', 1, array('eng', 'num'), array('-')));
        $this->assertFalse(APIhelpers::checkString('user-01', 1, array('num'), array('-')));
    }

    public function testEmptyAlphabetRejectsAnyChar()
    {
        $this->assertFalse(APIhelpers::checkString('a', 1, array()));
    }

    public function testEmptyStringPassesWithZeroMinLength()
    {
        $this->assertTrue(APIhelpers::checkString('', 0, array()));
        $this->assertFalse(APIhelpers::checkString('', 1, array()));
    }

    /**
     * Длина сравнивается до и после trim(): строки с граничными пробелами
     * считаются некорректными, а не молча нормализуются.
     */
    public function testLeadingTrailingSpacesAreRejected()
    {
        $this->assertFalse(APIhelpers::checkString('  abc  ', 1, array('eng')));
    }
}
