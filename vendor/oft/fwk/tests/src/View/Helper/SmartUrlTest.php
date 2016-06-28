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

namespace Oft\Test\View\Helper;

class SmartUrlTest extends \PHPUnit_Framework_TestCase
{

    protected $application;
    protected $view;
    protected $url;
    
    public function setUp()
    {
        $this->application = new \Oft\Mvc\Application();
        $this->application->setService('Route', new \Oft\Mvc\Context\RouteContext());

        $this->view = new \Oft\View\View();
        $this->view->setApplication($this->application);

        $this->view->setHelperPluginManager(new \Zend\View\HelperPluginManager());
        
        $this->url = new \Oft\View\Helper\SmartUrl();
        $this->url->setView($this->view);
    }

    protected function initSmartUrlFromRoute($with1, $with2, $return)
    {
        $smartUrlFromRoute = \Mockery::mock('Oft\View\Helper\SmartUrlFromRoute');
        $smartUrlFromRoute->shouldReceive('setView')
            ->with($this->view);

        $smartUrlFromRoute->shouldReceive('__invoke')
            ->with($with1, $with2)
            ->andReturn($return);

        $this->view->getHelperPluginManager()->setService('smartUrlFromRoute', $smartUrlFromRoute);
    }

    public function testDefault()
    {
        $this->application->route->setDefault(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ));
        
        $this->application->route->setCurrent(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ), array());
                       
        $this->initSmartUrlFromRoute('default', array('module'=>'m','controller'=>'c','action'=>'a'), '/test/12');
        
        $this->assertEquals('/test/12', $this->url->__invoke());
    }

    public function testDefaultWithRouteName()
    {
        $this->application->route->setCurrent(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ), array());
        
        $this->initSmartUrlFromRoute('test', array(
            'module'=>'m',
            'controller'=>'c',
            'action'=>'a'), 
            '/test/12');
        
        $this->assertEquals('/test/12', $this->url->__invoke(null, null, null, array(), 'test'));
    }

    public function testMVCWithRouteName()
    {
        $this->application->route->setDefault(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ));
        
        $this->application->route->setCurrent(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ), array());

        $this->initSmartUrlFromRoute('modules', array(
            'module' => 'm2',
            'controller' => 'c2',
            'action' => 'a2'), 
            '/test/12');
        
        $this->assertEquals('/test/12', $this->url->__invoke('a2', 'c2', 'm2'));
    }

    public function testParamsWithRouteName()
    {
        $this->application->route->setDefault(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ));
        
        $this->application->route->setCurrent(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ), array());

        $this->initSmartUrlFromRoute('default', array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a',
            'param' => 'value'), 
            '/test/12');
        
        $this->assertEquals('/test/12', $this->url->__invoke(null, null, null, array('param' => 'value')));
    }
    
    public function testSmartUrlDefautModuleNotInRoute()
    {
        $this->application->route->setDefault(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ));
        
        $this->application->route->setCurrent(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ), array());

        $this->initSmartUrlFromRoute('modules', array(
            'module' => 'm1',
            'controller' => 'c1',
            'action' => 'a1'), 
            '/test/12');
        
        $this->assertEquals('/test/12', $this->url->__invoke('a1', 'c1', 'm1'));
    }
    
    public function testSmartUrlDefautModuleInRoute()
    {
        $this->application->route->setDefault(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ));
        
        $this->application->route->setCurrent(array(
            'name' => 'route'
        ), array());

        $this->initSmartUrlFromRoute('modules', array(
            'module' => 'm1',
            'controller' => 'c1',
            'action' => 'a1'), 
            '/test/12');
        
        $this->assertEquals('/test/12', $this->url->__invoke('a1', 'c1', 'm1'));
    }
    
    public function testSmartUrlNotInDefautModuleNotInRoute()
    {
        $this->application->route->setDefault(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ));
        
        $this->application->route->setCurrent(array(
            'module' => 'm1',
            'controller' => 'c1',
            'action' => 'a1'
        ), array());

        $this->initSmartUrlFromRoute('modules', array(
            'module' => 'm1',
            'controller' => 'c',
            'action' => 'a'), 
            '/test/12');
        
        $this->assertEquals('/test/12', $this->url->__invoke('a', 'c'));
    }
    
    public function testSmartUrlNotInDefautModuleInRoute()
    {
        $this->application->route->setDefault(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ));
        
        $this->application->route->setCurrent(array(
            'module' => 'm1',
            'controller' => 'c1',
            'action' => 'a1',
            'name' => 'test'
        ), array());

        $this->initSmartUrlFromRoute('modules', array(
            'module' => 'm1',
            'controller' => 'c',
            'action' => 'a'), 
            '/test/12');
        
        $this->assertEquals('/test/12', $this->url->__invoke('a', 'c'));
    }
    
    public function testSmartUrlUnsetParamsModuleControllerAction()
    {
        $this->application->route->setDefault(array(
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a'
        ));
        
        $this->application->route->setCurrent(array(
            'module' => 'm1',
            'controller' => 'c1',
            'action' => 'a1',
        ), array());

        $this->initSmartUrlFromRoute('modules', array(
            'module' => 'm1',
            'controller' => 'c1',
            'action' => 'a1'), 
            '/test/12');
        
        $this->assertEquals('/test/12', $this->url->__invoke(null, null, null, array(
            'module' => 'm2',
            'controller' => 'c2',
            'action' => 'a2',
        )));
    }

}
