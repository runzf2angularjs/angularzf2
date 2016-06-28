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

namespace Oft\Test\View;

use Mockery;
use Oft\View\View;
use PHPUnit_Framework_TestCase;

class ViewTest extends PHPUnit_Framework_TestCase
{

    public function testSetViewPlugins()
    {
        $view = new View();

        $testHelper = \Mockery::mock('Zend\View\Helper\AbstractHelper');
        $testHelper->shouldReceive('setView')
            ->with($view)
            ->once();

        $helperPluginManager = new \Zend\View\HelperPluginManager();
        $helperPluginManager->setService('test', $testHelper);

        $view->setHelperPluginManager($helperPluginManager);

        $result = $view->plugin('test');

        $this->assertSame($testHelper, $result);
    }

    public function testGetEngine()
    {
        $view = new View();

        $this->assertSame($view, $view->getEngine());
    }

    public function testSetResolver()
    {
        $resolver = Mockery::mock('Zend\View\Resolver\ResolverInterface');
        $view = new View();

        $result = $view->setResolver($resolver);
        $this->assertSame($view, $result);
        $this->assertSame($resolver, $view->getResolver());
    }

    public function testRender()
    {
        $template = 'some/template';
        
        $viewModel = new \Zend\View\Model\ViewModel();
        $viewModel->setTemplate($template);
        
        $resolver = Mockery::mock('Zend\View\Resolver\ResolverInterface');
        $resolver->shouldReceive('resolve')
            ->with($template)
            ->andReturn(__DIR__ . '/_files/test-render.phtml');
        
        $view = new View();
        $view->setResolver($resolver);

        $result = $view->render($viewModel);

        $this->assertSame('result', $result);
    }

    public function testMagicIsset()
    {
        $view = new View();

        $template = 'test';

        // ViewModel
        $viewModel = new \Zend\View\Model\ViewModel();
        $viewModel->setVariable('var', 'test');
        $viewModel->setTemplate($template);

        // Mock resolver
        $resolver = \Mockery::mock('Oft\View\Resolver\DirectResolver');
        $resolver->shouldReceive('resolve')
            ->once()
            ->with($template)
            ->andReturn(__DIR__ . '/_files/test-isset.phtml'); // Fichier mock
        
        $view->setResolver($resolver);

        $result = $view->render($viewModel);

        $this->assertSame("var is 1\nvarNotSet is 0\nvar is 1\nvarNotSet is 0\nvarContent is test\nvar1Content is \n", $result);
    }
}
