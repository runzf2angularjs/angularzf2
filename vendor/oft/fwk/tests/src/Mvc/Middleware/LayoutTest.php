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

namespace Oft\Mvc\Middleware;

use Mockery;
use Oft\Mvc\Application;
use Oft\Mvc\Context\RenderOptionsContext;
use Oft\Test\Mock\HttpContext;
use Oft\View\Model;
use PHPUnit_Framework_TestCase;

class LayoutTest extends PHPUnit_Framework_TestCase
{
    public function testCall()
    {
        $content = 'content';

        $viewModel = new Model();

        $app = new Application();
        $app->setService('RenderOptions', new RenderOptionsContext(array(
            'layoutTemplateName' => 'layout_default',
            'layoutTemplatePath' => 'layout_path',
            'viewModel' => $viewModel
        )));
        
        $app->setService('Http', new HttpContext());
        $app->http->response->shouldReceive('getContent')
            ->once()
            ->withNoArgs()
            ->andReturn($content);
        $app->http->response->shouldReceive('setContent')
            ->once()
            ->with($content)
            ->andReturnNull();

        $view = Mockery::mock('Oft\View\View');
        
        $view->shouldReceive('render')
            ->once()
            ->andReturn($content);

        $app->setService('View', $view);
        
        $nextMiddleware = Mockery::mock('Oft\Mvc\MiddlewareAbstract');
        $nextMiddleware->shouldReceive('call')
            ->once()
            ->with($app);
        
        $layout = new Layout();
        $layout->setNextMiddleware($nextMiddleware);
        
        $layout->call($app);
    }

}
