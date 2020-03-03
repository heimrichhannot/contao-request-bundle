<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\EventListener;

use Contao\System;

class InsertTagsListener
{
    /**
     * @var array
     */
    private $supportedTags = [
        'request_get',
        'request_post',
    ];

    /**
     * Replaces request insert tags.
     *
     * @param string $tag
     *
     * @return string|false
     */
    public function onReplaceInsertTags(string $tag)
    {
        $elements = explode('::', $tag);
        $key = $elements[0];

        if (\in_array($key, $this->supportedTags, true)) {
            return $this->replaceRequestInsertTags($key, $elements[1]);
        }

        return false;
    }

    /**
     * Replaces a request-related insert tag.
     *
     * @param string $insertTag
     * @param string $key
     *
     * @return string
     */
    private function replaceRequestInsertTags(string $insertTag, string $key)
    {
        switch ($insertTag) {
            case 'request_get':
                return System::getContainer()->get('huh.request')->getGet($key);
            case 'request_post':
                return System::getContainer()->get('huh.request')->getPost($key);
        }
    }
}
