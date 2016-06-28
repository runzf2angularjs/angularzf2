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

class BasePathTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Oft\View\Helper\BasePath */
    protected $basePath;

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
        
        $this->basePath = new \Oft\View\Helper\BasePath();
        $this->basePath->setView($view);
    }
    
    public function testInvokeWithEmptyUrl()
    {
        $this->app->http->request->shouldReceive('getBasePath')
            ->once()
            ->withNoArgs()
            ->andReturn('path/to');
        
        $result = $this->basePath->__invoke();

        $this->assertSame('path/to', $result);
    }

    public function testInvokeWithEmptyUrlAndEmptyBasePath()
    {
        $this->app->http->request->shouldReceive('getBasePath')
            ->once()
            ->withNoArgs()
            ->andReturn('');

        $result = $this->basePath->__invoke();

        $this->assertSame('', $result);
    }

    public function testInvokeWithUrl()
    {
        $this->app->http->request->shouldReceive('getBasePath')
            ->once()
            ->withNoArgs()
            ->andReturn('path/to');

        $result = $this->basePath->__invoke('yop');

        $this->assertSame('path/to/yop', $result);
    }

    public function testInvokeWithUrlBeginingWithASlash()
    {
        $this->app->http->request->shouldReceive('getBasePath')
            ->once()
            ->withNoArgs()
            ->andReturn('path/to');

        $result = $this->basePath->__invoke('/yeah');

        $this->assertSame('path/to/yeah', $result);
    }

    public function testInvokeWithUrlAndEmptyBasePath()
    {
        $this->app->http->request->shouldReceive('getBasePath')
            ->once()
            ->withNoArgs()
            ->andReturn('');

        $result = $this->basePath->__invoke('yop');

        $this->assertSame('/yop', $result);
    }
}
