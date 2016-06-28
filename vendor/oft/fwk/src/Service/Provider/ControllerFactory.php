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

use Oft\Module\ModuleManager;
use Oft\Mvc\ControllerAbstract;
use Oft\Mvc\Exception\NotFoundException;
use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;
use Oft\Util\String;

class ControllerFactory implements FactoryInterface
{
    /** @var ModuleManager */
    protected $moduleManager;

    public function create(ServiceLocatorInterface $app)
    {
        $this->moduleManager = $app->moduleManager;

        return $this;
    }

    /**
     * Retourne la classe du contrôleur à partir de la route
     *
     * @param array $route Composantes de la route
     * @return string
     */
    public function getControllerClass(array $route)
    {
        $moduleNs = $this->moduleManager->getModuleNamespace($route['module']);

        return $moduleNs
            . '\\Controller\\'
            . String::dashToCamelCase($route['controller'])
            . 'Controller';
    }

    /**
     * Retourne le nom de l'action à partir de la route
     *
     * @param array $route Composantes de la route
     * @return string
     */
    public function getActionMethod(array $route)
    {
        return lcfirst(String::dashToCamelCase($route['action'], false)) . 'Action';
    }

    /**
     * Retourne l'instance du contrôleur à partir de la route
     *
     * @param array $route Composantes de la route
     * @return ControllerAbstract
     */
    public function getControllerInstance(array $route)
    {
        $class = $this->getControllerClass($route);

        if (!\class_exists($class)) {
            throw new NotFoundException("Controller class is not defined");
        }

        return new $class;
    }

    /**
     * Retourne un tableau de 2 éléments (callable) :
     * instance du contrôleur et nom de l'action
     *
     * @param array $route Composantes de la route
     * @return array
     */
    public function createFromRoute(array $route)
    {
        $instance = $this->getControllerInstance($route);
        $method   = $this->getActionMethod($route);

        if (!\is_callable(array($instance, $method))) {
            throw new NotFoundException("Action does not exists on that controller");
        }

        return array($instance, $method);
    }

}
