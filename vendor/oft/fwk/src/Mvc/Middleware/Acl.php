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
use Oft\Mvc\Exception\ForwardException;
use Oft\Mvc\Exception\RedirectException;
use Oft\Mvc\MiddlewareAbstract;

/**
 * Middleware Acl
 *  - Vérifie si l'utilisateur a les droits nécessaires pour accéder à une page
 *  - Redirige vers la page de connexion s'il n'est pas connecté et que la page est refusée
 *
 * @author CC PHP <cdc.php@orange.com>
 */
class Acl extends MiddlewareAbstract
{
    /**
     * Implémentation du middleware
     *
     * @param Application $app Conteneur d'application
     */
    public function call(Application $app)
    {
        if (!$this->isAllowed($app)) {
            // Guest can try to connect first
            if ($app->identity->get()->isGuest()) {
                $this->authRedirect($app);
            }

            // No right !
            $app->route->setCurrentAsError(array(
                'type' => 'noRight',
                'message' => "Vous n'avez pas accès à cette page"
            ));
        }

        // Try to dispatch taking care of forwards
        $forwardedCount = $app->config['maxForward'] + 1; // Max number of forwards
        do {
            try {
                $this->next->call($app);

                return; // Dispatched, we can leave
            } catch (ForwardException $nextRoute) { // Forward is handled here to allow ACL checks
                $forwardedCount --;
                $app->route->setCurrentAsForward($nextRoute->getRoute(), $nextRoute->getParams());
            }
        } while($forwardedCount);

        // Too many forwards
        throw new \RuntimeException('Too many forward');
    }

    /**
     * Vérification si l'identity est autorisée à accéder à une page
     *
     * @param Application $app
     * @return boolean
     */
    public function isAllowed(Application $app)
    {
        $currentRoute = $app->route->current;

        // Assets are always allowed
        // Fix #869 : important, l'objet Identity ne doit pas être sollicité pour servir les assets (session)
        if (isset($currentRoute['name']) && in_array($currentRoute['name'], array('assets', 'assets.file'))) {
            return true;
        }

        $identity = $app->identity->get();
        $acl = $app->get('Acl');

        if ($acl->isMvcAllowed($currentRoute, $identity)) {
            oft_trace('ACL : ' . $identity->getUsername() . ' => OK (ACL)', array('route' => $currentRoute));
            return true;
        }

        oft_trace('ACL : ' . $identity->getUsername() . ' => KO', array('route' => $currentRoute));
        return false;
    }

    /**
     * Stockage de chemin de la page avant redirection vers l'authentification
     * Permet de rediriger sur cette même page après connexion
     *
     * @param Application $app
     * @throws RedirectException
     */
    public function authRedirect(Application $app)
    {
        // Save requested URI
        $container = $app->http->session->getContainer('Oft\Mvc\Middleware\Acl');
        $baseUrl = $app->http->request->getBaseUrl();

        $redirectUrl = $app->http->request->getFromServer('REQUEST_URI');
        if ($baseUrl !== '/') {
            $redirectUrl = str_replace($baseUrl, '', $redirectUrl);
        }
        $container->authRedirectUrl = $redirectUrl;

        // Redirect
        throw new RedirectException($baseUrl . '/auth/login');
    }

}
