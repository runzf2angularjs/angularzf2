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

namespace Oft\View\Helper;

use Aura\Router\Router;
use Zend\View\Helper\AbstractHelper;

class SmartUrl extends AbstractHelper
{

    /**
     * @var Router
     */
    protected $router;

    /**
     * Retourne une URL à partir des informations fournies
     *
     * Cette aide s'appuie sur l'aide "smartUrlFromRoute"
     *
     * @param string $action Action ciblée
     * @param string $controller Contrôleur ciblé
     * @param string $module Module ciblé
     * @param array $params Paramètres éventuels dans l'URL
     * @param string $routeName Nom de la route associée
     * @return string
     */
    public function __invoke($action = null, $controller = null, $module = null, array $params = array(), $routeName = null)
    {
        /* @var $app \Oft\Mvc\Application */
        $app = $this->view->app;
        
        // Module, Controller, Action ne doivent pas être fournis dans $params
        unset($params['module']);
        unset($params['controller']);
        unset($params['action']);
        
        // module, controller, action
        $currentRoute = $app->route->current;
        // autres paramètres de la route
        $routeParams = $app->route->params;

        if ($action !== null) {
            $currentRoute['action'] = $action;
        }
        if ($controller !== null) {
            $currentRoute['controller'] = $controller;
        }
        if ($module !== null) {
            $currentRoute['module'] = $module;
        }

        $route = array_merge($routeParams, $currentRoute, $params);
        
        // La route courante n'est pas prise en compte avec SmartUrl
        unset($route['name']);
        
        if ($routeName === null) {
            if ($route['module'] === $app->route->default['module']) {
                $routeName = 'default';
            } else {
                $routeName = 'modules';
            }
        }

        return $this->view->smartUrlFromRoute($routeName, $route);
    }

}
