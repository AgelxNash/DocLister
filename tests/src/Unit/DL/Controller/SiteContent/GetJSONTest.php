<?php namespace DocLister\Tests\Unit\DL\Controller\SiteContent;

use DocLister\Tests\Unit\DL\DLAbstract;

class GetJSONTest extends DLAbstract
{
    public function testJsonFormatNewContainsRowsAndTotal()
    {
        $this->modx->config['server_offset_time'] = 0;

        $DL = $this->mockDocLister('site_content', array(
            'api' => '1',
            'makeUrl' => 0,
            'JSONformat' => 'new',
        ));

        $json = $DL->getJSON(array(array(
            'id' => 1,
            'pagetitle' => 'Doc',
            'pub_date' => 0,
        )), '1');
        $data = json_decode($json, true);

        $this->assertArrayHasKey('rows', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertCount(1, $data['rows']);
        $this->assertSame(1, $data['rows'][0]['id']);
    }

    /**
     * Контракт публичных форматов JSON: инициализация $return в getJSON()
     * не меняет результат для форматов old и simple.
     */
    public function testJsonFormatOldAndSimpleRemainUnchanged()
    {
        $this->modx->config['server_offset_time'] = 0;

        $rows = array(array(
            'id' => 1,
            'pagetitle' => 'Doc',
            'pub_date' => 0,
        ));

        $DL = $this->mockDocLister('site_content', array(
            'api' => '1',
            'makeUrl' => 0,
        ));
        $old = json_decode($DL->getJSON($rows, '1'), true);
        $this->assertSame(array('id', 'pagetitle', 'pub_date', 'date', 'title', 'e_title'), array_keys($old[0]));

        $DL = $this->mockDocLister('site_content', array(
            'api' => '1',
            'makeUrl' => 0,
            'JSONformat' => 'simple',
        ));
        $simple = json_decode($DL->getJSON($rows, '1'), true);
        $this->assertSame(array(0), array_keys($simple));
        $this->assertSame(1, $simple[0]['id']);
    }
}
