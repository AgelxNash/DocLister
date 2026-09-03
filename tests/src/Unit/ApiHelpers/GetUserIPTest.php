<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class GetUserIPTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Все ключи, которые getUserIP() читает через getEnv()/$_SERVER.
     */
    private static $ipKeys = array(
        'HTTP_COMING_FROM',
        'HTTP_X_COMING_FROM',
        'HTTP_VIA',
        'HTTP_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    );

    private $backup = array();

    protected function setUp(): void
    {
        foreach (self::$ipKeys as $key) {
            $this->backup[$key] = array(
                'in_server' => array_key_exists($key, $_SERVER),
                'server'    => array_key_exists($key, $_SERVER) ? $_SERVER[$key] : null,
                'in_env'    => array_key_exists($key, $_ENV),
                'env'       => array_key_exists($key, $_ENV) ? $_ENV[$key] : null,
            );
            unset($_SERVER[$key], $_ENV[$key]);
        }
    }

    protected function tearDown(): void
    {
        foreach (self::$ipKeys as $key) {
            unset($_SERVER[$key], $_ENV[$key]);
            if ($this->backup[$key]['in_server']) {
                $_SERVER[$key] = $this->backup[$key]['server'];
            }
            if ($this->backup[$key]['in_env']) {
                $_ENV[$key] = $this->backup[$key]['env'];
            }
        }
    }

    public function testDefaultWhenNothingDetected()
    {
        $this->assertSame('127.0.0.1', APIhelpers::getUserIP());
        $this->assertSame('10.0.0.1', APIhelpers::getUserIP('10.0.0.1'));
    }

    public function testRemoteAddrFallback()
    {
        $_SERVER['REMOTE_ADDR'] = '192.0.2.10';

        $this->assertSame('192.0.2.10', APIhelpers::getUserIP());
    }

    public function testForwardedForHasPriorityOverRemoteAddr()
    {
        $_SERVER['REMOTE_ADDR'] = '192.0.2.10';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.5';

        $this->assertSame('203.0.113.5', APIhelpers::getUserIP());
    }

    public function testViaHasPriorityOverRemoteAddr()
    {
        $_SERVER['REMOTE_ADDR'] = '192.0.2.10';
        $_SERVER['HTTP_VIA'] = '198.51.100.7';

        $this->assertSame('198.51.100.7', APIhelpers::getUserIP());
    }

    /**
     * Значение proxy-заголовка проверяется регуляркой IPv4 уже после выбора ветки,
     * поэтому мусорный заголовок глушит даже валидный REMOTE_ADDR
     * и метод отдаёт значение по умолчанию.
     */
    public function testGarbageProxyHeaderFallsBackToDefault()
    {
        $_SERVER['REMOTE_ADDR'] = '192.0.2.10';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'unknown';

        $this->assertSame('127.0.0.1', APIhelpers::getUserIP());
    }

    public function testNonIpv4RemoteAddrReplacedByDefault()
    {
        $_SERVER['REMOTE_ADDR'] = 'not-an-ip';

        $this->assertSame('127.0.0.1', APIhelpers::getUserIP());
    }
}
