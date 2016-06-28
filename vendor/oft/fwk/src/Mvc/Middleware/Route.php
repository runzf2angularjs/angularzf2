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

namespace Oft\Mvc\Middleware;

use Aura\Router\Router as AuraRouter;
use Oft\Mvc\Application;
use Oft\Mvc\MiddlewareAbstract;

class Route extends MiddlewareAbstract
{

    /**
     * Implémentation du middleware
     *
     * @param Application $app Conteneur d'application
     */
    public function call(Application $app)
    {
        /* @var $router AuraRouter */
        $router = $app->get('Router');

        $pathInfo = $app->http->request->getPathInfo();

        $matchingRoute = $router->match($pathInfo, $_SERVER);

        if ($matchingRoute) {
            // Get routing params & wildcard params
            $route = array(
                'module' => $matchingRoute->params['module'],
                'controller' => $matchingRoute->params['controller'],
                'action' => $matchingRoute->params['action'],
                'name' => $matchingRoute->name
            );

            $params = $matchingRoute->params;
            unset($params['module']);
            unset($params['controller']);
            unset($params['action']);
        } else {
            // 404 route
            $route = $app->route->notFound;
            $params = array('route' => $pathInfo);
        }

        $app->route->setCurrent($route, $params);
        
        $this->next->call($app);
    }

}
