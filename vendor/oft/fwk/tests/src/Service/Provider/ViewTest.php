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

use Oft\Auth\Identity;
use Mockery;

class ViewTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $config = array(
            'view' => array(
                'helpers' => array(
                    'assets' => 'Oft\View\Helper\Assets',
                    'application' => 'Oft\View\Helper\Application',
                    'title' => 'Oft\View\Helper\Title',
                    'footer' => 'Oft\View\Helper\Footer',
                ),
                'factories' => array(),
                'config' => array(),
            ),
            'assets' => array(),
            'menu-bar' => array(array('name' => 'test')),
            'application' => array(
                'name' => 'test',
                'contact' => array(
                    'url' => 'http://test',
                    'name' => 'http://testName',
                )
            ),
        );
        
        $app = new \Oft\Mvc\Application();

        $app->setService('Http', new \Oft\Test\Mock\HttpContext());
        
        $app->http->request->shouldReceive('getBaseUrl')
            ->once()
            ->withNoArgs()
            ->andReturn('/path/to/root');

        $viewHelperPlugins = new \Zend\View\HelperPluginManager();

        $app->setService('ViewHelperPlugins', $viewHelperPlugins);
        
        $viewProvider = new \Oft\Service\Provider\View();
        
        /* @var $view \Oft\Mvc\View */
        $view = $viewProvider->create($app);

        $this->assertInstanceOf('Oft\View\View', $view);

        //$this->assertSame('/path/to/root', $view->basePath());
    }

}
