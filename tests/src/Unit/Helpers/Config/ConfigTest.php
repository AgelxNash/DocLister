<?php namespace DocLister\Tests\Unit\Helpers\Config;

use \Helpers\Config;
use \PHPUnit\Framework\TestCase;
use \stdClass;

class ConfigTest extends TestCase
{
    public function testSetConfigMergeAndOverwrite()
    {
        $Config = new Config();

        $this->assertSame(1, $Config->setConfig(array('a' => 1)));
        $this->assertSame(2, $Config->setConfig(array('b' => 2, 'a' => 3)));

        $config = $Config->getConfig();
        $this->assertSame(3, $config['a']);
        $this->assertSame(2, $config['b']);

        $this->assertSame(1, $Config->setConfig(array('c' => 4), true));
        $this->assertSame(array('c' => 4), $Config->getConfig());
    }

    public function testSetConfigNotArray()
    {
        $Config = new Config();

        $this->assertFalse($Config->setConfig('string'));
        $this->assertSame(array(), $Config->getConfig());
    }

    public function testConstructorWithArray()
    {
        $Config = new Config(array('x' => 'y'));

        $this->assertSame(array('x' => 'y'), $Config->getConfig());
    }

    public function testGetCFGDef()
    {
        $Config = new Config(array(
            'exists' => 'value',
            'empty' => '',
            'zero' => 0,
        ));

        $this->assertSame('value', $Config->getCFGDef('exists', 'def'));
        $this->assertSame('', $Config->getCFGDef('empty', 'def'));
        $this->assertSame(0, $Config->getCFGDef('zero', 'def'));
        $this->assertSame('def', $Config->getCFGDef('missing', 'def'));
        $this->assertNull($Config->getCFGDef('missing'));
    }

    public function testLoadArrayFromJsonString()
    {
        $Config = new Config();

        $this->assertSame(array('a' => 1), $Config->loadArray('{"a":1}'));
        $this->assertSame(array('x', 'y'), $Config->loadArray('["x","y"]'));
    }

    public function testLoadArrayFromSeparatedString()
    {
        $Config = new Config();

        $this->assertSame(array('a', 'b', 'c'), $Config->loadArray('a,b,c'));
        $this->assertSame(array('a', 'b'), $Config->loadArray('a|b', '|'));
        $this->assertSame(array(), $Config->loadArray('a,b', ''));
        $this->assertSame(array(), $Config->loadArray(',,'));
    }

    public function testLoadArrayFromOtherTypes()
    {
        $Config = new Config();

        $array = array('keep' => 'as-is');
        $this->assertSame($array, $Config->loadArray($array));

        $object = new stdClass();
        $object->prop = 1;
        $loaded = $Config->loadArray($object);
        $this->assertCount(1, $loaded);
        $this->assertSame($object, $loaded[0]);

        $this->assertSame(array(), $Config->loadArray(123));
        $this->assertSame(array(), $Config->loadArray(null));
    }

    public function testLoadConfigFromCoreFile()
    {
        $Config = new Config();
        $Config->setPath('assets/snippets/DocLister');

        $loaded = $Config->loadConfig('paginate:core');

        $this->assertSame('pages', $loaded['paginate']);
        $this->assertSame('10', $loaded['display']);
        $this->assertSame('pages', $Config->getCFGDef('paginate', ''));
    }

    public function testLoadConfigMultipleNames()
    {
        $Config = new Config();
        $Config->setPath('assets/snippets/DocLister');

        $loaded = $Config->loadConfig('paginate:core;crumbs:core');

        $this->assertSame('pages', $loaded['paginate']);
        $this->assertArrayHasKey('tpl', $loaded);
        $this->assertArrayHasKey('debug', $loaded);
    }

    public function testLoadConfigMissingFile()
    {
        $Config = new Config();
        $Config->setPath('assets/snippets/DocLister');

        $this->assertSame(array(), $Config->loadConfig('not-exists:core'));
        $this->assertSame(array(), $Config->loadConfig('paginate'));
        $this->assertSame(array(), $Config->loadConfig(null));
    }
}
