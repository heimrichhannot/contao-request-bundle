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
    }

    public function testAddUnusedParameters()
    {
        $_POST = ['id' => 12];

        $requestStack = new RequestStack();
        $requestStack->push(new \Symfony\Component\HttpFoundation\Request());

        $request = new Request($this->mockContaoFramework(), $requestStack);

        $this->assertSame(['id' => 12], $request->request->all());

        $_POST = ['id' => 12, 'test' => 'test'];

        $this->assertSame(['id' => 12, 'test' => 'test'], $request->request->all());
    }
}
