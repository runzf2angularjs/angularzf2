<?php
/**
 * Copyright (C) 2015 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * If you are Orange employee you shall use this software in accordance with
 * the Orange Source Charter (http://opensource.itn.ftgroup/index.php/Orange_Source).
 */

namespace Oft\Test\functions;

use Oft\Mvc\Application;
use Oft\Util\Functions;
use PHPUnit_Framework_TestCase;

class EscaperTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {        
        $app = new Application(array(
            'escaper' => array(
                'encoding' => 'UTF-8',
            ),
        ));

        Functions::setApp($app);
    }
    
    protected function tearDown()
    {
        Functions::setApp(null);
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage La méthode d'échappement associée au type "ko" n'existe pas
     */
    public function testException()
    {
        e('test', 'ko');
    }

    public function testHtml()
    {
        $escaped = eHtml('<div>');
        $expected = '&lt;div&gt;';

        $this->assertEquals($expected, $escaped);
    }

    public function testXml()
    {
        $escaped = eXml('<tag>&');
        $expected = '&lt;tag&gt;&amp;';

        $this->assertEquals($expected, $escaped);
    }

    public function testHtmlAttr()
    {
        $escaped = eHtmlAttr('title onmouseover=alert(/ThisIsATrap()/);');
        $expected = 'title&#x20;onmouseover&#x3D;alert&#x28;&#x2F;ThisIsATrap&#x28;&#x29;&#x2F;&#x29;&#x3B;';

        $this->assertEquals($expected, $escaped);
    }

    public function testJs()
    {
        $escaped = eJs("alert(&quot;Meow!&quot;);");
        $expected = 'alert\x28\x26quot\x3BMeow\x21\x26quot\x3B\x29\x3B';

        $this->assertEquals($expected, $escaped);
    }

    public function testCss()
    {
        $escaped = eCss("background-image: url('http://example.com/foo.jpg?</style><script>alert(1)</script>');");
        $expected = 'background\2D image\3A \20 url\28 \27 http\3A \2F \2F example\2E com\2F foo\2E jpg\3F \3C \2F style\3E \3C script\3E alert\28 1\29 \3C \2F script\3E \27 \29 \3B ';

        $this->assertEquals($expected, $escaped);
    }

    public function testUrl()
    {
        $escaped = eUrl("\" onmouseover=\"alert('zf2')");
        $expected = '%22%20onmouseover%3D%22alert%28%27zf2%27%29';

        $this->assertEquals($expected, $escaped);
    }

}
