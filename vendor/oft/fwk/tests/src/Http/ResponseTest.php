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

namespace Oft\Test\Http;

use Oft\Http\Response;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Response
     */
    protected $response;

    public function setUp()
    {
        $response = new SymfonyResponse();

        $this->response = new Response($response);
    }

    public function testGetResponseObject()
    {
        $this->assertInstanceOf('\Oft\Http\Response', $this->response);
    }

    public function testSetStatusCode()
    {
        $code = 200;

        $this->response->setStatusCode($code, 'OK');

        $statusCode = $this->response->getStatusCode();

        $this->assertEquals($statusCode, $code);
    }

    public function testSetContentType()
    {
        $type = 'HTML';

        $this->response->setContentType($type);

        $result = $this->response->getContentType();

        $this->assertEquals($type, $result);
    }

    public function testSetCookie()
    {
        $this->response->deleteCookie('test');

        $cookies = $this->response->getResponseObject()->headers->getCookies();

        $this->assertEmpty($cookies);

        $this->response->setCookie('test', 'value', 12, '/test', 'domain', true, false);

        $cookies = $this->response->getResponseObject()->headers->getCookies();

        $this->assertNotEmpty($cookies);

        /** @var $cookie \Symfony\Component\HttpFoundation\Cookie */
        $cookie = $cookies[0];

        $this->assertEquals('test', $cookie->getName());
        $this->assertEquals('value', $cookie->getValue());
        $this->assertEquals('/test', $cookie->getPath());
        $this->assertEquals(12, $cookie->getExpiresTime());
        $this->assertEquals('domain', $cookie->getDomain());
        $this->assertEquals(true, $cookie->isSecure());
        $this->assertEquals(false, $cookie->isHttpOnly());

        $this->response->deleteCookie('test', '/test', 'domain');

        $cookies = $this->response->getResponseObject()->headers->getCookies();

        $this->assertEmpty($cookies);
    }

    public function testContent()
    {
        $content = $this->response->getContent();

        $this->assertEquals('', $content);

        $this->response->setContent('test');

        $content = $this->response->getContent();

        $this->assertEquals('test', $content);

        $this->response->addContent('value');

        $content = $this->response->getContent();

        $this->assertEquals('testvalue', $content);
    }

    public function testSetHeader()
    {
        $this->response->setHeader('key', 'test');
        $this->response->setHeader('key', 'test1');

        $result = $this->response->getResponseObject()->headers->get('key');

        $this->assertEquals('test1', $result);
    }

    public function testSend()
    {
        $this->response->setContent('test');
        $this->response->send();

        $this->expectOutputString('test');
    }
}
 