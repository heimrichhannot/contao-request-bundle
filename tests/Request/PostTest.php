<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RequestBundle\Test\Request;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\RequestBundle\Request;

class PostTest extends ContaoTestCase
{
    /**
     * @var Request
     */
    public $request;

    public function setUp()
    {
        parent::setUp();

        if (!defined('TL_MODE')) {
            define('TL_MODE', 'FE');
        }

        $this->request = new Request($this->mockContaoFramework());
        // reset request parameter bag
        $this->request->set(new \Symfony\Component\HttpFoundation\Request());
    }

    public function testPostHtmlPage()
    {
        $strHtml = '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>Title of document</title></head><body>some content</body></html>';

        $this->request->setPost('test', $strHtml);

        $strActual = $this->request->getPost('test', true);

        $strExpected = '&#60;!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"&#62;
&#60;html xmlns&#61;"http://www.w3.org/1999/xhtml"&#62;&#60;head&#62;&#60;meta http-equiv&#61;"Content-Type" content&#61;"text/html; charset&#61;UTF-8"&#62;&#60;title&#62;Title of document&#60;/title&#62;&#60;/head&#62;&#60;body&#62;some content&#60;/body&#62;&#60;/html&#62;
';
        $this->assertSame($strExpected, $strActual);
    }

    /**
     * Binary uuid should returned as binary.
     *
     * @test
     */
    public function testPostUuidArray()
    {
        $uuid1 = \Contao\StringUtil::uuidToBin('9c6697cf-c874-11e7-8bb3-a08cfddc0261');
        $uuid2 = \Contao\StringUtil::uuidToBin('b8b30475-c4a9-11e7-a954-a08cfddc0261');
        $uuid3 = \Contao\StringUtil::uuidToBin('3523ce67-e0ed-11e7-bc3c-a08cfddc0261');

        $arrUuids = [
            $uuid1,
            $uuid2,
            $uuid3,
        ];

        $this->request->setPost('test', $arrUuids);

        $this->assertSame($arrUuids, $this->request->getPost('test'));
        $this->assertSame($arrUuids, $this->request->getPostHtml('test'));
        $this->assertSame($arrUuids, $this->request->getPostRaw('test'));
    }

    /**
     * Binary uuid should returned as binary.
     *
     * @test
     */
    public function testPostUuidValue()
    {
        $uuid = \Contao\StringUtil::uuidToBin('9c6697cf-c874-11e7-8bb3-a08cfddc0261');

        $this->request->setPost('test', $uuid);

        $this->assertSame($uuid, $this->request->getPost('test'));
        $this->assertSame($uuid, $this->request->getPostHtml('test'));
        $this->assertSame($uuid, $this->request->getPostRaw('test'));
    }

    /**
     * @test
     */
    public function testPost()
    {
        $strInput = '<p>foo <5 hier steht viel Text<span><b>Test <a href="http://example.org" onclick="alert(\'xss\')">Link</a></b></span></p><span> FOOBAR</span>';
        $strExpected = '&#60;p&#62;foo &lt;5 hier steht viel Text&#60;span&#62;&#60;b&#62;Test Link&#60;/b&#62;&#60;/span&#62;&#60;/p&#62;&#60;span&#62; FOOBAR&#60;/span&#62;';

        $this->request->setPost('test', $strInput);

        $this->assertSame($strExpected, $this->request->getPost('test'));
    }

    /**
     * @test
     */
    public function testHtmlPostWithDecodeEntities()
    {
        $strInput = '<p>foo <5 hier steht viel Text<span><b>Test <a href="http://example.org" onclick="alert(\'xss\')">Link</a></b></span></p><span> FOOBAR</span>';
        $strExpected = '<p>foo <5 hier steht viel Text<span><b>Test Link</b></span></p><span> FOOBAR</span>';

        $this->request->setPost('test', $strInput);

        $this->assertSame($strExpected, $this->request->getPostHtml('test', true));
    }

