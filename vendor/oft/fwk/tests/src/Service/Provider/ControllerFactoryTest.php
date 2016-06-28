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

namespace Test_MyMock\Controller;

    class My_TestIndexController
    {

        public function myIndexAction()
        {
            
        }

    }


namespace Oft\Test\Service\Provider;

use Oft\Mvc\Application;
use Oft\Service\Provider\ControllerFactory;
use PHPUnit_Framework_TestCase;

class ControllerFactoryTest extends PHPUnit_Framework_TestCase
{
    /** @var ControllerFactory */
    protected $controllerFactory;
    /** @var ModuleManager */
    protected $moduleManager;

    protected function setUp()
    {
        $controllerFactoryProvider = new ControllerFactory();
        $moduleManager = \Mockery::mock('Oft\Module\ModuleManager');
        $app = new Application(array(), $moduleManager);
        $controllerFactory = $controllerFactoryProvider->create($app);

        $this->controllerFactory = $controllerFactory;
        $this->moduleManager = $moduleManager;
    }

    public function testGetControllerClass()
    {
        $this->moduleManager->shouldReceive('getModuleNamespace')
            ->with('oft')
            ->andReturn('Oft');
            
        $route = array(
            'module' => 'oft',
            'controller' => 'index',
        );
        $className = $this->controllerFactory->getControllerClass($route);
        $this->assertSame('Oft\\Controller\\IndexController', $className);
    }

    public function testGetControllerClassWithDash()
    {
        $this->moduleManager->shouldReceive('getModuleNamespace')
            ->with('oft-ihm')
            ->andReturn('OftIhm');
        
        $controllerFactory = $this->controllerFactory;

        $route = array(
            'module' => 'oft-ihm',
            'controller' => 'my-index',
        );
        $className = $controllerFactory->getControllerClass($route);
        $this->assertSame('OftIhm\\Controller\\MyIndexController', $className);
    }

    public function testGetControllerClassWithUnderscore()
    {
        $this->moduleManager->shouldReceive('getModuleNamespace')
            ->with('oft_ihm')
            ->andReturn('Oft_Ihm');
        
        $controllerFactory = $this->controllerFactory;

        $route = array(
            'module' => 'oft_ihm',
            'controller' => 'my_index',
        );
        $className = $controllerFactory->getControllerClass($route);
        $this->assertSame('Oft_Ihm\\Controller\\My_IndexController', $className);
    }

    public function testGetActionMethodSimple()
    {
        $controllerFactory = $this->controllerFactory;

        $route = array(
            'action' => 'index',
        );

        $actionName = $controllerFactory->getActionMethod($route);

        $this->assertSame('indexAction', $actionName);
    }

    public function testGetActionMethodDash()
    {
        $controllerFactory = $this->controllerFactory;

        $route = array(
            'action' => 'my-index',
        );

        $actionName = $controllerFactory->getActionMethod($route);

        $this->assertSame('myIndexAction', $actionName);
    }

    public function testGetActionMethodUnderscore()
    {
        $controllerFactory = $this->controllerFactory;

        $route = array(
            'module' => 'test_mock',
            'controller' => 'my_index',
            'action' => 'my_index',
        );

        $actionName = $controllerFactory->getActionMethod($route);

        $this->assertSame('my_IndexAction', $actionName);
    }

    public function testCreateFromRoute()
    {
        $this->moduleManager->shouldReceive('getModuleNamespace')
            ->with('test_my-mock')
            ->andReturn('Test_MyMock');
        
        $controllerFactory = $this->controllerFactory;

        $route = array(
            'module' => 'test_my-mock',
            'controller' => 'my_test-index',
            'action' => 'my-index',
        );

        $instance = $controllerFactory->createFromRoute($route);
        
        $this->assertInternalType('array', $instance);
        $this->assertInstanceOf('Test_MyMock\\Controller\\My_TestIndexController', $instance[0]);
        $this->assertSame('myIndexAction', $instance[1]);
    }
    
    public function testGetInstanceThrowsExceptionIfClassDoesNotExists()
    {
        $this->setExpectedException(
            'Oft\Mvc\Exception\NotFoundException',
            "Controller class is not defined"
        );
        
        $this->moduleManager->shouldReceive('getModuleNamespace')
            ->with('noModule')
            ->andReturn('noModule');

        $controllerFactory = $this->controllerFactory;
        
        $route = array(
            'module' => 'noModule',
            'controller' => 'noCtrl',
            'action' => 'noAction',
        );
        
        $controllerFactory->getControllerInstance($route);
    }
    
    public function testGetInstanceThrowsExceptionIsActionIsNotAMethod()
    {
        $this->setExpectedException(
            'Oft\Mvc\Exception\NotFoundException',
            'Action does not exists on that controller'
        );

        $this->moduleManager->shouldReceive('getModuleNamespace')
            ->with('test_my-mock')
            ->andReturn('Test_MyMock');

        $controllerFactory = $this->controllerFactory;
        
        $route = array(
            'module' => 'test_my-mock',
            'controller' => 'my_test-index',
            'action' => 'my-index-that-does-not-exists',
        );
        
        $controllerFactory->createFromRoute($route);
    }
}
