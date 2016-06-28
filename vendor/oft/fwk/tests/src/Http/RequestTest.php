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

use Oft\Http\Request;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class RequestTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Request
     */
    protected $request;

    public function setUp()
    {
        $request = SymfonyRequest::createFromGlobals();

        $this->request = new Request($request);
    }

    public function testConstruct()
    {
        $result = $this->request->getRequestObject();

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $result);
    }

    protected function mockBaseUrl($baseUrl)
    {
        $request = \Mockery::mock('\Symfony\Component\HttpFoundation\Request');
        $request->shouldReceive('getBaseUrl')
            ->once()
            ->withNoArgs()
            ->andReturn($baseUrl);

        $this->request = new Request($request);
    }

    public function testGetBaseUrlEmpty()
    {
        $this->mockBaseUrl('');

        $url = $this->request->getBaseUrl();

        $this->assertEquals('', $url);
    }

    public function testGetBaseUrl()
    {
        $this->mockBaseUrl('/test');

        $url = $this->request->getBaseUrl();

        $this->assertEquals('/test', $url);
    }
    
    protected function mockBasePath($baseUrl)
    {
        $request = \Mockery::mock('\Symfony\Component\HttpFoundation\Request');
        $request->shouldReceive('getBasePath')
            ->once()
            ->withNoArgs()
            ->andReturn($baseUrl);

        $this->request = new Request($request);
    }
    
    public function testGetBasePathEmpty()
    {
        $this->mockBasePath('');

        $url = $this->request->getBasePath();

        $this->assertEquals('', $url);
    }
    
    public function testGetBasePath()
    {
        $this->mockBasePath('/test');

        $url = $this->request->getBasePath();

        $this->assertEquals('/test', $url);
    }

    public function testGetPathInfo()
    {
        $request = \Mockery::mock('\Symfony\Component\HttpFoundation\Request');
        $request->shouldReceive('getPathInfo')
            ->once()
            ->withNoArgs()
            ->andReturn('/test');

        $this->request = new Request($request);

        $result = $this->request->getPathInfo();

        $this->assertEquals('/test', $result);
    }

    public function testGetFromServer()
    {
        $this->request->getRequestObject()->server->set('test', 'value');

        $result = $this->request->getFromServer('test');

        $this->assertEquals('value', $result);

        $this->request->getRequestObject()->server->set('test2', 'value2');

        $result = $this->request->getFromServer();

        $this->assertArrayHasKey('test', $result);
        $this->assertArrayHasKey('test2', $result);

        $result = $this->request->getFromServer('test3', 'test2');

        $this->assertEquals('test2', $result);
    }

    public function testGetFromQuery()
    {
        $this->request->getRequestObject()->query->set('test', 'value');

        $result = $this->request->getFromQuery('test');

        $this->assertEquals('value', $result);

        $this->request->getRequestObject()->query->set('test2', 'value2');

        $result = $this->request->getFromQuery();

        $this->assertArrayHasKey('test', $result);
        $this->assertArrayHasKey('test2', $result);

        $result = $this->request->getFromQuery('test3', 'test2');

        $this->assertEquals('test2', $result);
    }

    public function testGetFromPost()
    {
        $this->request->getRequestObject()->request->set('test', 'value');

        $result = $this->request->getFromPost('test');

        $this->assertEquals('value', $result);

        $this->request->getRequestObject()->request->set('test2', 'value2');

        $result = $this->request->getFromPost();

        $this->assertArrayHasKey('test', $result);
        $this->assertArrayHasKey('test2', $result);

        $result = $this->request->getFromPost('test3', 'test2');

        $this->assertEquals('test2', $result);
    }

    public function testGetFromCookies()
    {
        $this->request->getRequestObject()->cookies->set('test', 'value');

        $result = $this->request->getFromCookies('test');

        $this->assertEquals('value', $result);

        $this->request->getRequestObject()->cookies->set('test2', 'value2');

        $result = $this->request->getFromCookies();

        $this->assertArrayHasKey('test', $result);
        $this->assertArrayHasKey('test2', $result);

        $result = $this->request->getFromCookies('test3', 'test2');

        $this->assertEquals('test2', $result);
    }

    public function testGetFromHeaders()
    {
        $this->request->getRequestObject()->headers->set('test', 'value');

        $result = $this->request->getFromHeaders('test');

        $this->assertEquals('value', $result);

        $this->request->getRequestObject()->headers->set('test2', 'value2');

        $result = $this->request->getFromHeaders();

        $this->assertArrayHasKey('test', $result);
        $this->assertArrayHasKey('test2', $result);

        $result = $this->request->getFromHeaders('test3', 'test2');

        $this->assertEquals('test2', $result);
    }

    public function testGetHttpMethod()
    {
        $method = $this->request->getHttpMethod();

        $this->assertEquals('GET', $method);

        $this->request->getRequestObject()->setMethod('POST');

        $method = $this->request->getHttpMethod();

        $this->assertEquals('POST', $method);
    }

    public function testGetRequestUri()
    {
        $this->request->getRequestObject()->headers->set('X_ORIGINAL_URL', 'test');

        $uri = $this->request->getRequestUri();

        $this->assertEquals('test', $uri);
    }

    public function testIsPost()
    {
        $this->assertFalse($this->request->isPost());

        $this->request->getRequestObject()->setMethod('POST');

        $this->assertTrue($this->request->isPost());
    }

    public function testIsMethod()
    {
        $this->assertTrue($this->request->isMethod('GET'));
        $this->assertFalse($this->request->isMethod('POST'));
    }

    public function testIsHttps()
    {
        $this->assertFalse($this->request->isHttps());

        $this->request->getRequestObject()->server->set('HTTPS', true);

        $this->assertTrue($this->request->isHttps());
    }

    public function testIsXmlHttpRequest()
    {
        $this->assertFalse($this->request->isXmlHttpRequest());

        $this->request->getRequestObject()->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->assertTrue($this->request->isXmlHttpRequest());
    }
}
 