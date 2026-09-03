<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class SanitarInTest extends \PHPUnit\Framework\TestCase
{
    private $hadModx = false;
    private $modxBackup = null;

    protected function setUp(): void
    {
        $this->hadModx = array_key_exists('modx', $GLOBALS);
        $this->modxBackup = $this->hadModx ? $GLOBALS['modx'] : null;
        $modx = new \stdClass();
        $modx->db = new \DBAPI();
        $GLOBALS['modx'] = $modx;
    }

    protected function tearDown(): void
    {
        if ($this->hadModx) {
            $GLOBALS['modx'] = $this->modxBackup;
        } else {
            unset($GLOBALS['modx']);
        }
    }

    public function testStringInput()
    {
        $this->assertSame("'1','2','3'", APIhelpers::sanitarIn('1,2,3'));
    }

    public function testArrayInput()
    {
        $this->assertSame("'a','b'", APIhelpers::sanitarIn(array('a', 'b')));
    }

    public function testEmptyItemsAreSkipped()
    {
        $this->assertSame("'1','2'", APIhelpers::sanitarIn('1,,2'));
        $this->assertSame("''", APIhelpers::sanitarIn(''));
        $this->assertSame("''", APIhelpers::sanitarIn(array()));
    }

    public function testCustomSeparator()
    {
        $this->assertSame("'1','2'", APIhelpers::sanitarIn('1;2', ';'));
        $this->assertSame('1,2', APIhelpers::sanitarIn('1|2', '|', false));
    }

    public function testWithoutQuotes()
    {
        $this->assertSame('1,2,3', APIhelpers::sanitarIn('1,2,3', ',', false));
    }

    /**
     * Нескалярный вход без массива даёт пустой список (обёрнутый в кавычки при $quote=true).
     */
    public function testNonScalarNonArrayGivesEmptyList()
    {
        $this->assertSame("''", APIhelpers::sanitarIn(new \stdClass()));
    }

    /**
     * Каждый непустой элемент проходит через $modx->db->escape(),
     * и в выходную строку попадает именно результат escape().
     */
    public function testEachItemIsEscaped()
    {
        $modx = new \stdClass();
        $modx->db = new class {
            public $escaped = array();

            public function escape($data)
            {
                $this->escaped[] = $data;

                return 'E:' . $data;
            }
        };
        $GLOBALS['modx'] = $modx;

        $this->assertSame("'E:x','E:y'", APIhelpers::sanitarIn('x,,y'));
        $this->assertSame(array('x', 'y'), $modx->db->escaped);
    }
}