    /**
     * @test
     */
    public function testHtmlPostWithAllowedTagsAndDecodeEntities()
    {
        $strInput = '<p>foo <5 hier steht viel Text<span><b>Test <a href="http://example.org" onclick="alert(\'xss\')">Link</a></b></span></p><span> FOOBAR</span>';
        $strExpected = '<p>foo <5 hier steht viel Text<span>&#60;b&#62;Test Link&#60;/b&#62;</span></p><span> FOOBAR</span>';

        $this->request->setPost('test', $strInput);

        $this->assertSame($strExpected, $this->request->getPostHtml('test', true, '<p><span>'));
    }

    /**
     * @dataProvider tidyDataProvider
     * @test
     */
    public function testTidy($strText, $strAllowedTags, $blnDecodeEntities, $strExpected)
    {
        $this->assertSame($strExpected, $this->request->tidy($strText, $strAllowedTags, $blnDecodeEntities));
    }

    /**
     * @dataProvider xssDataProvider
     * @test
     */
    public function testXSSPost($strText, $strExpected, $strExpectedRaw, $strExpectedHtml)
    {
        $this->request->setPost('test', $strText);

        $this->assertSame($strExpected, $this->request->getPost('test'));
        $this->assertSame($strExpectedRaw, $this->request->getPostRaw('test'));
        $this->assertSame($strExpectedHtml, $this->request->getPostHtml('test', false, '<p>'));
    }

    public function tidyDataProvider()
    {
        // [0] => input
        // [1] => strAllowedTas <p><span>
        // [2] => expected tidied value
        $arrList = [
            [
                'john.doe@example.org',
                '',
                true,
                'john.doe@example.org',
            ],
            [
                '<p>foo <5 hier >=6 steht viel Text<span src="javascript:alert(\'XSS\')"><b>Test <a href="http://example.org" onclick="alert(\'xss\')">Link</a></b></span></p>',
                '<p><span>',
                true,
                '<p>foo <5 hier >=6 steht viel Text<span src="javascript:alert(\'XSS\')">&#60;b&#62;Test &#60;a href="http://example.org" onclick="alert(\'xss\')"&#62;Link&#60;/a&#62;&#60;/b&#62;</span></p>',
            ],
            [
                '<audio>foo <5 hier steht viel Text</audio>',
                '',
                true,
                '<audio>foo <5 hier steht viel Text</audio>',
            ],
            [
                '<audio>foo <5 hier steht viel Text</audio>',
                '',
                false,
                '&#60;audio&#62;foo &lt;5 hier steht viel Text&#60;/audio&#62;',
            ],
            [
                '<md_text>foo <5 hier steht viel Text</md_text>',
                '',
                true,
                '<md_text>foo <5 hier steht viel Text</md_text>',
            ],
            [
                '<p>foo <5 hier steht viel Text<span src="javascript:alert(\'XSS\')"><b>Test <a href="http://example.org" onclick="alert(\'xss\')">Link</a></b></span></p>',
                '<p>',
                true,
                '<p>foo <5 hier steht viel Text&#60;span src="javascript:alert(\'XSS\')"&#62;&#60;b&#62;Test &#60;a href="http://example.org" onclick="alert(\'xss\')"&#62;Link&#60;/a&#62;&#60;/b&#62;&#60;/span&#62;</p>',
            ],
            [
                '<strong>bar</strong> ist <5 und < 10 und << 20.',
                '<strong>',
                true,
                '<strong>bar</strong> ist <5 und < 10 und << 20.',
            ],
            [
                '<p>hier test <5</p>',
                '<p>',
                true,
                '<p>hier test <5</p>',
            ],
            [
                '<br /> <br> <br/> <br/ >',
                '<br>',
                true,
                '<br><br><br><br>',
            ],
            [
                '<p title="test> 5"> <p title="test > 5"> <p title="test <p> 5">',
                '<p>',
                true,
                '<p title="test> 5"> </p><p title="test > 5"> </p><p title="test <p> 5"></p>',
            ],
            [
                'Arthur Friend <a.friend@email.com> Arthur Friend <a friend@email.com>',
                '<a>',
                true,
                'Arthur Friend <a.friend@email.com> Arthur Friend <a friend@email.com>',
            ],
            [
                'bla << bla <<5',
                '',
                true,
                'bla << bla <<5',
            ],
            [
                '<!-- comment --> <![CDATA ]>',
                '',
                true,
                '<!-- comment --> <![CDATA ]>',
            ],
        ];

        return $arrList;
    }

