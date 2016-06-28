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

use Oft\Mvc\Application;
use Oft\Mvc\ControllerAbstract;
use Oft\Mvc\Exception\ForwardException;
use Oft\Mvc\Exception\HttpException;
use Oft\Mvc\Exception\NotFoundException;
use Oft\Mvc\MiddlewareAbstract;
use Oft\View\Model;

class Dispatch extends MiddlewareAbstract
{

    /** @var bool */
    protected $exceptionAlreadyCatched = false;

    /** @var Model */
    protected $viewModel;

    /**
     * Initialise l'instance du contrôleur
     *
     * @param ControllerAbstract $controllerInstance
     * @param Application $app
     * @return void
     */
    protected function initializeControllerInstance($controllerInstance, Application $app)
    {
        if (\method_exists($controllerInstance, 'setApplication')) {
            $controllerInstance->setApplication($app);
        }

        if (\method_exists($controllerInstance, 'setHttpRequest')) {
            $controllerInstance->setHttpRequest($app->http->request);
        }

        if (\method_exists($controllerInstance, 'setHttpResponse')) {
            $controllerInstance->setHttpResponse($app->http->response);
        }

        if (\method_exists($controllerInstance, 'setViewModel')) {
            $controllerInstance->setViewModel($this->viewModel);
        }

        if (\method_exists($controllerInstance, 'setParams')) {
            $controllerInstance->setParams($app->route->params);
        }

        if (\method_exists($controllerInstance, 'init')) {
            $controllerInstance->init();
        }
    }

    /**
     * Implémentation du middleware
     *
     * @param Application $app Conteneur d'application
     */
    public function call(Application $app)
    {
        $route = $app->route->current;
        $controllerFactory = $app->get('ControllerFactory');

        try {
            $this->viewModel = new Model();

            // Reset render options to defaults
            $app->renderOptions->reset();

            // Get controller callable from route
            $controllerInstanceCallable = $controllerFactory->createFromRoute($route);

            // Initialize instance
            if (is_array($controllerInstanceCallable)) {
                $this->initializeControllerInstance($controllerInstanceCallable[0], $app);
            }

            // Call action capturing echo'ed content
            try {
                ob_start();
                $result = call_user_func_array($controllerInstanceCallable, $app->route->params);

                $this->handleResult($result, $app);
            } catch (\Exception $e) {
                // In case of exception we discard the content
                ob_end_clean();
                throw $e;
            }

            // Add captured content to response
            $content = ob_get_clean();
            if (strlen($content)) {
                $app->http->response->addContent($content);
            }

            // Dispatched, we can leave
            return;
        } catch (ForwardException $e) {
            // Handled in Acl
            throw $e;
        } catch (NotFoundException $e) {
            // Try to handle it gracefully
            $nextRoute = $app->route->notFound;
            $nextParams = array();
        } catch (HttpException $e) {
            // Handled in Application::run
            throw $e;
        } catch (\Exception $e) {
            $nextRoute = $app->route->error;
            $nextParams = array(
                'type' => 'exception',
                'exception' => $e,
            );
        }

        // If already catched
        if ($this->exceptionAlreadyCatched) {
            throw $e;
        }
        $this->exceptionAlreadyCatched = true;

        // One time recursive call
        $app->route->setCurrent($nextRoute, $nextParams);
        $this->call($app);
    }

    public function handleResult($result, Application $app)
    {
        // If it is an Oft\View\Model merge the values
        if ($result instanceof Model) {
            $this->viewModel->merge($result->getArrayCopy());
        } else if (is_array($result)) { // array => merge with vars
            $this->viewModel->merge($result);
        } else if (is_bool($result)) { // bool => use it to say if we should render or not
            $app->renderOptions->setRenderView($result);
        } else if (!is_null($result)) { // !null => set it as the 'content' key
            $this->viewModel['content'] = $result;
        }

        // Set renderOption viewModel
        $app->renderOptions->setViewModel($this->viewModel);
    }

}
