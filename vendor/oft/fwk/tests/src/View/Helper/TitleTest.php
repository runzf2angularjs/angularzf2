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

include_once __DIR__ . '/_files/functions.php';

class TitleTest extends \PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $appName = 'test';

        $app = new \Oft\Mvc\Application(array(
            'application' => array(
                'name' => $appName
            )
        ));

        $translator = new \Zend\I18n\Translator\Translator();
        
        $view = \Mockery::mock('Zend\View\Renderer\RendererInterface');
        $view->shouldReceive('application')
            ->andReturn($app);

        $view
            ->shouldReceive('headTitle')
            ->withArgs(array($appName, 'SET'))
            ->once();

        $title = new \Oft\View\Helper\Title();
        $title->setTranslator($translator);
        $title->setView($view);

        $title->setAppName($appName);

        $title->__invoke();
        $result = (string)$title;

        $this->assertEquals('<span>' . $appName . '</span>', $result);
    }

    public function testAddTitle()
    {
        $appName = 'test';
        $appTitle = 'test';

        $app = new \Oft\Mvc\Application(array(
            'application' => array(
                'name' => $appName
            )
        ));
        
        $view = new \Oft\View\View();
        $view->setApplication($app);
        $view->setHelperPluginManager(new \Zend\View\HelperPluginManager());

        $title = $appName . ' - ' . $appTitle;

        $expectedTitle = '<span>' . $title . '</span>';

        $headTitle = \Mockery::mock('Zend\View\Helper\HeadTitle');
        $headTitle->shouldReceive('setView');
        $headTitle->shouldReceive('__invoke')
            ->with($appName, 'SET');
        $headTitle->shouldReceive('__invoke')
            ->with($title, 'SET');
        $view->getHelperPluginManager()->setService('headTitle', $headTitle);

        $translator = new \Zend\I18n\Translator\Translator();
        
        $title = new \Oft\View\Helper\Title();
        $title->setTranslator($translator);
        $title->setView($view);

        $title->setAppName($appName);

        $title->__invoke($appTitle);
        $result = (string)$title;

        $this->assertEquals($expectedTitle, $result);
    }
}
