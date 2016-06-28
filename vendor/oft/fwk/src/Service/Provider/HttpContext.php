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

use Oft\Http\Request;
use Oft\Http\Response;
use Oft\Http\Session;
use Oft\Mvc\Context\HttpContext as MvcHttpContext;
use Oft\Service\FactoryInterface;
use Oft\Service\ServiceLocatorInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class HttpContext implements FactoryInterface
{

    /**
     * Instancie et configure les objets Request et Response
     *
     * @param ServiceLocatorInterface $app
     *
     * @return array
     */
    public function create(ServiceLocatorInterface $app)
    {
        $sessionOption = $app->config['session'];

        if (isset($sessionOption['save_path'])) {
            ini_set('session.save_path', $sessionOption['save_path']);
            unset($sessionOption['save_path']);
        }

        // SF2 Session init
        $sessionStorage = new NativeSessionStorage($sessionOption, new NativeSessionHandler());
        $session = new SymfonySession($sessionStorage);

        $request = new SymfonyRequest($_GET, array_merge($_POST, $_FILES), array(), $_COOKIE, array(), $_SERVER);
        $response = new SymfonyResponse();

        // Pas de mise en cache souhaitée par défaut
        $response->headers->set('cache-control', 'no-cache');
        $response->headers->set('pragma', 'no-cache');
        $response->headers->set('expires', -1);

        $request->setSession($session);

        $httpContext = new MvcHttpContext();
        $httpContext->setRequest(new Request($request));
        $httpContext->setResponse(new Response($response));
        $httpContext->setSession(new Session($session, $sessionStorage));

        return $httpContext;
    }

}
