<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\DependencyInjection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\DependencyInjection\RequestExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class RequestExtensionTest extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => false]));
        $extension = new RequestExtension();
        $extension->load([], $container);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $extension = new RequestExtension();
        $this->assertInstanceOf(RequestExtension::class, $extension);
    }
}
