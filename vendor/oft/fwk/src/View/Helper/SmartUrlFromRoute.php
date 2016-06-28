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

class SmartUrlFromRoute extends AbstractHelper
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * Retourne une URL à partir de la route et des paramètres fournis
     *
     * La route par défaut est utilisée si non fournie
     *
     * @param string $route Nom de la route
     * @param array $params Paramètres éventuels de la route
     * @return string
     */
    public function __invoke($route = null, array $params = array())
    {
        if ($this->router === null) {
            $this->router = $this->view->app->get('Router');
        }

        if ($route === null) {
            $route = 'default';
        }

        $baseUrl = $this->view->getBaseUrl();

        return $baseUrl . $this->router->generate($route, $params);
    }

    /**
     * Définit le composant router
     *
     * @param Router $router
     * @return void
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }
}
