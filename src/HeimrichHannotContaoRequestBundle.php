<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle;

use HeimrichHannot\RequestBundle\DependencyInjection\RequestExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoRequestBundle extends Bundle
{
    /**
     * @return RequestExtension
     */
    public function getContainerExtension()
    {
        return new RequestExtension();
    }
}