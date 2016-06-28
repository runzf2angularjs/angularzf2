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

namespace Oft\Test\Service\Provider;

class RouterTest extends \PHPUnit_Framework_TestCase
{

    protected function getApp($config)
    {
        $moduleManager = new \Oft\Module\ModuleManager();
        $moduleManager->addModule(new \Oft\Test\Mock\Module\Module(), true);
        $moduleManager->addModule(new \Oft\Test\Mock\Module2\Module());

        $app = new \Oft\Mvc\Application($config, $moduleManager);

        $routeContextFactory = new \Oft\Service\Provider\RouteContext();
        $routeContext = $routeContextFactory->create($app);

        $app->setService('Route', $routeContext);

        return $app;
    }

    public function testCreateService()
    {
        $config = array(
            'routeContext' => array(
                'default' => array(
                    'module' => 'app',
                    'controller' => 'index',
                    'action' => 'index',
                ),
                'notFound' => array(),
                'error' => array(),
            ),
            'routes' => array(),
        );

        $app = $this->getApp($config);

        $routerProvider = new \Oft\Service\Provider\Router();

        /* @var $router \Aura\Router\Router */
        $router = $routerProvider->doCreate($app);

        $this->assertInstanceOf('\Aura\Router\Router', $router);
        $routes = $router->getRoutes();
        $this->assertInstanceOf('\Aura\Router\RouteCollection', $routes);
        $this->assertSame(2, count($routes));
    }

    public function testCreateServiceWithRoutes()
    {
        $config = array(
            'routeContext' => array(
                'default' => array(
                    'module' => 'app',
                    'controller' => 'index',
                    'action' => 'index',
                ),
                'notFound' => array(),
                'error' => array(),
            ),
            'routes' => array(
                'r1' => array(
                    'path' => '/test/1'
                ),
                'r2' => array(
                    'path' => '/test/2',
                    'values' => array('p' => 'v'),
                ),
                'r3' => array(
                    'path' => '/test/3',
                    'tokens' => array(
                        't' => 'regex'
                    )
                ),
                'r4' => array(
                    'path' => '/test/4',
                    'method' => 'GET'
                ),
                'r5' => array(
                    'path' => '/test/5',
                    'method' => array('GET', 'POST')
                ),
            ),
        );

        $app = $this->getApp($config);

        $routerProvider = new \Oft\Service\Provider\Router();

        /* @var $router \Aura\Router\Router */
        $router = $routerProvider->doCreate($app);
        $routes = $router->getRoutes();

        $this->assertInstanceOf('\Aura\Router\Route', $routes['r1']);
        $this->assertSame('/test/1', $routes['r1']->path);
        $this->assertSame('r1', $routes['r1']->name);

        $this->assertSame('/test/2', $routes['r2']->path);
        $this->assertInternalType('array', $routes['r2']->values);
        $this->assertSame('v', $routes['r2']->values['p']);

        $this->assertSame('/test/3', $routes['r3']->path);
        $this->assertInternalType('array', $routes['r3']->values);
        $this->assertSame('regex', $routes['r3']->tokens['t']);

        $this->assertSame('/test/4', $routes['r4']->path);
        $this->assertInternalType('array', $routes['r4']->server);
        $this->assertArrayHasKey('REQUEST_METHOD', $routes['r4']->server);
        $this->assertSame('GET', $routes['r4']->server['REQUEST_METHOD']);

        $this->assertSame('/test/5', $routes['r5']->path);
        $this->assertInternalType('array', $routes['r5']->server);
        $this->assertArrayHasKey('REQUEST_METHOD', $routes['r5']->server);
        $this->assertSame('GET|POST', $routes['r5']->server['REQUEST_METHOD']);
    }

