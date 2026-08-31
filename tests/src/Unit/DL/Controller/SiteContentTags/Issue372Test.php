<?php namespace DocLister\Tests\Unit\DL\Controller\SiteContentTags;

use DocLister\Tests\Unit\DL\DLAbstract;

class Issue372Test extends DLAbstract
{
    /**
     * @see: https://github.com/AgelxNash/DocLister/issues/372
     */
    public function testStaticCommaSeparatedTagsBecomeInFilter()
    {
        $DL = $this->mockDocLister('site_content_tags', array(
            'debug' => 0,
            'idType' => 'documents',
            'documents' => '1',
            'tagsTV' => 7,
            'tagsData' => 'static:Blue,Dark Grey,Green,Light Grey,Orange,Purple,Red,Turquoise',
        ));

        $filters = $this->getProperty($DL, '_filters');

        $this->assertContains("t.`name` IN (", $filters['where']);
        $this->assertContains('Blue', $filters['where']);
        $this->assertContains('Dark Grey', $filters['where']);
        $this->assertContains('Turquoise', $filters['where']);
        $this->assertNotContains("t.`name`='Blue,Dark Grey", $filters['where']);
        $this->assertContains('site_content_tags', $filters['join']);
    }

    public function testConfiguredPipeSeparatorStillWins()
    {
        $DL = $this->mockDocLister('site_content_tags', array(
            'debug' => 0,
            'idType' => 'documents',
            'documents' => '1',
            'tagsData' => 'static:Blue||Green||Red',
        ));

        $filters = $this->getProperty($DL, '_filters');

        $this->assertContains("t.`name` IN (", $filters['where']);
        $this->assertContains('Green', $filters['where']);
        $this->assertNotContains(',', $filters['where']);
    }

    public function testSingleTagKeepsEqualityFilter()
    {
        $DL = $this->mockDocLister('site_content_tags', array(
            'debug' => 0,
            'idType' => 'documents',
            'documents' => '1',
            'tagsData' => 'static:Blue',
        ));

        $filters = $this->getProperty($DL, '_filters');

        $this->assertContains("t.`name`='Blue'", $filters['where']);
        $this->assertNotContains(' IN (', $filters['where']);
    }

    public function testGetModeWithoutRequestDoesNotJoinTags()
    {
        $DL = $this->mockDocLister('site_content_tags', array(
            'debug' => 0,
            'idType' => 'documents',
            'documents' => '1',
            'tagsData' => 'get:myTags',
        ));

        $filters = $this->getProperty($DL, '_filters');

        $this->assertSame('', $filters['join']);
        $this->assertSame('', $filters['where']);
    }
}
