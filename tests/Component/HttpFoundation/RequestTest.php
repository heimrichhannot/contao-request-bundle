<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Test\Component\HttpFoundation;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestTest extends ContaoTestCase
{
    /**
     * @var Request
     */
    protected $request;

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        unset($_GET, $_POST);
    }

    public function setUp()
    {
        parent::setUp();

        $_POST = [];
        $_GET = [];

        $requestStack = new RequestStack();
        $requestStack->push(new \Symfony\Component\HttpFoundation\Request());

        $backendMatcher = new RequestMatcher('/contao', 'test.com', null, ['192.168.1.0']);
        $frontendMatcher = new RequestMatcher('/index', 'test.com', null, ['192.168.1.0']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $this->request = new Request($this->mockContaoFramework(), $requestStack, $scopeMatcher);

        $container = $this->mockContainer();
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

        $result = $this->request;
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Request::class, $result);
        $this->assertSame([], $result->request->all());
        $this->assertSame([], $result->query->all());

        $result = $this->request->getAllPost();
        $this->assertSame([], $result);

        $result = $this->request->getAllPostHtml();
        $this->assertSame([], $result);

        $result = $this->request->getAllPostRaw();
        $this->assertSame([], $result);

        $_GET = ['id' => 12];

        $result = $this->request;
        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\Request::class, $result);
        $this->assertSame(12, $result->query->get('id'));
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
        $this->request->query->replace(['id' => 22]);
        $result = $this->request->getGet(null, true);
        $this->assertSame(['id' => 22], $result->all());
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
        $this->request->request->set('test', ['id' => 12]);

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
        $this->request->request->add(['test' => ['id' => '12']]);

        $result = $this->request->getAllPost();
        $this->assertSame(['test' => ['id' => '12']], $result);

        $result = $this->request->getAllPostHtml();
        $this->assertSame(['test' => ['id' => '12']], $result);

        $result = $this->request->getAllPostRaw();
        $this->assertSame(['test' => ['id' => '12']], $result);

        $requestStack = new RequestStack();
        $requestStack->push(new \Symfony\Component\HttpFoundation\Request());
    }

    public function testXssClean()
    {
        $uuid = \Contao\StringUtil::uuidToBin('9c6697cf-c874-11e7-8bb3-a08cfddc0261');
        $result = $this->request->xssClean(['<script>alert(\'xss\')</script>', $uuid]);
        $this->assertSame(['<script>alert(\'xss\')</script>', $uuid], $result);
    }

    public function testFrontendInsertTagEncoding()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_scope', 'frontend');
        $request->query->add(['test' => '{{email::test@test.com}}']);
        $request->request->add(['test' => '{{email::test@test.com}}']);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $backendMatcher = new RequestMatcher(null, null, null, null, ['_scope' => 'backend']);
        $frontendMatcher = new RequestMatcher(null, null, null, null, ['_scope' => 'frontend']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $this->request = new Request($this->mockContaoFramework(), $requestStack, $scopeMatcher);

        $this->assertSame('&#123;&#123;email::test@test.com&#125;&#125;', $this->request->getGet('test'));
        $this->assertSame('&#123;&#123;email::test@test.com&#125;&#125;', $this->request->getPost('test'));
    }

    public function testBackendInsertTagEncoding()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_scope', 'backend');
        $request->query->add(['test' => '{{email::test@test.com}}']);
        $request->request->add(['test' => '{{email::test@test.com}}']);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $backendMatcher = new RequestMatcher(null, null, null, null, ['_scope' => 'backend']);
        $frontendMatcher = new RequestMatcher(null, null, null, null, ['_scope' => 'frontend']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $this->request = new Request($this->mockContaoFramework(), $requestStack, $scopeMatcher);

        $this->assertSame('&#123;&#123;email::test@test.com&#125;&#125;', $this->request->getGet('test'));
        $this->assertSame('{{email::test@test.com}}', $this->request->getPost('test'));
    }

    public function getUnusedGet()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_scope', 'frontend');
        $request->query->add(['test' => 1234]);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $backendMatcher = new RequestMatcher(null, null, null, null, ['_scope' => 'backend']);
        $frontendMatcher = new RequestMatcher(null, null, null, null, ['_scope' => 'frontend']);

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $this->request = new Request($this->mockContaoFramework(), $requestStack, $scopeMatcher);

        $this->assertSame(1234, $this->request->getGet('test'));

        $_GET['auto_item'] = 'foo';

        $this->assertSame('foo', $this->request->getGet('auto_item'));
        $this->assertSame('foo', $this->request->query->get('auto_item'));
        $this->assertSame(['test' => 1234, 'auto_item' => 'foo'], $this->request->query->all());
    }

    public function testCheckCurrentRequest()
    {
        $request = $this->getMockBuilder(Request::class)->setMethods(null)->disableOriginalConstructor()->getMock();

        $reflectionClass = new \ReflectionClass(Request::class);

        $testMethod = $reflectionClass->getMethod('checkCurrentRequest');
        $testMethod->setAccessible(true);

        $this->assertSame([], $testMethod->invokeArgs($request, [null]));

        $parameterBag = new ParameterBag();
        $parameterBag->set('name', null);
        $result = $testMethod->invokeArgs($request, [$parameterBag]);

        $this->assertSame([], $result);
    }
}
