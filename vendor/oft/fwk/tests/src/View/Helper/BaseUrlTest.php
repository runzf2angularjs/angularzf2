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

namespace Oft\Test\View\Helper;

class BaseUrlTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Oft\View\Helper\BaseUrl */
    protected $baseUrl;

    /** @var \Oft\Mvc\Application */
    protected $app;

    public function setUp()
    {
        $http = new \stdClass();
        $http->request = \Mockery::mock('Oft\Mvc\Http\RequestInterface');

        $this->app = new \Oft\Mvc\Application();
        $this->app->setService('Http', $http);

        $view = new \Oft\View\View();
        $view->setApplication($this->app);
        
        $this->baseUrl = new \Oft\View\Helper\BaseUrl();
        $this->baseUrl->setView($view);
    }
    
    public function testInvokeWithEmptyUrl()
    {
        $this->app->http->request->shouldReceive('getBaseUrl')
            ->once()
            ->withNoArgs()
            ->andReturn('path/to');
        
        $result = $this->baseUrl->__invoke();

        $this->assertSame('path/to', $result);
    }

    public function testInvokeWithEmptyUrlAndEmptyBaseUrl()
    {
        $this->app->http->request->shouldReceive('getBaseUrl')
            ->once()
            ->withNoArgs()
            ->andReturn('');

        $result = $this->baseUrl->__invoke();

        $this->assertSame('', $result);
    }

    public function testInvokeWithUrl()
    {
        $this->app->http->request->shouldReceive('getBaseUrl')
            ->once()
            ->withNoArgs()
            ->andReturn('path/to');

        $result = $this->baseUrl->__invoke('yop');

        $this->assertSame('path/to/yop', $result);
    }

    public function testInvokeWithUrlBeginingWithASlash()
    {
        $this->app->http->request->shouldReceive('getBaseUrl')
            ->once()
            ->withNoArgs()
            ->andReturn('path/to');

        $result = $this->baseUrl->__invoke('/yeah');

        $this->assertSame('path/to/yeah', $result);
    }

    public function testInvokeWithUrlAndEmptyBaseUrl()
    {
        $this->app->http->request->shouldReceive('getBaseUrl')
            ->once()
            ->withNoArgs()
            ->andReturn('');

        $result = $this->baseUrl->__invoke('yop');

        $this->assertSame('/yop', $result);
    }
}
