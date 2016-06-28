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

namespace Oft\Test\Service\Provider;

use Mockery;
use Oft\Mvc\Application;
use Oft\Service\Provider\ViewHelperPlugins;
use PHPUnit_Framework_TestCase;

class ViewHelperPluginsTest extends PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {        
        $app = new Application(array(
            'view' => array(
                'helpers' => array(
                    'assets' => 'AssetHelper',
                ),
                'helpersFactories' => array(
                    'menuBar' => 'MenuBarFactory'
                ),
                'helpersConfig' => array(
                ),
            ),
            'assets' => array('assets'),
            'application' => array(
                'name' => 'testapp',
                'contact' => array('testcontact')
            ),
            'footer-links' => array('testlinks'),
        ));

        $assets = Mockery::mock('Oft\View\Helper\Assets');
        $assets->shouldReceive('setConfiguration')
            ->with(array('assets'))
            ->once();

        $title = Mockery::mock('Oft\View\Helper\Title');
        $title->shouldReceive('setAppName')
            ->with('testapp')
            ->once();

        $footer = Mockery::mock('Oft\View\Helper\Footer');
        $footer->shouldReceive('setAppName')
            ->with('testapp')
            ->andReturn($footer)
            ->once();
        $footer->shouldReceive('setContact')
            ->with(array('testcontact'))
            ->andReturn($footer)
            ->once();
        $footer->shouldReceive('setLinks')
            ->with(array('testlinks'))
            ->andReturn($footer)
            ->once();

        $viewHelperPlugin = Mockery::mock('Zend\View\HelperPluginManager');
        $viewHelperPlugin->shouldReceive('setInvokableClass')
            ->with('assets', 'AssetHelper')
            ->once();
        $viewHelperPlugin->shouldReceive('setFactory')
            ->with('menuBar', 'MenuBarFactory')
            ->once();
        $viewHelperPlugin->shouldReceive('get')
            ->with('assets')
            ->andReturn($assets)
            ->once();
        $viewHelperPlugin->shouldReceive('get')
            ->with('title')
            ->andReturn($title)
            ->once();
        $viewHelperPlugin->shouldReceive('get')
            ->with('footer')
            ->andReturn($footer)
            ->once();
        $viewHelperPlugin->shouldReceive('setServiceLocator')
            ->with($app)
            ->andReturn($viewHelperPlugin)
            ->once();

        $viewHelperPluginService = new ViewHelperPlugins();
        $viewHelperPluginService->setViewHelperPlugin($viewHelperPlugin);

        $service = $viewHelperPluginService->create($app);

        $this->assertInstanceOf('Zend\View\HelperPluginManager', $service);
    }

}
 