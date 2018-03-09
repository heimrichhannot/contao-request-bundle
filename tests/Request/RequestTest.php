<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Test\Request;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\Request;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use Symfony\Component\HttpFoundation\RequestStack;

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

        $container = $this->mockContainer();
        $container->set('huh.utils.container', new ContainerUtil($this->mockContaoFramework()));

        // request stack
        $request = new \Symfony\Component\HttpFoundation\Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $container->set('request_stack', $requestStack);

        System::setContainer($container);
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

        $result = $this->request->cleanHtml('value', false, true, '', false);
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
        $_POST = ['test' => ['id' => 12]];

        $result = $this->request->getPost('test', true);
        $this->assertSame(['id' => '12'], $result);
        $this->assertNull($this->request->getPost('blaFoo'));

        $result = $this->request->getPostHtml('test', true);
        $this->assertSame(['id' => '12'], $result);
        $this->assertNull($this->request->getPostHtml('blaFoo'));

        $result = $this->request->getPostRaw('test', true);
        $this->assertSame(['id' => '12'], $result);
        $this->assertNull($this->request->getPostRaw('blaFoo'));
    }

    public function testGetAllPost()
    {
        $result = $this->request->getAllPost();
        $this->assertSame(['test' => ['id' => '12']], $result);

        $result = $this->request->getAllPostHtml();
        $this->assertSame(['test' => ['id' => '12']], $result);

        $result = $this->request->getAllPostRaw();
        $this->assertSame(['test' => ['id' => '12']], $result);

        $request = new Request($this->mockContaoFramework());

        $_POST = null;

        $result = $request->getAllPost();
        $this->assertSame([], $result);

        $result = $request->getAllPostHtml();
        $this->assertSame([], $result);

        $result = $request->getAllPostRaw();
        $this->assertSame([], $result);
    }

    public function testXssClean()
    {
        $uuid = \Contao\StringUtil::uuidToBin('9c6697cf-c874-11e7-8bb3-a08cfddc0261');
        $result = $this->request->xssClean(['<script>alert("test")</script>', $uuid]);
        $this->assertSame(['<script>alert("test")</script>', $uuid], $result);
    }
}
