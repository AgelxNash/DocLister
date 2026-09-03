<?php namespace DocLister\Tests\Unit\ApiHelpers;

use APIhelpers;

class GetEnvTest extends \PHPUnit\Framework\TestCase
{
    const KEY = 'DL_UNIT_TEST_GETENV_KEY_X31';

    private $inServer = false;
    private $inEnv = false;
    private $serverValue;
    private $envValue;

    protected function setUp(): void
    {
        $this->inServer = array_key_exists(self::KEY, $_SERVER);
        $this->serverValue = $this->inServer ? $_SERVER[self::KEY] : null;
        $this->inEnv = array_key_exists(self::KEY, $_ENV);
        $this->envValue = $this->inEnv ? $_ENV[self::KEY] : null;
        unset($_SERVER[self::KEY], $_ENV[self::KEY]);
    }

    protected function tearDown(): void
    {
        unset($_SERVER[self::KEY], $_ENV[self::KEY]);
        if ($this->inServer) {
            $_SERVER[self::KEY] = $this->serverValue;
        }
        if ($this->inEnv) {
            $_ENV[self::KEY] = $this->envValue;
        }
    }

    public function testServerVariableHasPriority()
    {
        $_SERVER[self::KEY] = 'from-server';
        $_ENV[self::KEY] = 'from-env';

        $this->assertSame('from-server', APIhelpers::getEnv(self::KEY));
    }

    public function testEnvVariableFallback()
    {
        $_ENV[self::KEY] = 'from-env';

        $this->assertSame('from-env', APIhelpers::getEnv(self::KEY));
    }

    public function testUnknownKeyReturnsFalse()
    {
        $this->assertFalse(APIhelpers::getEnv('DL_UNIT_TEST_GETENV_ABSENT_Q7'));
    }

    /**
     * isset() срабатывает и на пустую строку, поэтому значение из $_SERVER
     * возвращается как есть, без перехода к следующей ветке поиска.
     */
    public function testEmptyServerValueIsReturnedAsIs()
    {
        $_SERVER[self::KEY] = '';

        $this->assertSame('', APIhelpers::getEnv(self::KEY));
    }
}
