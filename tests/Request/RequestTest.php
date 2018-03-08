<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Test\Request;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\Request;

class RequestTest extends ContaoTestCase
{
    /**
     * @var Request
     */
    protected $request;

    public function setUp()
    {
        parent::setUp();

        $this->request = new Request($this->mockContaoFramework());
    }

    public function testGetInstance()
    {
        $_GET = null;
        $_POST = null;

        $result = $this->request->getInstance();
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Request::class, $result);

        $_GET = ['id' => 12];
        $_POST = ['id' => 12];

        $result = $this->request->getInstance();
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Request::class, $result);
        $this->assertSame(12, $result->query->get('id'));
        $this->assertSame(12, $result->request->get('id'));
    }

    public function testSetGet()
    {
        $this->request->setGet('test', null);
        $this->assertFalse($this->request->hasGet('test'));

        $this->request->setGet('test', 'test');
        $this->assertTrue($this->request->hasGet('test'));
        $this->assertSame('test', $this->request->getGet('test'));
    }

    public function testSetPost()
    {
        $this->request->setPost('test', null);
        $this->assertFalse($this->request->hasPost('test'));

        $this->request->setPost('test', 'test');
        $this->assertTrue($this->request->hasPost('test'));
        $this->assertSame('test', $this->request->getPost('test'));
    }

    public function testGetGet()
    {
        $_GET = ['id' => 12];

        $result = $this->request->getGet(null, true);
        $this->assertSame(['id' => 12], $result->all());
    }

    public function testClean()
    {
        $result = $this->request->clean(null);
        $this->assertNull($result);
    }

    public function testCleanHtml()
    {
        $result = $this->request->cleanHtml(null);
        $this->assertNull($result);

        $result = $this->request->cleanHtml('value', false, true, null, false);
        $this->assertSame('value', $result);
    }

    public function testCleanRaw()
    {
        $result = $this->request->cleanRaw(null);
        $this->assertNull($result);

        $result = $this->request->cleanRaw('value', true, true);
        $this->assertSame('value', $result);
    }

    public function testGetPost()
    {
        $_POST = ['id' => 12];

        $result = $this->request->getPost(null, true);
        $this->assertSame(['id' => 12], $result->all());

        $result = $this->request->getPostHtml(null, true);
        $this->assertSame(['id' => 12], $result->all());

        $result = $this->request->getPostRaw(null, true);
        $this->assertSame(['id' => 12], $result->all());
    }

    public function testXssClean()
    {
        $uuid = \Contao\StringUtil::uuidToBin('9c6697cf-c874-11e7-8bb3-a08cfddc0261');
        $result = $this->request->xssClean(['<script>alert("test")</script>', $uuid]);
        $this->assertSame(['<script>alert("test")</script>', $uuid], $result);
    }
}
