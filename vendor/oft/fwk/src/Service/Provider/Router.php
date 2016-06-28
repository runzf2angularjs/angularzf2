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

namespace Oft\Service\Provider;

use Aura\Router\Router as AuraRouter;
use Aura\Router\RouterFactory;
use Oft\Mvc\Application;
use Oft\Service\CachedFactoryAbstract;
use Oft\Service\ServiceLocatorInterface;

class Router extends CachedFactoryAbstract
{

    /**
     * Instancie et configure le router
     *
     * @param Application $app
     * @return AuraRouter
     */
    public function doCreate(ServiceLocatorInterface $app)
    {
        $routerFactory = new RouterFactory();
        /* @var $router AuraRouter */
        $router = $routerFactory->newInstance();

        $routes = $router->getRoutes();

        // Default routes validators
        $routes->addTokens(array(
            'module' => implode('|', array_keys($app->moduleManager->getModules())),
            'controller' => '[a-z][a-z\-0-9]+',
            'action' => '[a-z][a-z\-0-9]+'
        ));

        // Default route values
        $routes->addValues($app->route->default);

        // Define Module routes
        foreach ($app->config['routes'] as $routeName => $routeConfig) {
            $route = $routes->add(is_string($routeName) ? $routeName : null, $routeConfig['path']);
            if (isset($routeConfig['values'])) {
                $route->addValues($routeConfig['values']);
            }
            if (isset($routeConfig['tokens'])) {
                $route->addTokens($routeConfig['tokens']);
            }
            if (isset($routeConfig['method'])) {
                if (is_array($routeConfig['method'])) {
                    $route->addServer(array(
                        'REQUEST_METHOD' => implode('|', $routeConfig['method'])
                    ));
                } else {
                    $route->addServer(array(
                        'REQUEST_METHOD' => $routeConfig['method']
                    ));
                }
            }
        }

        // Default routes
        $routes->add('modules', '/{module}{/controller,action}');
        $routes->add('default', '{/controller,action}');

        return $router;
    }

}
