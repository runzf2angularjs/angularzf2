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

namespace Oft\Test\Mvc;

use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    public function testEmptyConstruct()
    {
        $app = new Application();

        $this->assertInstanceOf('Oft\Module\ModuleManager', $app->moduleManager);
    }

    public function testConstructWithModuleManager()
    {
        $moduleManager = new \Oft\Module\ModuleManager;
        $app = new Application(array(), $moduleManager);

        $this->assertInstanceOf('Oft\Module\ModuleManager', $app->moduleManager);
        $this->assertSame($app->moduleManager, $moduleManager);
    }

    public function testSetMiddlewares()
    {
        $config = array();
        $middleware2 = \Mockery::mock('\Oft\Mvc\MiddlewareAbstract');
        $middleware = \Mockery::mock('\Oft\Mvc\MiddlewareAbstract');
        $middleware->shouldReceive('setNextMiddleware')
            ->once()
            ->with($middleware2);

        $config['middlewares'] = array($middleware, $middleware2);

        $app = new \Oft\Mvc\Application($config);

        $middlewares = $app->middlewares;
        $this->assertArrayHasKey(0, $middlewares);
        $this->assertSame($middleware, $middlewares[0]);
        $this->assertArrayHasKey(1, $middlewares);
        $this->assertSame($middleware2, $middlewares[1]);
    }

    public function testInitMiddlewares()
    {
        $middleware3 = '\Oft\Test\Mock\Middleware';

        $middleware2 = \Mockery::mock('\Oft\Mvc\MiddlewareAbstract');
        $middleware2->shouldReceive('setNextMiddleware')
            ->with($middleware3)
            ->once();

        $middleware = \Mockery::mock('\Oft\Mvc\MiddlewareAbstract');
        $middleware->shouldReceive('setNextMiddleware')
            ->with($middleware2)
            ->once();

        $config = array(
            'middlewares' => array(
                0 => $middleware,
                'someKey' => $middleware2,
                3 => $middleware3
            )
        );

        $app = new \Oft\Mvc\Application($config);

        $middlewares = $app->middlewares;
        $this->assertArrayHasKey(0, $middlewares);
        $this->assertFalse(is_string($middlewares[2]));
        $this->assertInstanceOf('\Oft\Test\Mock\Middleware', $middlewares[2]);
    }

    public function testGetConfig()
    {
        $app = new Application(array(
            'test' => 'value'
        ));

        $this->assertSame(array('test' => 'value'), $app->config);
    }

    public function testGetModuleManager()
    {
        $config = array(
            'defaultModule' => 'Oft\Test\Mock\Module',
            'modules' => array(
                'Oft\Test\Mock\Module2'
            ),
        );

        $app = new \Oft\Mvc\Application($config);

        $this->assertInstanceOf('Oft\Module\ModuleManager', $app->moduleManager);
        $this->assertArrayHasKey('oft-test', $app->moduleManager->getModules());
        $this->assertArrayHasKey('oft-test2', $app->moduleManager->getModules());
    }

    public function testMagicGetReturnAttributeIfNotNull()
    {
        $app = new Application();

        $this->assertInstanceOf('Oft\Module\ModuleManager', $app->moduleManager);
    }

    public function testMagicGetUseServiceLocatorIfNoAttributeSetAndInWhiteList()
    {
        $app = new Application();
        $app->setService('View', 'result');

        $this->assertSame($app->view, 'result');
    }

    public function testMagicGetThrowExceptionIfNoAttributeSetAndNotInWhiteList()
    {
        $this->setExpectedException('RuntimeException');
        
        $app = new Application();
        $app->setService('Dumb', 'result');

        $app->dumb;
    }

    public function testRun()
    {
        $middleware = \Mockery::mock('Oft\Mvc\MiddlewareAbstract');

        $config = array(
            'services' => array(
                'Log' => 'stdClass',
                'Translator' => 'stdClass',
            ),
            'debug' => true,
            'middlewares' => array($middleware),
        );

        $app = new Application($config);

        $middleware->shouldReceive('call')
            ->with($app)
            ->once();


        $app->setService('Http', new \Oft\Test\Mock\HttpContext());
        $app->setService('Log', \Mockery::mock('Oft\Service\Provider\Log'));
        $app->setService('Translator', \Mockery::mock('Oft\Service\Provider\Translator'));

        // Initialize HttpContext
        $app->http;

        $response = $app->run();

        $this->assertInstanceof('Oft\Http\ResponseInterface', $response);
    }

    public function testRunWithRedirect()
    {
        $middleware = \Mockery::mock('Oft\Mvc\MiddlewareAbstract');
        
        $app = new Application(array(
            'services' => array(
                'Log' => 'stdClass',
                'Translator' => 'stdClass',
            ),
            'debug' => true,
            'middlewares' => array($middleware)
        ));

        $response = \Mockery::mock('Oft\Http\ResponseInterface');
        $response->shouldReceive('setStatusCode')
            ->once()
            ->andReturn($response);
        $response->shouldReceive('setContent')
            ->never()
            ->andReturn($response);
        $response->shouldReceive('addHeaders')
            ->once();

        $mockHttpContext = new \Oft\Test\Mock\HttpContext();
        $mockHttpContext->setResponse($response);

        $app->setService('Http', $mockHttpContext);

        $middleware->shouldReceive('call')
            ->with($app)
            ->once()
            ->andThrow('Oft\Mvc\Exception\RedirectException');

        // Initialize HttpContext
        $app->http;
        
        $result = $app->run();

        $this->assertSame($result, $response);
    }

    public function testIsDebugReturnTrue()
    {
        $app = new Application(array('debug' => true));

        $this->assertTrue($app->isDebug);
    }

    public function testIsDebugReturnFalse()
    {
        $app = new Application(array('debug' => false));

        $this->assertFalse($app->isDebug);
    }

    public function testIsDebugReturnFalseByDefault()
    {
        $app = new Application();

        $this->assertFalse($app->isDebug);
    }

    public function testIsCliReturnTrue()
    {
        $app = new Application(array('cli' => true));

        $this->assertTrue($app->isCli);
    }

    public function testIsCliReturnFalse()
    {
        $app = new Application(array('cli' => false));

        $this->assertFalse($app->isCli);
    }

    public function testIsCliReturnFalseByDefault()
    {
        $app = new Application();

        $this->assertFalse($app->isCli);
    }
}
