<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Component\HttpFoundation;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Input;
use Contao\StringUtil;
use Contao\Validator;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class Request extends \Symfony\Component\HttpFoundation\Request
{
    /**
     * Query string parameters ($_GET).
     *
     * @var QueryParameterBag
     */
    public $query;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var ScopeMatcher
     */
    protected $scopeMatcher;

    /**
     * Request constructor.
     */
    public function __construct(ContaoFrameworkInterface $framework, RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->framework = $framework;
        $this->scopeMatcher = $scopeMatcher;

        $request = $requestStack->getCurrentRequest();

        if (null === $request) {
            parent::__construct([], [], [], [], [], [], null);
        } else {
            parent::__construct(
                $this->checkCurrentRequest($request->query),
                $this->checkCurrentRequest($request->request),
                $this->checkCurrentRequest($request->attributes),
                $this->checkCurrentRequest($request->cookies),
                $this->checkCurrentRequest($request->files),
                $this->checkCurrentRequest($request->server),
                $request->getContent()
            );
        }

        // As long as contao adds unused parameters to $_GET and $_POST Globals inside \Contao\Input, we have to add them inside custom ParameterBag classes
        $this->query = new QueryParameterBag($request && $request->query ? $request->query->all() : []);
    }

    /**
     * For test purposes use \Symfony\Component\HttpFoundation\Request::create() for dummy data.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function setRequest(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->initialize($request->query->all(), $request->request->all(), $request->attributes->all(), $request->cookies->all(), $request->files->all(), $request->server->all(), $request->getContent());

        // As long as contao adds unused parameters to $_GET and $_POST Globals inside \Contao\Input, we have to add them inside custom ParameterBag classes
        $this->query = new QueryParameterBag($request->query->all());

        return $this;
    }

    /**
     * Shorthand setter for query arguments ($_GET).
     *
     * @param string $key   The requested field
     * @param mixed  $value The input value
     */
    public function setGet(string $key, $value)
    {
        // Convert special characters (see #7829)
        $key = str_replace([' ', '.', '['], '_', $key);

        $key = Input::cleanKey($key);

        if (null === $value) {
            $this->query->remove($key);
        } else {
            $this->query->set($key, $value);
        }
    }

    /**
     * Shorthand setter for request arguments ($_POST).
     *
     * @param string $key   The requested field
     * @param mixed  $value The input value
     */
    public function setPost(string $key, $value)
    {
        $key = Input::cleanKey($key);

        if (null === $value) {
            $this->request->remove($key);
        } else {
            $this->request->set($key, $value);
        }
    }

    /**
     * Shorthand getter for query arguments ($_GET).
     *
     * @param string $postKey        The requested field
     * @param bool   $decodeEntities If true, all entities will be decoded
     * @param bool   $tidy           If true, varValue is tidied up
     *
     * @return mixed If no $strkey is defined, return all cleaned query parameters, otherwise the cleaned requested query value
     */
    public function getGet(string $postKey = null, bool $decodeEntities = false, bool $tidy = false)
    {
        if (null === $postKey) {
            $postValues = $this->query;

            if ($decodeEntities) {
                foreach ($postValues as $key => &$value) {
                    $value = $this->clean($value, $decodeEntities, true, $tidy);
                }
            }

            return $postValues;
        }

        return $this->clean($this->query->get($postKey), $decodeEntities, true, $tidy);
    }

    /**
     * XSS clean, decodeEntities, tidy/strip tags, encode special characters and encode inserttags and return save, cleaned value(s).
     *
     * @param mixed $value            The input value
     * @param bool  $decodeEntities   If true, all entities will be decoded
     * @param bool  $encodeInsertTags If true, encode the opening and closing delimiters of insert tags
     * @param bool  $tidy             If true, varValue is tidied up
     * @param bool  $strictMode       If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed The cleaned value
     */
    public function clean($value, bool $decodeEntities = false, bool $encodeInsertTags = true, bool $tidy = true, bool $strictMode = true)
    {
        // do not clean, otherwise empty string will be returned, not null
        if (null === $value) {
            return $value;
        }

        if (\is_array($value)) {
            foreach ($value as $i => $childValue) {
                $value[$i] = $this->clean($childValue, $decodeEntities, $encodeInsertTags, $tidy, $strictMode);
            }

            return $value;
        }

        // do not handle binary uuid
        if (Validator::isUuid($value)) {
            return $value;
        }

        $value = $this->xssClean($value, $strictMode);

        if ($tidy) {
            $value = $this->tidy($value);
        } else {
            // decodeEntities for tidy is more complex, because non allowed tags should be displayed as readable text, not as html entity
            $value = Input::decodeEntities($value);
        }

        // do not encodeSpecialChars when tidy did run, otherwise non allowed tags will be encoded twice
        if (!$decodeEntities && !$tidy) {
            $value = Input::encodeSpecialChars($value);
        }

        if ($encodeInsertTags) {
            $value = Input::encodeInsertTags($value);
        }

        return $value;
    }

    /**
     * XSS clean, decodeEntities, tidy/strip tags, encode special characters and encode inserttags and return save, cleaned value(s).
     *
     * @param mixed  $value            The input value
     * @param bool   $decodeEntities   If true, all entities will be decoded
     * @param bool   $encodeInsertTags If true, encode the opening and closing delimiters of insert tags
     * @param string $allowedTags      List of allowed html tags
     * @param bool   $tidy             If true, varValue is tidied up
     * @param bool   $strictMode       If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed The cleaned value
     */
    public function cleanHtml($value, bool $decodeEntities = false, bool $encodeInsertTags = true, string $allowedTags = '', bool $tidy = true, bool $strictMode = true)
    {
        // do not clean, otherwise empty string will be returned, not null
        if (null === $value) {
            return $value;
        }

        if (\is_array($value)) {
            foreach ($value as $i => $childValue) {
                $value[$i] = $this->cleanHtml($childValue, $decodeEntities, $encodeInsertTags, $allowedTags, $tidy, $strictMode);
            }

            return $value;
        }

        // do not handle binary uuid
        if (Validator::isUuid($value)) {
            return $value;
        }

        $value = $this->xssClean($value, $strictMode);

        if ($tidy) {
            $value = $this->tidy($value, $allowedTags, $decodeEntities);
        } else {
            // decodeEntities for tidy is more complex, because non allowed tags should be displayed as readable text, not as html entity
            $value = Input::decodeEntities($value);
        }

        // do not encodeSpecialChars when tidy did run, otherwise non allowed tags will be encoded twice
        if (!$decodeEntities && !$tidy) {
            $value = Input::encodeSpecialChars($value);
        }

        if ($encodeInsertTags) {
            $value = Input::encodeInsertTags($value);
        }

        return $value;
    }

    /**
     * XSS clean, preserve basic entities encode inserttags and return raw unsafe but filtered value.
     *
     * @param mixed $value            The input value
     * @param bool  $encodeInsertTags If true, encode the opening and closing delimiters of insert tags
     * @param bool  $tidy             If true, varValue is tidied up
     * @param bool  $strictMode       If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed The cleaned value
     */
    public function cleanRaw($value, bool $encodeInsertTags = true, bool $tidy = false, bool $strictMode = false)
    {
        // do not clean, otherwise empty string will be returned, not null
        if (null === $value) {
            return $value;
        }

        if (\is_array($value)) {
            foreach ($value as $i => $childValue) {
                $value[$i] = $this->cleanRaw($childValue, $encodeInsertTags, $tidy, $strictMode);
            }

            return $value;
        }

        // do not handle binary uuid
        if (Validator::isUuid($value)) {
            return $value;
        }

        $value = $this->xssClean($value, $strictMode);

        if ($tidy) {
            $value = $this->tidy($value);
        }

        $value = Input::preserveBasicEntities($value);

        if ($encodeInsertTags) {
            $value = Input::encodeInsertTags($value);
        }

        return $value;
    }

    /**
     * Returns true if the get parameter is defined.
     *
     * @param string $key The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function hasGet(string $key)
    {
        return $this->query->has($key);
    }

    /**
     * Shorthand getter for request arguments ($_POST).
     *
     * @param string $key            The requested field
     * @param bool   $decodeEntities If true, all entities will be decoded
     * @param bool   $tidy           If true, varValue is tidied up
     * @param bool   $strictMode     If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed|null If no $strKey is defined, return all cleaned query parameters, otherwise the cleaned requested query value
     */
    public function getPost(string $key, bool $decodeEntities = false, bool $tidy = true, bool $strictMode = true)
    {
        if (!$this->request->has($key)) {
            return null;
        }

        return $this->clean($this->request->get($key), $decodeEntities, $this->scopeMatcher->isFrontendRequest($this), $tidy, $strictMode);
    }

    /**
     * Shorthand getter for request arguments ($_POST).
     *
     * @param string $strKey         The requested field
     * @param bool   $decodeEntities If true, all entities will be decoded
     * @param bool   $tidy           If true, varValue is tidied up
     * @param bool   $strictMode     If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return array If no $strKey is defined, return all cleaned query parameters, otherwise the cleaned requested query value
     */
    public function getAllPost(bool $decodeEntities = false, bool $tidy = true, bool $strictMode = true): array
    {
        $arrValues = $this->request->all();

        if (empty($arrValues)) {
            return $arrValues;
        }

        foreach ($arrValues as $key => &$varValue) {
            $varValue = $this->clean($varValue, $decodeEntities, $this->scopeMatcher->isFrontendRequest($this), $tidy, $strictMode);
        }

        return $arrValues;
    }

    /**
     * Shorthand getter for request arguments ($_POST) preserving allowed HTML tags.
     *
     * @param string $key            The requested field
     * @param bool   $decodeEntities If true, all entities will be decoded
     * @param string $allowedTags    List of allowed html tags
     * @param bool   $tidy           If true, varValue is tidied up
     * @param bool   $strictMode     If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed|null If no $strKey is defined, return all cleaned query parameters, otherwise the cleaned requested query value
     */
    public function getPostHtml(string $key, bool $decodeEntities = false, string $allowedTags = '', bool $tidy = true, bool $strictMode = true)
    {
        if (!$this->request->has($key)) {
            return null;
        }

        return $this->cleanHtml($this->request->get($key), $decodeEntities, $this->scopeMatcher->isFrontendRequest($this), $allowedTags, $tidy, $strictMode);
    }

    /**
     * Shorthand getter for request arguments ($_POST) preserving allowed HTML tags.
     *
     * @param string $strKey         The requested field
     * @param bool   $decodeEntities If true, all entities will be decoded
     * @param string $allowedTags    List of allowed html tags
     * @param bool   $tidy           If true, varValue is tidied up
     * @param bool   $strictMode     If true, the xss cleaner removes also JavaScript event handlers
     */
    public function getAllPostHtml(bool $decodeEntities = false, string $allowedTags = '', bool $tidy = true, bool $strictMode = true): array
    {
        $arrValues = $this->request->all();

        if (empty($arrValues)) {
            return $arrValues;
        }

        foreach ($arrValues as $key => &$varValue) {
            $varValue = $this->cleanHtml($varValue, $decodeEntities, $this->scopeMatcher->isFrontendRequest($this), $allowedTags, $tidy, $strictMode);
        }

        return $arrValues;
    }

    /**
     * Shorthand getter for request arguments ($_POST), returning raw, unsafe but filtered values.
     *
     * @param string $key        The requested field
     * @param bool   $tidy       If true, varValue is tidied up
     * @param bool   $strictMode If true, the xss cleaner removes also JavaScript event handlers
     *
     * @return mixed|null If no $strkey is defined, return all cleaned query parameters, otherwise the cleaned requested query value
     */
    public function getPostRaw(string $key, bool $tidy = false, bool $strictMode = false)
    {
        if (!$this->request->has($key)) {
            return null;
        }

        return $this->cleanRaw($this->request->get($key), $this->scopeMatcher->isFrontendRequest($this), $tidy, $strictMode);
    }

    /**
     * Shorthand getter for request arguments ($_POST), returning raw, unsafe but filtered values.
     *
     * @param string $strKey     The requested field
     * @param bool   $tidy       If true, varValue is tidied up
     * @param bool   $strictMode If true, the xss cleaner removes also JavaScript event handlers
     */
    public function getAllPostRaw(bool $tidy = false, bool $strictMode = false): array
    {
        $arrValues = $this->request->all();

        if (empty($arrValues)) {
            return $arrValues;
        }

        foreach ($arrValues as $key => &$varValue) {
            $varValue = $this->cleanRaw($varValue, $this->scopeMatcher->isFrontendRequest($this), $tidy, $strictMode);
        }

        return $arrValues;
    }

    /**
     * Clean a value and try to prevent XSS attacks.
     *
     * @param mixed $varValue   A string or array
     * @param bool  $strictMode If true, the function removes also JavaScript event handlers
     *
     * @return mixed The cleaned string or array
     */
    public function xssClean($varValue, bool $strictMode = false)
    {
        if (\is_array($varValue)) {
            foreach ($varValue as $key => $value) {
                $varValue[$key] = $this->xssClean($value, $strictMode);
            }

            return $varValue;
        }

        // do not xss clean binary uuids
        if (Validator::isBinaryUuid($varValue)) {
            return $varValue;
        }

        // Fix issue StringUtils::decodeEntites() returning empty string when value is 0 in some contao 4.9 versions
        if ('0' !== $varValue && 0 !== $varValue) {
            $varValue = StringUtil::decodeEntities($varValue);
        }

        $varValue = preg_replace('/(&#[A-Za-z0-9]+);?/i', '$1;', $varValue);

        // fix: "><script>alert('xss')</script> or '></SCRIPT>">'><SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>
        $varValue = preg_replace('/(?<!\w)(?>["|\']>)+(<[^\/^>]+>.*)/', '$1', $varValue);

        $varValue = Input::xssClean($varValue, $strictMode);

        return $varValue;
    }

    /**
     * Tidy an value.
     *
     * @param string $varValue       Input value
     * @param string $allowedTags    Allowed tags as string `<p><span>`
     * @param bool   $decodeEntities If true, all entities will be decoded
     *
     * @return string The tidied string
     */
    public function tidy($varValue, string $allowedTags = '', bool $decodeEntities = false): string
    {
        if (!$varValue) {
            return $varValue;
        }

        // do not tidy non-xss critical characters for performance
        if (!preg_match('#"|\'|<|>|\(|\)#', StringUtil::decodeEntities($varValue))) {
            return $varValue;
        }

        // remove illegal white spaces after closing tag slash <br / >
        $varValue = preg_replace('@\/(\s+)>@', '/>', $varValue);

        // Encode opening tag arrow brackets
        $varValue = preg_replace_callback('/<(?(?=!--)!--[\s\S]*--|(?(?=\?)\?[\s\S]*\?|(?(?=\/)\/[^.\-\d][^\/\]\'"[!#$%&()*+,;<=>?@^`{|}~ ]*|[^.\-\d][^\/\]\'"[!#$%&()*+,;<=>?@^`{|}~ ]*(?:\s[^.\-\d][^\/\]\'"[!#$%&()*+,;<=>?@^`{|}~ ]*(?:=(?:"[^"]*"|\'[^\']*\'|[^\'"<\s]*))?)*)\s?\/?))>/', function ($matches) {
            return substr_replace($matches[0], '&lt;', 0, 1);
        }, $varValue);

        // Encode less than signs that are no tags with [lt]
        $varValue = str_replace('<', '[lt]', $varValue);

        // After we saved less than signs with [lt] revert &lt; sign to <
        $varValue = StringUtil::decodeEntities($varValue);

        // Restore HTML comments
        $varValue = str_replace(['&lt;!--', '&lt;!['], ['<!--', '<!['], $varValue);

        // Recheck for encoded null bytes
        while (false !== strpos($varValue, '\\0')) {
            $varValue = str_replace('\\0', '', $varValue);
        }

        $objCrawler = new HtmlPageCrawler($varValue);

        if (!$objCrawler->isHtmlDocument()) {
            $objCrawler = new HtmlPageCrawler('<div id="tidyWrapperx123x123xawec3">'.$varValue.'</div>');
        }

        $arrAllowedTags = explode('<', str_replace('>', '', $allowedTags));
        $arrAllowedTags = array_filter($arrAllowedTags);

        try {
            if (!empty($arrAllowedTags)) {
                $objCrawler->filter('*')->each(function ($node, $i) use ($arrAllowedTags) {
                    /** @var $node HtmlPageCrawler */

                    // skip wrapper
                    if ('tidyWrapperx123x123xawec3' === $node->getAttribute('id')) {
                        return $node;
                    }

                    if (!\in_array($node->getNode(0)->tagName, $arrAllowedTags, true)) {
                        $strHTML = $node->saveHTML();
                        $strHTML = str_replace(['<', '>'], ['[[xlt]]', '[[xgt]]'], $strHTML);

                        // remove unwanted tags and return the element text
                        return $node->replaceWith($strHTML);
                    }

                    return $node;
                });
            }
            // unwrap div#tidyWrapper and set value to its innerHTML
            if (!$objCrawler->isHtmlDocument()) {
                $varValue = $objCrawler->filter('div#tidyWrapperx123x123xawec3')->getInnerHtml();
            } else {
                $varValue = $objCrawler->saveHTML();
            }

            // HTML documents or fragments, Crawler first converts all non-ASCII characters to entities (see: https://github.com/wasinger/htmlpagedom/issues/5)
            $varValue = StringUtil::decodeEntities($varValue);

            // trim last [nbsp] occurance
            $varValue = preg_replace('@(\[nbsp\])+@', '', $varValue);
        } catch (SyntaxErrorException $e) {
        }

        $varValue = $this->restoreBasicEntities($varValue, $decodeEntities);

        if (!$decodeEntities) {
            $varValue = Input::encodeSpecialChars($varValue);
        }

        // encode unwanted tag opening and closing brakets
        $arrSearch = ['[[xlt]]', '[[xgt]]'];
        $arrReplace = ['&#60;', '&#62;'];
        $varValue = str_replace($arrSearch, $arrReplace, $varValue);

        return $varValue;
    }

    /**
     * Restore basic entities.
     *
     * @param string $buffer         The string with the tags to be replaced
     * @param bool   $decodeEntities If true, all entities will be decoded
     *
     * @return string The string with the original entities
     */
    public function restoreBasicEntities(string $buffer, bool $decodeEntities = false): string
    {
        $buffer = str_replace(['[&]', '[&amp;]', '[lt]', '[gt]', '[nbsp]', '[-]'], ['&amp;', '&amp;', '&lt;', '&gt;', '&nbsp;', '&shy;'], $buffer);

        if ($decodeEntities) {
            $buffer = StringUtil::decodeEntities($buffer);
        }

        return $buffer;
    }

    /**
     * Returns true if the post parameter is defined.
     *
     * @param string $key The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function hasPost(string $key): bool
    {
        return $this->request->has($key);
    }

    /**
     * @param $request
     *
     * @return array
     */
    protected function checkCurrentRequest($request)
    {
        if (null === $request || !$request instanceof ParameterBag) {
            return [];
        }

        $parameters = $request->all();

        foreach ($parameters as $i => $parameter) {
            if (null === $parameter) {
                unset($parameters[$i]);
            }
        }

        return $parameters;
    }
}
