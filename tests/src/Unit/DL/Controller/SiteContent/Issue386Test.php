<?php namespace DocLister\Tests\Unit\DL\Controller\SiteContent;

use DocLister\Tests\Unit\DL\DLAbstract;

class Issue386Test extends DLAbstract
{
    /**
     * @see: https://github.com/AgelxNash/DocLister/issues/386
     */
    public function testApiDateUsesPubDateAfterDocumentWithoutPubDate()
    {
        $this->modx->config['server_offset_time'] = 0;

        $createdon = strtotime('2024-02-08 00:00:00');
        $pubDate = strtotime('2023-01-03 00:00:00');

        $DL = $this->mockDocLister('site_content', array(
            'api' => '1',
            'makeUrl' => 0,
            'dateSource' => 'pub_date',
            'dateFormat' => 'd.m.Y',
            'JSONformat' => 'simple',
        ));

        $json = $DL->getJSON(array(
            array(
                'id' => 1,
                'pagetitle' => 'Today',
                'pub_date' => 0,
                'createdon' => $createdon,
            ),
            array(
                'id' => 15,
                'pagetitle' => 'Backdated',
                'pub_date' => $pubDate,
                'createdon' => $createdon,
            ),
        ), '1');
        $data = json_decode($json, true);

        $this->assertSame(date('d.m.Y', $createdon), $data[0]['date']);
        $this->assertSame(date('d.m.Y', $pubDate), $data[1]['date']);
        $this->assertNotSame($data[0]['date'], $data[1]['date']);
    }
}
