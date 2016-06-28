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

namespace Oft\Acl;

use Aura\Router\Route;
use Aura\Router\RouteCollection;
use Oft\Auth\Identity;
use Zend\Permissions\Acl\Acl as Zend_Acl;

/**
 * Composant de gestion des Acl
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Acl extends Zend_Acl
{    
    /**
     * Liste des routes
     * 
     * @var RouteCollection 
     */
    protected $routes;

    /**
     * Liste des ressources autorisées via "whitelist"
     * 
     * @var array 
     */
    protected $whitelist;

    /**
     * 
     * @param RouteCollection $routes
     * @param array $whitelist
     */
    public function __construct(RouteCollection $routes, array $whitelist)
    {
        $this->routes = $routes;
        $this->whitelist = $whitelist;
    }

    /**
     * Récupération du nom d'une route
     * 
     * @param array $route
     * @return string
     */
    public function getRouteName(array $route)
    {
        // Get route name or guess it
        if (isset($route['name'])) {
            $routeName = $route['name'];
        } else if (isset($route['module'])) {
            $routeName = 'modules';
        } else {
            $routeName = 'default';
        }

        return $routeName;
    }

    /**
     * Récupération des paramètres d'une route
     * 
     * @param Route|array $route
     * @return array
     */
    public function getRouteParams($route)
    {
        $routeName = $this->getRouteName($route);

        // No route defined => return as-is
        if (!isset($this->routes[$routeName])) {
            return $route;
        }

        // Get defaults module, controller and action from router
        return array_merge(
            $this->routes[$routeName]->values, // Default values
            $route
        );
    }
    
    /**
     * Récupération des noms de ressources d'une route
     * 
     * @param Route|array $route
     * @return array
     */
    public function getInlineRoute($route)
    {
        $routeParams = $this->getRouteParams($route);
        
        return array(
            "mvc.{$routeParams['module']}.{$routeParams['controller']}.{$routeParams['action']}",
            "mvc.{$routeParams['module']}.{$routeParams['controller']}",
            "mvc.{$routeParams['module']}"
        );
    }

    /**
     * Test si une route est autorisée en fonction de :
     *  - L'identity de l'utilisateur (admin)
     *  - La "whitelist"
     *  - La définition des ressources autorisées aux rôles
     * 
     * @param Route|array $route
     * @param Identity $identity
     * @return boolean
     */
    public function isMvcAllowed($route, Identity $identity)
    {
        if ($identity->isAdmin()) {
            return true;
        }

        $inlineRoutes = $this->getInlineRoute($route);

        if ($this->isAllowedFromWhiteList($route, $inlineRoutes)) {
            return true;
        }

        if ($this->isAllowedFromRoute($route, $identity, $inlineRoutes)) {
            return true;
        }

        return false;
    }

    /**
     * Test si une route est autorisée dans la "whitelist"
     * 
     * @param Route|array $route
     * @param array $inlineRoutes
     * @return boolean
     */
    public function isAllowedFromWhiteList($route, array $inlineRoutes = null)
    {
        if ($inlineRoutes === null) {
            $inlineRoutes = $this->getInlineRoute($route);
        }

        // Whitelist (allow to bypass ACL for some resources)
        $whiteList = $this->whitelist;
        foreach ($inlineRoutes as $inlineRoute) {
            if (in_array($inlineRoute, $whiteList)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test si une route a au moins une ressource autorisée en fonction des groupes de Identity
     * 
     * @param array $route
     * @param Identity $identity
     * @param array $inlineRoutes
     * @return boolean
     */
    public function isAllowedFromRoute(array $route, Identity $identity, array $inlineRoutes = null)
    {
        // Put everything in line
        if ($inlineRoutes === null) {
            $inlineRoutes = $this->getInlineRoute($route);
        }

        // Check ACL
        foreach ($inlineRoutes as $mvcRoute) {
            foreach ($identity->getGroups() as $group => $notUsed) {
                if ($this->hasResource($mvcRoute) && $this->hasRole($group) && $this->isAllowed($group, $mvcRoute)) {
                    return true;
                }
            }
        }

        return false;
    }
    
    /**
     * Récupération des routes
     * 
     * @return RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
