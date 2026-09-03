<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class GenPassTest extends \PHPUnit\Framework\TestCase
{
    public function testLength()
    {
        $this->assertSame(12, strlen(APIhelpers::genPass(12, 'A')));
        $this->assertSame(0, strlen(APIhelpers::genPass(0, 'A')));
    }

    public function testUppercaseOnly()
    {
        $this->assertMatchesRegularExpression('/^[A-Z]+$/', APIhelpers::genPass(20, 'A'));
    }

    public function testLowercaseOnly()
    {
        $this->assertMatchesRegularExpression('/^[a-z]+$/', APIhelpers::genPass(20, 'a'));
    }

    public function testDigitsOnly()
    {
        $this->assertMatchesRegularExpression('/^[0-9]+$/', APIhelpers::genPass(20, '0'));
    }

    public function testPrintableChars()
    {
        $pass = APIhelpers::genPass(20, '.');
        $this->assertSame(20, strlen($pass));
        foreach (str_split($pass) as $char) {
            $this->assertGreaterThanOrEqual(33, ord($char));
            $this->assertLessThanOrEqual(126, ord($char));
        }
    }

    public function testDefaultRuleset()
    {
        $pass = APIhelpers::genPass(16);
        $this->assertSame(16, strlen($pass));
        $this->assertMatchesRegularExpression('/^[!-~]+$/', $pass);
    }

    public function testMixedRuleset()
    {
        $pass = APIhelpers::genPass(20, 'aaaA');
        $this->assertMatchesRegularExpression('/^[A-Za-z]+$/', $pass);
    }
}
