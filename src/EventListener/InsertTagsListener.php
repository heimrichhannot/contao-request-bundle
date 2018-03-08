<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\EventListener;

use HeimrichHannot\RequestBundle\Request;

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
    public function onReplaceInsertTags($tag)
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
    private function replaceRequestInsertTags($insertTag, $key)
    {
        switch ($insertTag) {
            case 'request_get':
                return Request::getGet($key);
            case 'request_post':
                return Request::getPost($key);
        }

        return '';
    }
}
