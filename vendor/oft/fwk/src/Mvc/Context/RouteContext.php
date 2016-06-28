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

namespace Oft\Mvc\Context;

/**
 * @property-read array $current Current route
 * @property-read array $params Current route params
 * @property-read array $previous Previous route
 * @property-read array $previousParams Previous route params
 * @property-read array $default Default route
 * @property-read array $notFound 404 route
 * @property-read array $error Error route
 *
 */
class RouteContext extends ContextAbstract
{

    protected $contexts = array(
        'current' => array(),
        'params' => array(),
        'previous' => array(),
        'previousParams' => array(),
        'default' => array(),
        'notFound' => array(),
        'error' => array(),
    );

    /**
     * Définit la route courante
     *
     * @param array $route Composantes de la route
     * @return self
     */
    public function setCurrent(array $route, array $params = array())
    {
        $this->setContext('previous', $this->current)
            ->setParams($params)
            ->setContext('current', $route);

        return $this;
    }

    public function setCurrentAsError(array $params)
    {
        return $this->setCurrent($this->error, $params);
    }

    public function setCurrentAsForward(array $route)
    {
        return $this->setCurrent(array_merge($this->current, $route));
    }

    /**
     * Définit les paramètres de la route courante
     *
     * @params array $currentRouteParams Paramètres de la route courante
     * @return self
     */
    public function setParams(array $params)
    {
        $this->setContext('previousParams', $this->params)
            ->setContext('params', $params);

        return $this;
    }

    public function setDefault(array $route)
    {
        $this->setContext('default', $route);

        return $this;
    }

    public function setNotFound(array $route)
    {
        $this->setContext('notFound', $route);

        return $this;
    }

    public function setError(array $route)
    {
        $this->setContext('error', $route);

        return $this;
    }

    /**
     * Retourne la valeur d'un paramètre de la route courante
     *
     * Si le paramètre est absent, $default sera retourné
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }

        return $default;
    }

}
