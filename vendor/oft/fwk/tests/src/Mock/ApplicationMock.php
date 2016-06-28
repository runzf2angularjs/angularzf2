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

namespace Oft\Test\Mock;

use Mockery;
use Oft\Auth\Identity;
use Oft\Http\SessionInterface;
use Oft\Mvc\Application;
use Oft\Mvc\Context\IdentityContext as MvcIdentityContext;
use Oft\Mvc\Context\RenderOptionsContext;
use Oft\Mvc\Context\RouteContext;
use Oft\Test\Mock\HttpContext;
use Oft\View\View;
use Zend\View\HelperPluginManager;

class ApplicationMock
{
    protected static function getIdentityContextMock(array $identityInfo = array(), SessionInterface $session = null)
    {
        $identity = new Identity($identityInfo);

        if ($session === null) {
            $session = Mockery::mock('Oft\Http\SessionInterface');
        }

        $identityContext = new MvcIdentityContext($session, 3600, $identity);

        return $identityContext;
    }

    protected static function getHttpContextMock()
    {
        return new HttpContext(array(
            'request' => Mockery::mock('Oft\Http\RequestInterface'),
            'response' => Mockery::mock('Oft\Http\ResponseInterface'),
            'session' => Mockery::mock('Oft\Http\SessionInterface'),
        ));
    }

    protected static function getViewMock(array $viewPlugins = array())
    {
        $usedViewPlugins = array_merge(
            array(
                'breadcrumb' => 'Oft\View\Helper\Breadcrumb',
                'smartUrl' => 'Oft\View\Helper\SmartUrl',
                'smartUrlFromRoute' => 'Oft\View\Helper\SmartUrlFromRoute'
            ),
            $viewPlugins
        );

        $view = new View();
        $pluginManager = new HelperPluginManager();

        foreach ($usedViewPlugins as $name => $pluginClass) {
            $plugin = new $pluginClass;
            $plugin->setView($view);
            $pluginManager->setService($name, new $plugin);
        }

        $view->setHelperPluginManager($pluginManager);
        
        return $view;
    }

    public static function factory(array $config = array(), array $identity = array(), $moduleManager = null)
    {
        $httpContext = self::getHttpContextMock();
        $identityContext = self::getIdentityContextMock($identity, $httpContext->session);
        $view = self::getViewMock();
        
        $app = new Application($config, $moduleManager);
        $app->setService('Http', $httpContext);
        $app->setService('Identity', $identityContext);
        $app->setService('Route', new RouteContext());
        $app->setService('RenderOptions', new RenderOptionsContext());
        $app->setService('View', $view);

        return $app;
    }
}
