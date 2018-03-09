<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Component\HttpFoundation;

class QueryParameterBag extends \Symfony\Component\HttpFoundation\ParameterBag
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct($parameters);
        $this->addUnusedParameters();
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $this->addUnusedParameters();

        return parent::all();
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        $this->addUnusedParameters();

        return parent::keys();
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $parameters = [])
    {
        $this->addUnusedParameters();
        parent::replace($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function add(array $parameters = [])
    {
        $this->addUnusedParameters();
        parent::add($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $this->addUnusedParameters();

        return parent::get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->addUnusedParameters();
        parent::set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        $this->addUnusedParameters();

        return parent::has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->addUnusedParameters();
        parent::remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlpha($key, $default = '')
    {
        $this->addUnusedParameters();

        return parent::getAlpha($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlnum($key, $default = '')
    {
        $this->addUnusedParameters();

        return parent::getAlnum($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getDigits($key, $default = '')
    {
        $this->addUnusedParameters();

        return parent::getDigits($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getInt($key, $default = 0)
    {
        $this->addUnusedParameters();

        return parent::getInt($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getBoolean($key, $default = false)
    {
        $this->addUnusedParameters();

        return parent::getBoolean($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function filter($key, $default = null, $filter = FILTER_DEFAULT, $options = [])
    {
        $this->addUnusedParameters();

        return parent::filter($key, $default, $filter, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $this->addUnusedParameters();

        return parent::getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $this->addUnusedParameters();

        return parent::count();
    }

    /**
     * Add unused \Contao\Input parameters, used for contao auto_item handling.
     */
    protected function addUnusedParameters(): void
    {
        // handle \Contao\Input unused $_GET parameters
        if (!empty($_GET)) {
            parent::add($_GET);
        }
    }
}
