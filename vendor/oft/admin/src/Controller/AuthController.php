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

namespace Oft\Admin\Controller;

use Exception;
use Monolog\Logger;
use Oft\Auth\AuthInterface;
use Oft\Mvc\ControllerAbstract;
use Oft\Mvc\Exception\HttpException;
use Oft\Mvc\Exception\RedirectException;

/**
 * Construction des IHM de connexion et déconnexion
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class AuthController extends ControllerAbstract
{

    public function loginAction()
    {
        /* @var $auth AuthInterface */
        $auth = $this->app->get('Auth');

        // Handle form if any
        $form = $auth->getForm();
        if ($form !== null && $this->request->isPost()) {
            $form->setData($this->request->getFromPost());
            if (! $form->isValid()) {
                $this->flashMessage('Invalid Form', self::WARNING);

                return array(
                    'form' => $form
                );
            }
        }

        // Handle auth
        if ($form === null || $this->request->isPost()) {
            try {
                /* @var $identity \Oft\Auth\Identity */
                $identity = $auth->authenticate();

                // Set preferred language in cookie (cannot be null)
                $this->app->http->response->setCookie(
                    'lang',
                    $identity->getLanguage()
                );

                $this->app->identity->set($identity);

                oft_trace(
                    "Auth success",
                    array(
                        'username' => $identity->getUsername(),
                        'groups' => $identity->getGroups()
                    )
                );

                $this->successRedirect();

            } catch (HttpException $e) {
                throw $e;
            } catch (Exception $e) {
                $this->flashMessage('Authentication failed', self::WARNING);

                oft_trace("Auth failed with exception '" . get_class($e) . "': " . $e->getMessage());
                oft_exception($e);
            }
        }

        if ($form === null) {
            return false;
        }

        return array(
            'form' => $form
        );
    }

    public function logoutAction()
    {
        oft_trace('Logout', array(), Logger::INFO);

        $this->app->identity->drop();
        $this->session->destroy();

        $this->flashMessage('You are now logged out');

        $this->logoutRedirect();
    }

    protected function successRedirect()
    {
        // Identifiant regénéré
        $session = $this->app->http->session;
        $session->regenerateId();

        $authRedirectUrl = '/';
        $container = $session->getContainer('Oft\Mvc\Middleware\Acl');

        if (isset($container->authRedirectUrl)) {
            $authRedirectUrl = $container->authRedirectUrl;
            $session->dropContainer('Oft\Mvc\Middleware\Acl');
        }

        throw new RedirectException($this->request->getBaseUrl() . $authRedirectUrl);
    }

    protected function logoutRedirect()
    {
        $config = $this->app->config['auth']['logout-url'];

        $logoutUrl = null;
        if (is_string($config)) { // String = URL
            $logoutUrl = $config;
        } else if (is_array($config)) { // Array
            // Route definition ...
            if (array_key_exists('route', $config)) {
                $logoutUrl = $this->smartUrlFromRoute(
                    isset($config['route']['name']) ? $config['route']['name'] : null,
                    isset($config['route']['values']) ? $config['route']['values'] : array()
                );
            }

            // ... or values for defaults routes
            $values = array_merge($this->app->route->default, $config);
            $logoutUrl = $this->smartUrl(
                $values['action'],
                $values['controller'],
                $values['module']
            );
        }

        throw new RedirectException($logoutUrl ? $logoutUrl : $this->smartUrlFromRoute());
    }

}