    public function testBug590InflectionBypassAcl()
    {
        $config = array(
            'routeContext' => array(
                'default' => array(
                    'module' => 'oft-test',
                    'controller' => 'index',
                    'action' => 'index',
                ),
                'notFound' => array(),
                'error' => array(),
            ),
            'routes' => array()
        );
        $app = $this->getApp($config);
        $routerProvider = new \Oft\Service\Provider\Router();
        $router = $routerProvider->doCreate($app);

        // Standard match
        $match = $router->match('/oft-test/some/controller');
        $this->assertNotFalse($match);
        $this->assertInternalType('array', $match->params);
        $this->assertArrayHasKey('module', $match->params);
        $this->assertArrayHasKey('controller', $match->params);
        $this->assertArrayHasKey('controller', $match->params);

        // Should Fail Match : bad module
        $match = $router->match('/oft-Test/some/controller');
        $this->assertFalse($match);

        // Should Fail Match : bad controller format
        $match = $router->match('/oft-test/Some/controller');
        $this->assertFalse($match);

        // Should Fail Match : bad action format
        $match = $router->match('/oft-test/some/Controller');
        $this->assertFalse($match);

        // Should NOT Fail
        $match = $router->match('/some/controller');
        $this->assertNotFalse($match);

        // Should Fail Match : bad action format
        $match = $router->match('/some/conTroller');
        $this->assertFalse($match);
        
        // Should Fail Match : bad controller format
        $match = $router->match('/soMe/controller');
        $this->assertFalse($match);
    }


    public function testMatchDefaultRoute()
    {
        $app = $this->getApp(array(
            'routes' => array(),
            'routeContext' => array(
                'default' => array(
                    'module' => 'oft-test',
                    'controller' => 'index',
                    'action' => 'index',
                ),
                'notFound' => array(),
                'error' => array(),
            )
        ));

        $routerProvider = new \Oft\Service\Provider\Router();
        $router = $routerProvider->doCreate($app);

        $match = $router->match('/ctrl/act');
        $this->assertInstanceOf('Aura\Router\Route', $match);

        $this->assertSame('default', $match->name);

        $this->assertArrayHasKey('module', $match->params);
        $this->assertSame('oft-test', $match->params['module']);

        $this->assertArrayHasKey('controller', $match->params);
        $this->assertSame('ctrl', $match->params['controller']);

        $this->assertArrayHasKey('action', $match->params);
        $this->assertSame('act', $match->params['action']);
    }

    public function testMatchModulesRoute()
    {
        $app = $this->getApp(array(
            'routes' => array(),
            'routeContext' => array(
                'default' => array(
                    'module' => 'oft-test',
                    'controller' => 'index',
                    'action' => 'index',
                ),
                'notFound' => array(),
                'error' => array(),
            )
        ));

        $routerProvider = new \Oft\Service\Provider\Router();
        $router = $routerProvider->doCreate($app);

        $match = $router->match('/oft-test/ctrl/act');
        $this->assertInstanceOf('Aura\Router\Route', $match);

        $this->assertSame('modules', $match->name);

        $this->assertArrayHasKey('module', $match->params);
        $this->assertSame('oft-test', $match->params['module']);

        $this->assertArrayHasKey('controller', $match->params);
        $this->assertSame('ctrl', $match->params['controller']);

        $this->assertArrayHasKey('action', $match->params);
        $this->assertSame('act', $match->params['action']);
    }

    public function testBug651IntegerInActionOrControllerWithModuleRoute()
    {
        $app = $this->getApp(array(
            'routes' => array(),
            'routeContext' => array(
                'default' => array(
                    'module' => 'oft-test',
                    'controller' => 'index',
                    'action' => 'index',
                ),
                'notFound' => array(),
                'error' => array(),
            )
        ));

        $routerProvider = new \Oft\Service\Provider\Router();
        $router = $routerProvider->doCreate($app);

        $match = $router->match('/oft-test/ctrl1ctrl2/act1act2');
        $this->assertInstanceOf('Aura\Router\Route', $match);

        $this->assertSame('modules', $match->name);

        $this->assertArrayHasKey('module', $match->params);
        $this->assertSame('oft-test', $match->params['module']);

        $this->assertArrayHasKey('controller', $match->params);
        $this->assertSame('ctrl1ctrl2', $match->params['controller']);

        $this->assertArrayHasKey('action', $match->params);
        $this->assertSame('act1act2', $match->params['action']);
    }
}
