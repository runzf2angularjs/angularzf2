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

namespace Oft\Controller;

use Oft\Mvc\ControllerAbstract;

class ErrorController extends ControllerAbstract
{

    public function init()
    {
        if ($this->response->getContentType() !== 'text/html') {
            $this->response->setContentType('text/html');
        }
        $this->setRenderLayout(true);
    }

    public function errorAction($type, $exception = null)
    {
        if ($exception instanceof \Exception) {
            $this->response->setStatusCode(500);

            oft_exception($exception, array(), 'security');

            if ($exception instanceof \DomainException) {
                $type = get_class($exception);
                $message = $exception->getMessage();
            } elseif ($this->isDebug()) {
                throw $exception;
            } else {
                $this->setLayoutTemplate('error');
                $type = "Erreur à l'exécution";
                $message = "Une erreur est survenue qui nous empêche d'afficher la page demandée";
            }
        } elseif (is_string($exception)) {
            $message = $exception;
        } else {
            $message = "Une erreur est survenue";
        }

        $this->viewModel->type = $type;
        $this->viewModel->message = $message;

        if ($this->isDebug()) {
            $this->viewModel->exception = $exception;
        }
    }

    public function notFoundAction()
    {
        $this->response->setStatusCode(404);

        oft_trace(
            '404 Not Found',
            array('route' => $this->app->route->previous),
            \Monolog\Logger::DEBUG
        );

        $this->viewModel->route = $this->app->route->previous;
        $this->viewModel->routeParams = $this->app->route->previousParams;
        $this->viewModel->isDebug = $this->isDebug();
    }
}
