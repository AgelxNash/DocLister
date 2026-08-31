<?php namespace DocLister\Tests\Unit\DL\Controller\Onetable;

use DocLister\Tests\Unit\DL\DLAbstract;

class getJSONTest extends DLAbstract
{
    public function testPrepareClosureCanAddFieldInApiMode()
    {
        $prepareCalled = false;
        $prepare = function ($data, $modx, $DL, $_ext) use (&$prepareCalled) {
            $prepareCalled = true;
            $data['aaaaa'] = 'AAAAAAAA';

            return $data;
        };

        $DL = $this->mockDocLister('onetable', array(
            'prepare' => $prepare,
            'api' => '1',
            'JSONformat' => 'simple',
        ));

        $json = $DL->getJSON(array(array(
            'id' => 1,
            'status_id' => 1,
            'fields' => '{}',
            'hash' => 'test',
        )), '1');
        $data = json_decode($json, true);

        $this->assertTrue($prepareCalled);
        $this->assertSame('AAAAAAAA', $data[0]['aaaaa']);
    }
}
