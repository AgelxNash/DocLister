<?php namespace DocLister\Tests\Unit\MODxAPI\modUsers;

use DocLister\Tests\Unit\MODxAPI\apiAbstract;

class checkBlockTest extends apiAbstract
{
    /** @var \modUsers */
    protected $modUsers = null;

    public function setUp()
    {
        parent::setUp();
        $this->modUsers = $this->mockModUsers();
        $this->setProperty($this->modUsers, 'id', 1);
    }

    public function testNoBlockedUntil()
    {
        $this->modUsers->set('blockeduntil', strtotime("+1 DAY"))
            ->set('blocked', 0);
        $this->assertTrue($this->modUsers->checkBlock());
    }

    public function testNoBlockedAfter()
    {
        $this->modUsers->set('blockedafter', strtotime("+1 DAY"))
            ->set('blocked', 0);
        $this->assertFalse($this->modUsers->checkBlock());
    }

    public function testNoBlockedUntilEnd()
    {
        $this->modUsers->set('blockeduntil', strtotime("-1 DAY"))
            ->set('blocked', 0);
        $this->assertFalse($this->modUsers->checkBlock());
    }

    public function testNoBlockedAfterEnd()
    {
        $this->modUsers->set('blockedafter', strtotime("-1 DAY"))
            ->set('blocked', 0);
        $this->assertTrue($this->modUsers->checkBlock());
    }

    public function testYesBlockedUntil()
    {
        $this->modUsers->set('blockeduntil', strtotime("+1 DAY"))
            ->set('blocked', 1);
        $this->assertTrue($this->modUsers->checkBlock());
    }

    public function testYesBlockedAfter()
    {
        $this->modUsers->set('blockedafter', strtotime("+1 DAY"))
            ->set('blocked', 1);
        $this->assertFalse($this->modUsers->checkBlock());
    }

    public function testYesBlockedUntilEnd()
    {
        $this->modUsers->set('blockeduntil', strtotime("-1 DAY"))
            ->set('blocked', 1);
        $this->assertFalse($this->modUsers->checkBlock());
    }

    public function testYesBlockedAfterEnd()
    {
        $this->modUsers->set('blockedafter', strtotime("-1 DAY"))
            ->set('blocked', 1);
        $this->assertTrue($this->modUsers->checkBlock());
    }

    public function testBlocked()
    {
        $this->modUsers->set('blocked', 1);
        $this->assertTrue($this->modUsers->checkBlock());
    }

    public function testNoBlocked()
    {
        $this->modUsers->set('blocked', 0);
        $this->assertFalse($this->modUsers->checkBlock());
    }
}
