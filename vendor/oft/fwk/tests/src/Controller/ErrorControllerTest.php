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

namespace Oft\Controller {

    class FunctionStorage
    {
        public static $storage = array();
    }

    function oft_exception($exception, $context, $channel)
    {
        FunctionStorage::$storage['exception'] = $exception;
        FunctionStorage::$storage['context'] = $context;
        FunctionStorage::$storage['channel'] = $channel;
    }

    function oft_trace($message, $context, $level)
    {
        FunctionStorage::$storage['message'] = $message;
        FunctionStorage::$storage['context'] = $context;
        FunctionStorage::$storage['level'] = $level;
    }
}

namespace Oft\Test\Controller {

use Exception;
use Oft\Controller\ErrorController;
use Oft\Controller\FunctionStorage;
use Oft\Mvc\Application;
use Oft\Test\Mock\ApplicationMock;
use Zend\View\Model\ViewModel;

    class MyErrorController extends ErrorController
    {

        public $appDebug = true;

        public function isDebug()
        {
            return $this->appDebug;
        }

    }

    class ErrorControllerTest extends \PHPUnit_Framework_TestCase
    {

        /** @var ErrorController */
        protected $error;

        /** @var Application */
        protected $app;

        /** @var ViewModel */
        protected $viewModel;

        /**
         * Sets up the fixture, for example, opens a network connection.
         * This method is called before a test is executed.
         */
        protected function setUp()
        {
            $this->error = new MyErrorController();

            $this->app = ApplicationMock::factory();
            $this->error->setApplication($this->app);

            $this->viewModel = new \Oft\View\Model();
            $this->error->setViewModel($this->viewModel);

            FunctionStorage::$storage = array();
        }

        public function testInitWithDefaultContentType()
        {
            $this->app->http->response->shouldReceive('getContentType')
                ->once()
                ->andReturn('text/html');
            $this->app->http->response->shouldReceive('setContentType')
                ->never()
                ->with('text/html');

            $this->error->init();

            $this->assertTrue($this->app->renderOptions->renderLayout);
            $this->assertTrue($this->app->renderOptions->renderView);
        }

        public function testInitWithOtherContentType()
        {
            $this->app->http->response->shouldReceive('getContentType')
                ->andReturn('some/type');
            $this->app->http->response->shouldReceive('setContentType')
                ->with('text/html');

            $this->error->init();

            $this->assertTrue($this->app->renderOptions->renderLayout);
            $this->assertTrue($this->app->renderOptions->renderView);
        }

        public function testErrorActionSimple()
        {
            $this->error->errorAction('type');

            $this->assertSame('type', $this->viewModel->type);
            $this->assertSame('Une erreur est survenue', $this->viewModel->message);
            $this->assertNull($this->viewModel->exception);
        }

        public function testErrorActionDomainException()
        {
            $this->app->http->response->shouldReceive('setStatusCode')
                ->with(500)
                ->once();

            $this->error->errorAction('type', new \DomainException('test'));

            $this->assertSame('DomainException', $this->viewModel->type);
            $this->assertSame('test', $this->viewModel->message);
            $this->assertInstanceOf('DomainException', $this->viewModel->exception);

            $this->assertInstanceOf('DomainException', FunctionStorage::$storage['exception']);
            $this->assertSame(array(), FunctionStorage::$storage['context']);
            $this->assertSame('security', FunctionStorage::$storage['channel']);
        }

        public function testErrorActionExceptionDebug()
        {
            $this->setExpectedException('InvalidArgumentException', 'test');
            
            $this->app->http->response->shouldReceive('setStatusCode')
                ->with(500)
                ->once();

            $this->error->errorAction('type', new \InvalidArgumentException('test'));
        }

        public function testErrorActionExceptionNoDebug()
        {
            $this->app->http->response->shouldReceive('setStatusCode')
                ->with(500)
                ->once();

            $this->error->appDebug = false;
            $this->error->errorAction('type', new Exception('test'));

            $this->assertSame('Erreur à l\'exécution', $this->viewModel->type);
            $this->assertSame('Une erreur est survenue qui nous empêche d\'afficher la page demandée', $this->viewModel->message);

            $this->assertInstanceOf('Exception', FunctionStorage::$storage['exception']);
            $this->assertSame(array(), FunctionStorage::$storage['context']);
            $this->assertSame('security', FunctionStorage::$storage['channel']);
        }

        public function testNotFoundAction()
        {
            $this->app->http->response->shouldReceive('setStatusCode')
                ->with(404)
                ->once();

            $this->app->route->setCurrent(array('previous'), array('previousParams'));
            $this->app->route->setCurrent(array('notFound'));

            $this->error->notFoundAction();

            $this->assertSame(array('previous'), $this->viewModel->route);
            $this->assertSame(array('previousParams'), $this->viewModel->routeParams);
        }

    }

}