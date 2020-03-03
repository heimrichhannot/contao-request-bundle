<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Test\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\RequestBundle\EventListener\InsertTagsListener;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class InsertTagsListenerTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!\defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        $container = $this->mockContainer();
        $requestStack = new RequestStack();
        $requestStack->push(new \Symfony\Component\HttpFoundation\Request());

        $backendMatcher = new RequestMatcher('/contao');
        $frontendMatcher = new RequestMatcher('/index');

        $scopeMatcher = new ScopeMatcher($backendMatcher, $frontendMatcher);

        $container->set('huh.request', new Request($this->mockContaoFramework(), $requestStack, $scopeMatcher));
        System::setContainer($container);
    }

    public function testOnReplaceInsertTags()
    {
        $listener = new InsertTagsListener();
        $result = $listener->onReplaceInsertTags('');
        $this->assertFalse($result);

        System::getContainer()->get('huh.request')->setGet('auto_item', 'success');
        $result = $listener->onReplaceInsertTags('request_get::auto_item');
        $this->assertSame('success', $result);

        System::getContainer()->get('huh.request')->setPost('auto_item', 'success');
        $result = $listener->onReplaceInsertTags('request_post::auto_item');
        $this->assertSame('success', $result);
    }

    public function testReplaceRequestInsertTags()
    {
        $listener = new InsertTagsListener();

        $function = $this->getMethod(InsertTagsListener::class, 'replaceRequestInsertTags');
        $this->assertNull($function->invokeArgs($listener, ['', 'key']));
    }

    protected function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
