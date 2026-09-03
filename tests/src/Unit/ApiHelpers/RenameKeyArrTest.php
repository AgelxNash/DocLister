<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class RenameKeyArrTest extends \PHPUnit\Framework\TestCase
{
    public function testPrefix()
    {
        $this->assertSame(
            array('pre.a' => 1, 'pre.b' => 2),
            APIhelpers::renameKeyArr(array('a' => 1, 'b' => 2), 'pre')
        );
    }

    public function testSuffix()
    {
        $this->assertSame(
            array('a.suf' => 1),
            APIhelpers::renameKeyArr(array('a' => 1), '', 'suf')
        );
    }

    public function testPrefixAndSuffix()
    {
        $this->assertSame(
            array('pre.a.suf' => 1),
            APIhelpers::renameKeyArr(array('a' => 1), 'pre', 'suf')
        );
    }

    public function testCustomAddAndSeparators()
    {
        $this->assertSame(
            array('p-a-s' => 1),
            APIhelpers::renameKeyArr(array('a' => 1), 'p', 's', '-', '|')
        );
    }

    public function testNoPrefixNoSuffixReturnsAsIs()
    {
        $data = array('a' => 1, 'b' => array('c' => 2));
        $this->assertSame($data, APIhelpers::renameKeyArr($data, '', ''));
    }

    /**
     * Вложенные массивы разворачиваются в плоский список ключей,
     * а ключ узла-массива получает пустое строковое значение.
     */
    public function testNestedArrayIsFlattened()
    {
        $this->assertSame(
            array('p.a.b' => 1, 'p.a' => ''),
            APIhelpers::renameKeyArr(array('a' => array('b' => 1)), 'p')
        );
    }

    public function testNestedArrayWithSuffix()
    {
        $this->assertSame(
            array('a.b.s' => 1, 'a.s' => ''),
            APIhelpers::renameKeyArr(array('a' => array('b' => 1)), '', 's')
        );
    }

    public function testScalarAndArrayMix()
    {
        $this->assertSame(
            array('p.x' => 5, 'p.a.b' => 1, 'p.a.c' => 2, 'p.a' => ''),
            APIhelpers::renameKeyArr(array('x' => 5, 'a' => array('b' => 1, 'c' => 2)), 'p')
        );
    }
}
