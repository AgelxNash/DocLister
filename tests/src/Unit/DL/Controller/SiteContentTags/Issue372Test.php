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

        $this->assertStringContainsString("t.`name` IN (", $filters['where']);
        $this->assertStringContainsString('Blue', $filters['where']);
        $this->assertStringContainsString('Dark Grey', $filters['where']);
        $this->assertStringContainsString('Turquoise', $filters['where']);
        $this->assertStringNotContainsString("t.`name`='Blue,Dark Grey", $filters['where']);
        $this->assertStringContainsString('site_content_tags', $filters['join']);
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

        $this->assertStringContainsString("t.`name` IN (", $filters['where']);
        $this->assertStringContainsString('Green', $filters['where']);
        $this->assertStringNotContainsString('Blue||Green', $filters['where']);
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

        $this->assertStringContainsString("t.`name`='Blue'", $filters['where']);
        $this->assertStringNotContainsString(' IN (', $filters['where']);
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