    public function xssDataProvider()
    {
        // [0] => input
        // [1] => expected post value (safe, filtered)
        // [2] => expected raw value (safe, unfiltered)
        // [3] => expected html value (safe, filtered but contains allowed html)

        $arrList = [
            [
                '<script>alert(\'xss\')</script>',
                '&#60;script&#62;alert&#40;\'xss\'&#41;&#60;/script&#62;',
                '<script>alert(\'xss\')</script>',
                '&#60;script&#62;alert&#40;\'xss\'&#41;&#60;/script&#62;',
            ],
            [
                '<script>alert(\'xss\')</script><p>Hello</p>',
                '&#60;script&#62;alert&#40;\'xss\'&#41;&#60;/script&#62;&#60;p&#62;Hello&#60;/p&#62;',
                '<script>alert(\'xss\')</script><p>Hello</p>',
                '&#60;script&#62;alert&#40;\'xss\'&#41;&#60;/script&#62;&#60;p&#62;Hello&#60;/p&#62;',
            ],
            [
                '"><script>alert(\'xss\')</script>',
                '&#60;script&#62;alert&#40;\'xss\'&#41;&#60;/script&#62;',
                '<script>alert(\'xss\')</script>',
                '&#60;script&#62;alert&#40;\'xss\'&#41;&#60;/script&#62;',
            ],
            [
                '%253Cscript%253Ealert(\'XSS\')%253C%252Fscript%253E',
                '%253Cscript%253Ealert&#40;\'XSS\'&#41;%253C%252Fscript%253E',
                '%253Cscript%253Ealert(\'XSS\')%253C%252Fscript%253E',
                '%253Cscript%253Ealert&#40;\'XSS\'&#41;%253C%252Fscript%253E',
            ],
            [
                '<IFRAME SRC="javascript:alert(\'XSS\');"></IFRAME>',
                '&#60;iframe src&#61;"alert&#40;\'XSS\'&#41;;"&#62;&#60;/iframe&#62;',
                '<IFRAME SRC="javascript:alert(\'XSS\');"></IFRAME>',
                '&#60;iframe src&#61;"alert&#40;\'XSS\'&#41;;"&#62;&#60;/iframe&#62;',
            ],
            [
                '"<IFRAME SRC="javascript:alert(\'XSS\');"></IFRAME>',
                '"&#60;iframe src&#61;"alert&#40;\'XSS\'&#41;;"&#62;&#60;/iframe&#62;',
                '"<IFRAME SRC="javascript:alert(\'XSS\');"></IFRAME>',
                '"&#60;iframe src&#61;"alert&#40;\'XSS\'&#41;;"&#62;&#60;/iframe&#62;',
            ],
            ['<BODY ONLOAD=alert(\'XSS\')>', '', '<BODY ONLOAD=alert(\'XSS\')>', ''],
            ['"<BODY ONLOAD=alert(\'XSS\')>', '"', '"<BODY ONLOAD=alert(\'XSS\')>', '"'],
            [
                '<<SCRIPT>alert("XSS");//<</SCRIPT>',
                'alert&#40;"XSS"&#41;;//&lt;',
                '<<SCRIPT>alert("XSS");//<</SCRIPT>',
                'alert&#40;"XSS"&#41;;//&lt;',
            ],
            [
                '<IMG SRC="javascript:alert(\'XSS\')"',
                '&lt;IMG SRC&#61;"alert&#40;\'XSS\'&#41;"',
                '<IMG SRC="javascript:alert(\'XSS\')"',
                '&lt;IMG SRC&#61;"alert&#40;\'XSS\'&#41;"',
            ],
            [
                '\';alert(String.fromCharCode(88,83,83))//\\\';alert(String.fromCharCode(88,83,83))//";alert(String.fromCharCode(88,83,83))//\";alert(String.fromCharCode(88,83,83))//',
                '\';alert&#40;String.fromCharCode&#40;88,83,83&#41;&#41;//&#92;\';alert&#40;String.fromCharCode&#40;88,83,83&#41;&#41;//";alert&#40;String.fromCharCode&#40;88,83,83&#41;&#41;//&#92;";alert&#40;String.fromCharCode&#40;88,83,83&#41;&#41;//',
                '\';alert(String.fromCharCode(88,83,83))//\\\';alert(String.fromCharCode(88,83,83))//";alert(String.fromCharCode(88,83,83))//\";alert(String.fromCharCode(88,83,83))//',
                '\';alert&#40;String.fromCharCode&#40;88,83,83&#41;&#41;//&#92;\';alert&#40;String.fromCharCode&#40;88,83,83&#41;&#41;//";alert&#40;String.fromCharCode&#40;88,83,83&#41;&#41;//&#92;";alert&#40;String.fromCharCode&#40;88,83,83&#41;&#41;//',
            ],
            [
                '></SCRIPT>">\'><SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>',
                '&#62;&#60;script&#62;alert&#40;String.fromCharCode&#40;88,83,83&#41;&#41;&#60;/script&#62;',
                '></SCRIPT><SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>',
                '&#62;&#60;script&#62;alert&#40;String.fromCharCode&#40;88,83,83&#41;&#41;&#60;/script&#62;',
            ],
            [
                ',\';!—"<XSS>=&{()}',
                ',\';!—"&#60;xss&#62;&#61;&{&#40;&#41;}&#60;/xss&#62;',
                ',\';!—"<XSS>=&{()}',
                ',\';!—"&#60;xss&#62;&#61;&{&#40;&#41;}&#60;/xss&#62;',
            ],
            [
                '\'\';!—"<XSS>=&{()}',
                '\'\';!—"&#60;xss&#62;&#61;&{&#40;&#41;}&#60;/xss&#62;',
                '\'\';!—"<XSS>=&{()}',
                '\'\';!—"&#60;xss&#62;&#61;&{&#40;&#41;}&#60;/xss&#62;',
            ],
            [
                '<IMG SRC=&#0000106&#0000097&#0000118&#0000097&#0000115&#0000099&#0000114&#0000105&#0000112&#0000116&#0000058&#0000097&#0000108&#0000101&#0000114&#0000116&#0000040&#0000039&#0000088&#0000083&#0000083&#0000039&#0000041>',
                '',
                '<IMG SRC=javascript:alert(\'XSS\')>',
                '',
            ],
            ['"><%00script', '"&#62;&lt;%00script', '"><%00script', '"&#62;&lt;%00script'],
            [
                '"><%tag style="xss:expression(alert(123))">',
                '',
                '<%tag style="xss:expression(alert(123))">',
                '',
            ],
            [
                '">%uff1cscript%uff1ealert(\'XSS\');%uff1c/script%uff1e',
                '"&#62;%uff1cscript%uff1ealert&#40;\'XSS\'&#41;;%uff1c/script%uff1e',
                '">%uff1cscript%uff1ealert(\'XSS\');%uff1c/script%uff1e',
                '"&#62;%uff1cscript%uff1ealert&#40;\'XSS\'&#41;;%uff1c/script%uff1e',
            ],
            [
                '><DIV TYLE="background-image: url(javascript:alert(\'XSS\'))">',
                '&#62;&#60;div tyle&#61;"background-image: url&#40;alert&#40;\'XSS\'&#41;&#41;"&#62;&#60;/div&#62;',
                '><DIV TYLE="background-image: url(javascript:alert(\'XSS\'))">',
                '&#62;&#60;div tyle&#61;"background-image: url&#40;alert&#40;\'XSS\'&#41;&#41;"&#62;&#60;/div&#62;',
            ],
            [
                '"><%00 style="xss:expression(alert(123))">',
                '',
                '<%00 style="xss:expression(alert(123))">',
                '',
            ],
        ];

        return $arrList;
    }
}
