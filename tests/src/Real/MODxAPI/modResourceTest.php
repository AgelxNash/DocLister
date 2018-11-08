<?php namespace DocLister\Tests\MODxAPI\Real;

use modResource;
use DocLister\Tests\Real\TestAbstract;
use Exception;

class modResourceTest extends TestAbstract
{
    protected $object;

    public function setUp()
    {
        $this->modx = $this->mockMODX();

        $this->object = new modResource($this->modx);
    }

    public function testGetId()
    {
        $this->assertNull($this->object->getID());
    }

    public function testCreate()
    {
        $pagetitle = 'test';
        $template = 'Minimal Template';

        $out = $this->object->create(compact('pagetitle', 'template'));

        $this->assertInstanceOf(modResource::class, $out);
        $this->assertSame($pagetitle, $this->object->get('pagetitle'));
        $this->assertSame(1, $this->object->get('template'));

        $template = 'bug';
        try {
            $out->set('template', $template);
        } catch (Exception $exception) {
            $this->assertSame($exception->getMessage(), sprintf('Template %s is not exists', $template));
        }

        $this->object->set('template', 0);
        $this->assertSame(0, $this->object->get('template'));

        $this->assertFalse($this->object->save());

        $this->assertArrayHasKey('rootForbidden', $this->object->getLog());

        $this->modx->config['udperms_allowroot'] = true;

        $this->assertGreaterThan(0, $this->object->save());
    }
}
