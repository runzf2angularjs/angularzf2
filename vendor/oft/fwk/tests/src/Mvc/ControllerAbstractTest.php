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

namespace Oft\Test\Mvc;

use Aura\Router\RouteCollection;
use Aura\Router\RouteFactory;
use Oft\Acl\Acl;
use Oft\Auth\Identity;
use Oft\Mvc\Application;
use Oft\Mvc\Context\RouteContext;
use Oft\Mvc\ControllerAbstract;
use Oft\Mvc\Exception\RedirectException;
use Oft\Test\Mock\ApplicationMock;
use Oft\Test\Mock\HttpContext;
use Oft\Test\Mock\IdentityContext;
use Oft\View\Helper\FlashMessenger;
use Oft\View\Model;
use Oft\View\View;
use PHPUnit_Framework_TestCase;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\View\Helper\HelperInterface;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\RendererInterface;

class Controller extends ControllerAbstract
{
    protected $init = false;
    
    public function get($name)
    {
        return $this->$name;
    }
    
    public function set($attr, $value)
    {
        $this->$attr = $value;
    }
    
    public function init()
    {
        parent::init();
        $this->init = true;
    }
}

class Plugin implements HelperInterface
{
    public $args;
    
    public function __invoke()
    {
        $this->args = func_get_args();
    }

    public function getView()
    {
        
    }

    public function setView(RendererInterface $view)
    {

    }

}

class ControllerAbstractTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ControllerAbstract
     */
    protected $controller;

    protected function setUp()
    {
        $_SESSION = array();
        FlashMessenger::setMessageContainer(null);
        
        $this->controller = new Controller();
    }
    
    public function testSetApp()
    {
        $app = new Application();

        $httpContextMock = new HttpContext();

        $app->setService('Http', $httpContextMock);
        
        $this->controller->setApplication($app);
        
        $this->assertSame($httpContextMock->request, $this->controller->get('request'));
        $this->assertSame($httpContextMock->response, $this->controller->get('response'));
    }

    public function testSetViewModel()
    {
        $viewModel = new Model();
        
        $this->controller->setViewModel($viewModel);
        
        $this->assertSame($viewModel, $this->controller->get('viewModel'));
    }

    public function testRedirect()
    {
        $this->getPluginForViewPluginsTests('smartUrl');

        try {
            $this->controller->redirect();
        } catch (RedirectException $e) {
            $headers = $e->getHeaders();
            $this->assertInternalType('array', $headers);
            $this->assertArrayHasKey('Location', $headers);
            $this->assertEquals('', $headers['Location']);
            return;
        }

        $this->fail();
    }

    public function testRedirectToUrl()
    {
        $url = '/url';

        try {
            $this->controller->redirectToUrl($url);
        } catch (RedirectException $e) {
            $headers = $e->getHeaders();
            $this->assertInternalType('array', $headers);
            $this->assertArrayHasKey('Location', $headers);
            $this->assertEquals($url, $headers['Location']);
            return;
        }

        $this->fail();
    }

    public function testRedirectToRoute()
    {
        $routeName = null;
        $params = array();

        $this->getPluginForViewPluginsTests('smartUrlFromRoute');

        try {
            $this->controller->redirectToRoute($routeName, $params);
        } catch (RedirectException $e) {
            $headers = $e->getHeaders();
            $this->assertInternalType('array', $headers);
            $this->assertArrayHasKey('Location', $headers);
            $this->assertEquals('', $headers['Location']);
            return;
        }

        $this->fail();
    }

    public function testForward()
    {
        $this->setExpectedException('Oft\Mvc\Exception\ForwardException');
        
        $this->controller->forward(array());
    }

    public function testFlashMessage()
    {
        $this->controller->flashMessage('test');
        $messages = FlashMessenger::getMessagesContainer()->exchangeArray(array());

        $this->assertTrue(is_array($messages));
        $this->assertArrayHasKey(0, $messages);
        $this->assertTrue(is_array($messages[0]));
        $this->assertSame('info', $messages[0][0]);
        $this->assertSame('test', $messages[0][1]);
    }

    public function testInit()
    {
        $this->assertFalse($this->controller->get('init'));
        $this->controller->init();
        $this->assertTrue($this->controller->get('init'));
    }

    protected function getPluginForViewPluginsTests($name)
    {
        $plugin = new Plugin();

        $pluginManager = new HelperPluginManager();
        $pluginManager->setService($name, $plugin);

        $view = new View();
        $view->setHelperPluginManager($pluginManager);

        $app = new Application();
        $app->setService('View', $view);

        $httpContextMock = new HttpContext();
        $app->setService('Http', $httpContextMock);

        $this->controller->setApplication($app);

        return $plugin;
    }

    public function testBreadcrumb()
    {
        $plugin = $this->getPluginForViewPluginsTests('breadcrumb');

        $this->controller->breadcrumb('test', 'href');

        $this->assertContains('test', $plugin->args);
        $this->assertContains('href', $plugin->args);
    }

    public function testSmartUrl()
    {
        $plugin = $this->getPluginForViewPluginsTests('smartUrl');
        
        $this->controller->smartUrl('a', 'c', 'm', array('p'=>'v'), 'routeName');
        
        $this->assertContains('a', $plugin->args);
        $this->assertContains('c', $plugin->args);
        $this->assertContains('m', $plugin->args);
        $this->assertContains('routeName', $plugin->args);
    }
    
    public function testSmartUrlFromRoute()
    {
        $plugin = $this->getPluginForViewPluginsTests('smartUrlFromRoute');
        
        $this->controller->smartUrlFromRoute('routeName', array('p' => 'v'));
        
        $this->assertContains('routeName', $plugin->args);
        $this->assertArrayHasKey('p', $plugin->args[1]);
    }
    
    public function testDisableRendering()
    {
        $app = ApplicationMock::factory();
        $viewModel = new Model();
        
        $this->controller->setApplication($app);
        $this->controller->setViewModel($viewModel);
        
        $this->assertTrue($app->renderOptions->renderLayout);
        $this->assertTrue($app->renderOptions->renderView);
        
        $this->controller->disableRendering(true, true);
        $this->assertFalse($app->renderOptions->renderLayout);
        $this->assertFalse($app->renderOptions->renderView);
        
        $this->controller->disableRendering(false, false);
        $this->assertTrue($app->renderOptions->renderLayout);
        $this->assertTrue($app->renderOptions->renderView);
    }

    public function testIsPost()
    {
        $app = ApplicationMock::factory();
        $app->http->request->shouldReceive('isPost')
            ->andReturn(true);
        
        $this->controller->setApplication($app);

        $this->assertTrue($this->controller->isPost());
    }

    public function testIsPostFalse()
    {
        $app = ApplicationMock::factory();
        $app->http->request->shouldReceive('isPost')
            ->andReturn(false);

        $this->controller->setApplication($app);

        $this->assertFalse($this->controller->isPost());
    }

    public function testGetCurrentIdentity()
    {
        $app = ApplicationMock::factory(array(), array('username' => 'test'));

        $this->controller->setApplication($app);

        $identity = $this->controller->getCurrentIdentity();

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);
        $this->assertSame('test', $identity->getUsername());
    }

    public function testIsDebugIsTrue()
    {
        $app = ApplicationMock::factory(array('debug' => true));
        $this->controller->setApplication($app);

        $this->assertTrue($this->controller->isDebug());
    }

    public function testIsDebugIsFalse()
    {
        $app = ApplicationMock::factory(array('debug' => false));
        $this->controller->setApplication($app);

        $this->assertFalse($this->controller->isDebug());
    }

    public function testHasAccessToAdmin()
    {
        $app = new Application();

        $identity = new Identity(array(
            'groups' => array('administrators' => 'Administrateurs')
        ));

        $httpContext = new HttpContext();
        $app->setService('Http', $httpContext);

        $identityContext = new IdentityContext($identity);
        $app->setService('Identity', $identityContext);

        $acl = new Acl(new RouteCollection(new RouteFactory()), array());
        $app->setService('Acl', $acl);

        $routeContext = new RouteContext();
        $routeContext->setCurrent(array());
        $app->setService('Route', $routeContext);

        $this->controller->setApplication($app);

        $this->assertTrue($this->controller->hasAccessTo('anyway'));
    }
    
    protected function getAppWithAccessTo($app, $access, $route, $group)
    {
        $resource = 'mvc.' . implode('.', $route);

        $identity = new Identity(array(
            'groups' => array($group => 'Groupe Test')
        ));

        $identityContext = new IdentityContext($identity);
        $app->setService('Identity', $identityContext);

        $httpContext = new HttpContext();
        $app->setService('Http', $httpContext);

        $routeContext = new RouteContext();
        $routeContext->setCurrent($route);
        $app->setService('Route', $routeContext);

        $acl = new Acl(new RouteCollection(new RouteFactory), $app->config['acl']['whitelist']);
        $acl->addRole(new GenericRole($group));
        $acl->addResource(new GenericResource($resource));

        switch ($access) {
            case true :
                $acl->allow($group, $resource);
                break;
            case false :
                $acl->deny($group, $resource);
                break;
        }

        $app->setService('Acl', $acl);
        
        return $app;
    }

    public function testHasAccessToTrue()
    {
        $access = true;
        $group = 'grptest';
        $route = array(
            'module' => 'has',
            'controller' => 'access',
            'action' => 'to',
        );
        $config = array('acl' => array('whitelist' => array()));

        $app = new Application($config);
        $this->getAppWithAccessTo($app, $access, $route, $group, $config);
        $this->controller->setApplication($app);

        $hasAccessToAction = $this->controller->hasAccessTo(
            $route['action']
        );
        $hasAccessToCtrl = $this->controller->hasAccessTo(
            $route['action'],
            $route['controller']
        );
        $hasAccessToModule = $this->controller->hasAccessTo(
            $route['action'],
            $route['controller'],
            $route['module']
        );

        $this->assertTrue($hasAccessToAction);
        $this->assertTrue($hasAccessToCtrl);
        $this->assertTrue($hasAccessToModule);
    }

    public function testHasAccessToWithArray()
    {
        $access = true;
        $group = 'grptest';
        $route = array(
            'module' => 'has',
            'controller' => 'access',
            'action' => 'to',
        );
        $config = array('acl' => array('whitelist' => array()));

        $app = new Application($config);
        $this->getAppWithAccessTo($app, $access, $route, $group, $config);
        $this->controller->setApplication($app);

        $hasAccessToAction = $this->controller->hasAccessTo(array(
            'action' => $route['action']
        ));
        $hasAccessToCtrl = $this->controller->hasAccessTo(array(
            'action' => $route['action'],
            'controller' => $route['controller']
        ));
        $hasAccessToModule = $this->controller->hasAccessTo(array(
            'action' => $route['action'],
            'controller' => $route['controller'],
            'module' => $route['module']
        ));

        $this->assertTrue($hasAccessToAction);
        $this->assertTrue($hasAccessToCtrl);
        $this->assertTrue($hasAccessToModule);
    }

    public function testHasAccessToFalse()
    {
        $access = false;
        $group = 'grptest';
        $route = array(
            'module' => 'has',
            'controller' => 'not-access',
            'action' => 'to',
        );
        $config = array('acl' => array('whitelist' => array()));

        $app = new Application($config);
        $this->getAppWithAccessTo($app, $access, $route, $group, $config);
        $this->controller->setApplication($app);

        $hasAccessToAction = $this->controller->hasAccessTo(
            $route['action']
        );
        $hasAccessToCtrl = $this->controller->hasAccessTo(
            $route['action'],
            $route['controller']
        );
        $hasAccessToModule = $this->controller->hasAccessTo(array(
            'action' => $route['action'],
            'controller' => $route['controller'],
            'module' => $route['module']
        ));

        $this->assertFalse($hasAccessToAction);
        $this->assertFalse($hasAccessToCtrl);
        $this->assertFalse($hasAccessToModule);
    }

    public function testHasAccessToWhitelist()
    {
        $access = false;
        $group = 'grptest';
        $route = array(
            'module' => 'has',
            'controller' => 'access',
            'action' => 'to',
        );

        $config = array(
            'acl' => array(
                'whitelist' => array('mvc.' . implode('.', $route))
            ),
        );

        $app = new Application($config);
        $this->getAppWithAccessTo($app, $access, $route, $group, $config);
        $this->controller->setApplication($app);
        
        $hasAccess = $this->controller->hasAccessTo(array(
            'action' => $route['action'],
            'controller' => $route['controller'],
            'module' => $route['module']
        ));

        $this->assertTrue($hasAccess);
    }

    public function testGetInputFilter()
    {
        $this->controller->set('inputFilterRules', array(
            'id' => array()
        ));

        $inputFilter = $this->controller->getInputFilter();

        $this->assertInstanceOf('Zend\InputFilter\InputFilter', $inputFilter);
        $this->assertInstanceOf('Zend\InputFilter\Input', $inputFilter->get('id'));
    }

    public function testHasParam()
    {
        $hasNotName = 'param';

        $hasName = 'id';
        $this->controller->set('inputFilterRules', array(
            $hasName => array()
        ));

        $hasParam = $this->controller->hasParam($hasName);
        $hasNotParam = $this->controller->hasParam($hasNotName);

        $this->assertTrue($hasParam);
        $this->assertFalse($hasNotParam);
    }

    public function testGetParamNotDefinedDefault()
    {
        $name = 'param';
        $default = 'default';

        $app = ApplicationMock::factory(array('debug' => true));
        $this->controller->setApplication($app);

        $result = $this->controller->getParam($name, $default);
        $messages = FlashMessenger::getMessagesContainer()->exchangeArray(array());

        $this->assertEquals($default, $result);
        $this->assertTrue(is_array($messages));
        $this->assertCount(1, $messages);
    }

    public function testGetParamWithEmptyValue()
    {
        $name = 'param';
        $default = 'default';
        $value = '';

        $app = ApplicationMock::factory(array('debug' => true));
        $this->controller->setApplication($app);

        // SET param with empty value
        $_GET[$name] = $value;
        $this->controller->set('inputFilterRules', array(
            $name => array()
        ));

        $result = $this->controller->getParam($name, $default);
        $messages = FlashMessenger::getMessagesContainer()->exchangeArray(array());

        $this->assertEquals($default, $result);
        $this->assertTrue(is_array($messages));
        $this->assertCount(0, $messages); // No messages
    }

    public function testGetParamWithInvalidInputInDebug()
    {
        $name = 'param';
        $value = 'value';

        $app = ApplicationMock::factory(array('debug' => true));
        $this->controller->setApplication($app);

        // SET param with invalid value
        $_GET[$name] = $value;
        $this->controller->set('inputFilterRules', array(
            $name => array(
                'validators' => array(
                    array('name' => 'Int'),
                ),
            ),
        ));

        // Logger for oft_error
        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')->once();
        \Monolog\Registry::removeLogger('default');
        \Monolog\Registry::addLogger($logger, 'default');

        try {
            $this->controller->getParam($name);
        } catch (\DomainException $e) {
            $this->assertStringStartsWith("Erreur de validation '$name' : ", $e->getMessage());
            return;
        }

        $this->fail();
    }

    public function testGetParamWithInvalidInput()
    {
        $name = 'param';
        $value = 'value';

        $app = ApplicationMock::factory(array('debug' => false));
        $this->controller->setApplication($app);

        // SET param with invalid value
        $_GET[$name] = $value;
        $this->controller->set('inputFilterRules', array(
            $name => array(
                'validators' => array(
                    array('name' => 'Int'),
                ),
            ),
        ));

        // Logger for oft_error
        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')->once();
        \Monolog\Registry::removeLogger('default');
        \Monolog\Registry::addLogger($logger, 'default');

        try {
            $this->controller->getParam($name);
        } catch (\DomainException $e) {
            $this->assertEquals("Erreur de validation des paramètres", $e->getMessage());
            return;
        }

        $this->fail();
    }

    public function testGetParamWithValidInput()
    {
        $name = 'param';
        $value = 42;

        $app = ApplicationMock::factory(array('debug' => false));
        $this->controller->setApplication($app);

        // SET param with invalid value
        $_GET[$name] = $value;
        $this->controller->set('inputFilterRules', array(
            $name => array(
                'validators' => array(
                    array('name' => 'Int'),
                ),
            ),
        ));

        try {
            $result = $this->controller->getParam($name);
        } catch (\DomainException $e) {
            $this->fail();
        }

        $this->assertEquals($value, $result);
    }

    public function testGetParamWithZero()
    {
        $name = 'param';
        $value = 0;

        $app = ApplicationMock::factory(array('debug' => false));
        $this->controller->setApplication($app);

        // SET param with zero
        $_GET[$name] = $value;
        $this->controller->set('inputFilterRules', array(
            $name => array(
                'validators' => array(
                    array('name' => 'Int'),
                ),
            ),
        ));

        try {
            $result = $this->controller->getParam($name, 45);
        } catch (\DomainException $e) {
            $this->fail();
        }

        $this->assertSame($value, $result);
    }

    public function testSendJson()
    {
        $array = array('key' => 'value');

        try {
            $this->controller->sendJson($array);
        } catch (\Oft\Mvc\Exception\HttpException $e) {
            $this->assertEquals(200, $e->getStatusCode());
            return;
        }

        $this->fail();
    }

    public function testSend()
    {
        $content = 'test';
        $contentType = 'test/test';
        $headers = array();
        $statusCode = 42;

        try {
            $this->controller->send($content, $contentType, $headers, $statusCode);
        } catch (\Oft\Mvc\Exception\HttpException $e) {
            $headers = $e->getHeaders();
            
            $this->assertEquals($content, $e->getContent());
            $this->assertEquals($statusCode, $e->getStatusCode());
            $this->assertEquals($contentType, $headers['Content-Type']);
            return;
        }

        $this->fail();
    }

    public function testSetLayoutTemplate()
    {
        $layoutTemplateName = 'template';
        $layoutTemplatePath = 'template/path';

        $app = ApplicationMock::factory();
        $viewModel = new Model();

        $this->controller->setApplication($app);
        $this->controller->setViewModel($viewModel);

        $this->controller->setLayoutTemplate($layoutTemplateName, $layoutTemplatePath);

        $this->assertEquals($layoutTemplateName, $app->renderOptions->layoutTemplateName);
        $this->assertEquals($layoutTemplatePath, $app->renderOptions->layoutTemplatePath);
    }

    public function testSetTemplate()
    {
        $viewTemplate = 'template';

        $app = ApplicationMock::factory();
        $viewModel = new Model();

        $this->controller->setApplication($app);
        $this->controller->setViewModel($viewModel);

        $this->controller->setTemplate($viewTemplate);

        $this->assertEquals($viewTemplate, $app->renderOptions->viewTemplate);
    }

    public function testSetRenderLayout()
    {
        $app = ApplicationMock::factory();

        $this->controller->setApplication($app);

        $this->controller->setRenderLayout(1);
        $this->assertTrue($app->renderOptions->renderLayout);

        $this->controller->setRenderLayout(0);
        $this->assertFalse($app->renderOptions->renderLayout);
    }

    public function testSetRenderView()
    {
        $app = ApplicationMock::factory();

        $this->controller->setApplication($app);

        $this->controller->setRenderView(1);
        $this->assertTrue($app->renderOptions->renderView);

        $this->controller->setRenderView(0);
        $this->assertFalse($app->renderOptions->renderView);
    }

    public function testCallUnknowAction()
    {
        $this->setExpectedException('Oft\Mvc\Exception\NotFoundException');

        $this->controller->unknownAction();
    }

    public function testCallUnknowMethod()
    {
        $this->setExpectedException('RuntimeException');

        $this->controller->unknownMethod();
    }
}
