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

class SmartUrlFromRouteTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Oft\View\Helper\SmartUrlFromRoute
     */
    protected $url;

    /**
     * @var \Aura\Router\Router
     */
    protected $router;
    
    /**
     * @var \Oft\Mvc\Context
     */
    protected $application;

    protected function setUp()
    {
        $this->application = new \Oft\Mvc\Application();
        
        $view = new \Oft\View\View();
        $view->setApplication($this->application);

        $routerFactory = new \Aura\Router\RouterFactory();
        $this->router = $routerFactory->newInstance();
        $this->url = new \Oft\View\Helper\SmartUrlFromRoute();

        $this->url->setRouter($this->router);
        $this->url->setView($view);
    }

    public function testDefault()
    {
        $routes = $this->router->getRoutes();
        $routes->add('default', '/test/12');
        
        $this->assertSame('/test/12', $this->url->__invoke());
    }

    public function testNotDefault()
    {
        $routes = $this->router->getRoutes();
        $routes->add('notDefault', '/test/12');
        
        $this->assertSame('/test/12', $this->url->__invoke('notDefault'));
    }

    public function testNotDefaultWithParams()
    {
        $routes = $this->router->getRoutes();
        $routes->add('notDefault', '/test/{params}');
        
        $this->assertSame('/test/value', $this->url->__invoke('notDefault', array('params' => 'value')));
    }

    public function testDefaultWithParams()
    {
        $routes = $this->router->getRoutes();
        $routes->add('default', '/test/{params}');
        
        $this->assertSame('/test/value', $this->url->__invoke(null, array('params' => 'value')));
    }
    
    public function testGetRouterFromContainer()
    {
        $routes = $this->router->getRoutes();
        $routes->add('default', '/test');
        
        $this->application->setService('Router', $this->router);
        
        $view = new \Oft\View\View();
        $view->setApplication($this->application);
        
        $urlHelper = new \Oft\View\Helper\SmartUrlFromRoute();
        $urlHelper->setView($view);

        $url = $urlHelper->__invoke();

        $this->assertSame('/test', $url);
    }
}
