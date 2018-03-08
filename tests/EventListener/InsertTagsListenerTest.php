<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Test\EventListener;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\EventListener\InsertTagsListener;
use HeimrichHannot\RequestBundle\Request;

class InsertTagsListenerTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_MODE')) {
            define('TL_MODE', 'FE');
        }

        $container = $this->mockContainer();
        $container->set('huh.request.request', new Request($this->mockContaoFramework()));
        System::setContainer($container);
    }

    public function testOnReplaceInsertTags()
    {
        $listener = new InsertTagsListener();
        $result = $listener->onReplaceInsertTags('');
        $this->assertFalse($result);

        System::getContainer()->get('huh.request.request')->setGet('auto_item', 'success');
        $result = $listener->onReplaceInsertTags('request_get::auto_item');
        $this->assertSame('success', $result);

        System::getContainer()->get('huh.request.request')->setPost('auto_item', 'success');
        $result = $listener->onReplaceInsertTags('request_post::auto_item');
        $this->assertSame('success', $result);
    }
}
