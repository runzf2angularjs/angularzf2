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

namespace Oft\Mvc;

use Oft\Mvc\Application;

abstract class MiddlewareAbstract
{

    /**
     * Middleware suivant
     *
     * @var MiddlewareAbstract
     */
    protected $next;

    /**
     * Définit le middleware suivant
     *
     * @param MiddlewareAbstract $nextMiddleware
     * @return void
     */
    public function setNextMiddleware(MiddlewareAbstract $nextMiddleware)
    {
        $this->next = $nextMiddleware;
    }

    /**
     * Retourne le middleware suivant
     *
     * @return MiddlewareAbstract
     */
    public function getNextMiddleware()
    {
        return $this->next;
    }

    /**
     * Implémentation du middleware
     *
     * Fonction appellée par le middleware précédent
     *
     * @param Application $app Conteneur d'application
     */
    abstract public function call(Application $app);

}
