<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\BeExplanationBundle\Tests;

use HeimrichHannot\RequestBundle\DependencyInjection\RequestExtension;
use HeimrichHannot\RequestBundle\HeimrichHannotContaoRequestBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoRequestBundleTest extends TestCase
{
    public function testGetContainerExtension()
    {
        $bundle = new HeimrichHannotContaoRequestBundle();

        $this->assertInstanceOf(RequestExtension::class, $bundle->getContainerExtension());
    }
}
