<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use HeimrichHannot\RequestBundle\HeimrichHannotContaoRequestBundle;
use Symfony\Component\Config\Loader\LoaderInterface;

class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(HeimrichHannotContaoRequestBundle::class)->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }

    /**
     * Allows a plugin to load container configuration.
     *
     * @param LoaderInterface $loader
     * @param array           $managerConfig
     *
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load('@HeimrichHannotContaoRequestBundle/Resources/config/services.yml');
    }
}
