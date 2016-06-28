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

namespace Oft\Test\Mvc\Middleware;

class RouteTest extends \PHPUnit_Framework_TestCase
{

    public function testCall()
    {
        $app = new \Oft\Mvc\Application(array('maxForward' => 10));

        $app->setService('Http', new \Oft\Test\Mock\HttpContext());
        $app->http->request->shouldReceive('getPathInfo')
            ->once()
            ->withNoArgs()
            ->andReturn('/');

        $route = new \stdClass;
        $route->name = 'n';
        $route->params = array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a',
            'p' => 'v'
        );
        
        $router = \Mockery::mock('\Aura\Router\Router');
        $router->shouldReceive('match')
            ->andReturn($route);
        
        $app->setService('Router', $router);
        $app->setService('Route', new \Oft\Mvc\Context\RouteContext());
        
        $nextMiddleware = \Mockery::mock('\Oft\Mvc\MiddlewareAbstract');
        $nextMiddleware->shouldReceive('call')
            ->once()
            ->with($app);
        
        $render = new \Oft\Mvc\Middleware\Route();
        $render->setNextMiddleware($nextMiddleware);
        
        $render->call($app);
        
        $currentRoute = $app->route->current;
        $this->assertSame('m', $currentRoute['module']);
        $this->assertSame('c', $currentRoute['controller']);
        $this->assertSame('a', $currentRoute['action']);
        
        $currentParams = $app->route->params;
        $this->assertArrayHasKey('p', $currentParams);
        $this->assertSame('v', $currentParams['p']);
    }
}
