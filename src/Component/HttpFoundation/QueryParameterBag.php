<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\ParameterBag;

class QueryParameterBag extends ParameterBag
{
    /**
     * @var ParameterBag
     */
    protected $unused;

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::all();
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::keys();
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $parameters = [])
    {
        parent::replace($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function add(array $parameters = [])
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }
        parent::add($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }
        parent::set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        if (!empty($_GET)) {
            unset($_GET[$key]);
            $this->addUnused($_GET);
        }
        parent::remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlpha($key, $default = '')
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::getAlpha($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlnum($key, $default = '')
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::getAlnum($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getDigits($key, $default = '')
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::getDigits($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getInt($key, $default = 0)
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::getInt($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getBoolean($key, $default = false)
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::getBoolean($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function filter($key, $default = null, $filter = FILTER_DEFAULT, $options = [])
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::filter($key, $default, $filter, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (!empty($_GET)) {
            $this->addUnused($_GET);
        }

        return parent::count();
    }

    /**
     * Add unused \Contao\Input parameters, used for contao auto_item handling.
     *
     * @param array|null $unused
     */
    protected function addUnused(array $unused = []): void
    {
        $this->unused = new ParameterBag($unused);
        $this->parameters = array_merge($this->unused->all(), $this->parameters);
    }
}
