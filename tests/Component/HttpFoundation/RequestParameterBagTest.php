<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Test\Component\HttpFoundation;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestParameterBagTest extends ContaoTestCase
{
    /**
     * @var Request
     */
    protected $request;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        unset($_POST);
    }

    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_MODE')) {
            define('TL_MODE', 'FE');
        }

        $_POST = [];

        $requestStack = new RequestStack();
        $requestStack->push(new \Symfony\Component\HttpFoundation\Request());

        $this->request = new Request($this->mockContaoFramework(), $requestStack);
    }

    public function testAddUnusedParameters()
    {
        $_POST = ['id' => 12];

        $this->assertSame(['id' => 12], $this->request->request->all());

        $_POST = ['id' => 12, 'test' => 'test'];

        $this->assertSame(['id' => 12, 'test' => 'test'], $this->request->request->all());
    }

    public function testKeys()
    {
        $_POST = ['id' => 12, 'test' => 'test'];
        $this->assertSame(['id', 'test'], $this->request->request->keys());
    }

    public function testReplace()
    {
        $_POST = ['test' => 'test'];

        $this->request->request->replace(['test' => 13]);

        $this->assertSame(['test' => 13], $this->request->request->all());
    }

    public function testAdd()
    {
        $_POST = ['id' => 12, 'test' => 'test'];
        $this->request->request->add(['number' => 13]);

        $this->assertSame(['id' => 12, 'test' => 'test', 'number' => 13], $this->request->request->all());
    }

    public function testRemove()
    {
        $_POST = ['name' => 'foo']; // unused $_POST (auto_item…)

        $this->request->request->add(['foo' => 'bar']);
        $this->request->request->remove('name');

        $this->assertSame(['foo' => 'bar'], $this->request->request->all());
    }

    public function testGet()
    {
        $this->request->request->set('id', 13);
        $this->assertSame(13, $this->request->request->get('id'));
    }

    public function testSet()
    {
        $this->request->request->set('id', 13);
        $this->assertSame(13, $this->request->request->get('id'));
    }

    public function testHas()
    {
        $this->request->request->set('id', 13);
        $this->assertTrue($this->request->request->has('id'));
    }

    public function testGetAlpha()
    {
        $_POST = ['test' => '123Test', 'id' => 13];
        $this->assertSame('Test', $this->request->request->getAlpha('test'));
        $this->assertSame('', $this->request->request->getAlpha('id'));
    }

    public function testGetAlnum()
    {
        $_POST = ['test' => '123', 'id' => 13];
        $this->assertSame('123', $this->request->request->getAlnum('test'));
        $this->assertSame('13', $this->request->request->getAlnum('id'));
    }
}
