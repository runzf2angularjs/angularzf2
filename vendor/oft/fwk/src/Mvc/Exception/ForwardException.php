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

namespace Oft\Mvc\Exception;

use Exception;

class ForwardException extends Exception
{

    /**
     * Composantes de la route
     *
     * @var array
     */
    protected $route = array();

    /**
     * Paramètres de la route
     *
     * @var array
     */
    protected $params = array();

    /**
     * Initialisation
     *
     * @param array $route Composantes de la route
     * @param array $params Paramètres de la route
     * @param bool $previous Exception précédente
     * @return self
     */
    public function __construct(array $route, array $params = array(), $message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        $this->route  = $route;
        $this->params = $params;
    }

    /**
     * Retourne la route
     *
     * @return array
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Retourne les paramètres de la route
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

}
