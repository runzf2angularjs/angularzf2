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

namespace Oft\Test\Mvc\Middleware;

use Mockery;
use Oft\Mvc\Application;
use Oft\Mvc\Context\RenderOptionsContext;
use Oft\Mvc\Context\RouteContext;
use Oft\Mvc\Middleware\Render;
use Oft\Test\Mock\HttpContext;
use Oft\View\Model;
use PHPUnit_Framework_TestCase;

class RenderTest extends PHPUnit_Framework_TestCase
{
    public function testCallWithRenderBypass()
    {
        $viewModel = new Model();

        $app = new Application();
        $app->setService('Http', new HttpContext());
        $app->setService('RenderOptions', new RenderOptionsContext(array(
            'viewTemplate' => 'template',
            'renderView' => false,
            'viewModel' => $viewModel
        )));
        
        $nextMiddleware = Mockery::mock('\Oft\Mvc\MiddlewareAbstract');
        $nextMiddleware->shouldReceive('call')
            ->once()
            ->with($app);
        
        $render = new Render();
        $render->setNextMiddleware($nextMiddleware);
        
        $render->call($app);
    }

    public function testCallWithRenderAndTemplate()
    {
        $viewModel = new Model();
        $viewModel->var = 1;

        $app = new Application();
        $app->setService('Http', new HttpContext());
        $app->setService('RenderOptions', new RenderOptionsContext(array(
            'viewTemplate' => 'template',
            'renderView' => true,
            'viewModel' => $viewModel
        )));

        $view = \Mockery::mock('Oft\View\View');
        $view->shouldReceive('render')
            ->with('template', array('var' => 1))
            ->andReturn('rendered');
        $app->setService('View', $view);

        $app->http->response->shouldReceive('prependContent')
            ->once()
            ->with('rendered')
            ->andReturnNull();

        $nextMiddleware = Mockery::mock('\Oft\Mvc\MiddlewareAbstract');
        $nextMiddleware->shouldReceive('call')
            ->once()
            ->with($app);

        $render = new Render();
        $render->setNextMiddleware($nextMiddleware);

        $render->call($app);
    }

    public function testCallWithRenderAndNoTemplate()
    {
        $viewModel = new Model();
        $viewModel->var = 1;

        $app = new Application();
        $app->setService('Http', new HttpContext());
        $app->setService('RenderOptions', new RenderOptionsContext(array(
            'viewTemplate' => '',
            'renderView' => true,
            'viewModel' => $viewModel
        )));
        $app->setService('Route', new RouteContext(array(
            'current' => array(
                'module' => 'mod',
                'controller' => 'ctrl',
                'action' => 'act'
            )
        )));

        $view = \Mockery::mock('Oft\View\View');
        $view->shouldReceive('render')
            ->with('mod/ctrl/act', array('var' => 1))
            ->andReturn('rendered');
        $app->setService('View', $view);

        $app->http->response->shouldReceive('prependContent')
            ->once()
            ->with('rendered')
            ->andReturnNull();

        $nextMiddleware = Mockery::mock('\Oft\Mvc\MiddlewareAbstract');
        $nextMiddleware->shouldReceive('call')
            ->once()
            ->with($app);

        $render = new Render();
        $render->setNextMiddleware($nextMiddleware);

        $render->call($app);
    }

}
