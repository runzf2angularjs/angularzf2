<?php

namespace Test\Mock;

use Mockery;
use Oft\Auth\Identity;
use Oft\Http\SessionInterface;
use Oft\Mvc\Application;
use Oft\Service\Provider\RouteContext;
use Oft\Mvc\Context\HttpContext;
use Oft\Mvc\Context\IdentityContext;
use Oft\View\View;
use Zend\View\HelperPluginManager;
use Zend\View\Model\ViewModel;

class ApplicationMock
{
    protected static function getIdentityContextMock(array $identityInfo = array(), SessionInterface $session = null)
    {
        $identity = new Identity($identityInfo);

        if ($session === null) {
            $session = Mockery::mock('Oft\Http\SessionInterface');
        }

        $identityContext = new IdentityContext($session, 3600, $identity);

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

    public static function factory(array $config = array(), array $identity = array())
    {
        $httpContext = self::getHttpContextMock();
        $identityContext = self::getIdentityContextMock($identity, $httpContext->session);
        $view = self::getViewMock();

        $app = new Application($config);
        $app->setService('Http', $httpContext);
        $app->setService('Identity', $identityContext);
        $app->setService('Route', new RouteContext());
        $app->setService('ViewModel', new ViewModel());
        $app->setService('View', $view);

        return $app;
    }
}
