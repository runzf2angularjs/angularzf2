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

use Oft\Mvc\Context\RouteContext as MvcRouteContext;
use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;

class RouteContext implements FactoryInterface
{
    public function create(ServiceLocatorInterface $app)
    {
        $defaultModuleName = $app->moduleManager->getDefault();
        $routeConfig = $app->config['routeContext'];

        $route = new MvcRouteContext();
        $route->setNotFound($routeConfig['notFound']);
        $route->setError($routeConfig['error']);

        $defaultRoute = $routeConfig['default'];
        $defaultRoute['module'] = str_replace('@default', $defaultModuleName, $defaultRoute['module']);
        $route->setDefault($defaultRoute);

        return $route;
    }

}
