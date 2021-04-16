<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Test\Component\HttpFoundation;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class QueryParameterBagTest extends ContaoTestCase
{
    /**
     * @var Request
     */
    protected $request;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        unset($_GET);
    }

    public function setUp()
    {
        parent::setUp();

        if (!\defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        $_GET = [];

        $requestStack = new RequestStack();
        $requestStack->push(new \Symfony\Component\HttpFoundation\Request());

        $backendMatcher = new RequestMatcher('/contao');
        $frontendMatcher = new RequestMatcher('/index');

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $this->request = new Request($this->mockContaoFramework(), $requestStack, $scopeMatcher);
    }

    public function testAddUnusedParameters()
    {
        $_GET = ['id' => 12];

        $this->assertSame(['id' => 12], $this->request->query->all());

        $_GET = ['id' => 12, 'test' => 'test'];

        $this->assertSame(['id' => 12, 'test' => 'test'], $this->request->query->all());
    }

    public function testKeys()
    {
        $_GET = ['id' => 12, 'test' => 'test'];
        $this->assertSame(['id', 'test'], $this->request->query->keys());
    }

    public function testReplace()
    {
        $_GET = ['test' => 'test'];

        $this->request->query->replace(['test' => 13]);

        $this->assertSame(['test' => 13], $this->request->query->all());
    }

    public function testAdd()
    {
        $_GET = ['id' => 12, 'test' => 'test'];
        $this->request->query->add(['number' => 13]);

        $this->assertSame(['id' => 12, 'test' => 'test', 'number' => 13], $this->request->query->all());
    }

    public function testRemove()
    {
        $_GET = ['name' => 'foo']; // unused $_GET (auto_item…)

        $this->request->query->add(['foo' => 'bar']);
        $this->request->query->remove('name');

        $this->assertSame(['foo' => 'bar'], $this->request->query->all());
    }

    public function testGet()
    {
        $this->request->query->set('id', 13);
        $this->assertSame(13, $this->request->query->get('id'));
    }

    public function testSet()
    {
        $_GET = ['id' => 12];

        $this->request->query->set('id', 13);
        $this->assertSame(13, $this->request->query->get('id'));
    }

    public function testHas()
    {
        $_GET = ['id' => 12];

        $this->assertTrue($this->request->query->has('id'));
    }

    public function testGetAlpha()
    {
        $_GET = ['test' => '123Test', 'id' => 13];
        $this->assertSame('Test', $this->request->query->getAlpha('test'));
        $this->assertSame('', $this->request->query->getAlpha('id'));
    }

    public function testGetAlnum()
    {
        $_GET = ['test' => '123', 'id' => 13];
        $this->assertSame('123', $this->request->query->getAlnum('test'));
        $this->assertSame('13', $this->request->query->getAlnum('id'));
    }

    public function testGetDigits()
    {
        $_GET = ['test' => '123Test'];
        $this->assertSame('123', $this->request->query->getDigits('test'));
    }

    public function testGetBoolean()
    {
        $_GET = ['false' => '123Test', 'true' => 1];
        $this->assertFalse($this->request->query->getBoolean('false'));
        $this->assertTrue($this->request->query->getBoolean('true'));
    }

    public function testCount()
    {
        $_GET = ['false' => '123Test', 'true' => 1];
        $this->assertSame(2, $this->request->query->count());
    }

    public function testGetIterator()
    {
        $_GET = ['false' => '123Test', 'true' => 1];
        $this->assertInstanceOf(\ArrayIterator::class, $this->request->query->getIterator());
    }

    public function testFilter()
    {
        $_GET = ['test' => 'on', 'id' => 13];

        $this->assertTrue($this->request->query->filter('test', null, FILTER_VALIDATE_BOOLEAN));
    }

    public function testGetInt()
    {
        $_GET = ['test' => 'on', 'id' => 13];

        $this->assertSame(13, $this->request->query->getInt('id'));
    }
}
