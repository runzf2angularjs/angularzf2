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

namespace Oft\Test\Controller;

use Oft\Controller\UserController;
use Oft\Mvc\Exception\RedirectException;
use Oft\Test\Mock\ApplicationMock;
use Oft\View\Model;
use PHPUnit_Framework_TestCase;

class UserControllerTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $_GET = array();
    }

    protected function tearDown()
    {
        $_GET = array();
    }

    public function testRenderActionWhenModified()
    {
        $controller = new UserController();

        $viewModel = new Model();
        $app = ApplicationMock::factory();

        $app->http->request->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('');

        $app->http->response->shouldReceive('setCookie')
            ->once()
            ->with('lang', 'fr');

        $controller->setApplication($app);
        $controller->setViewModel($viewModel);

        try {
            $_GET['redirect'] = '/url';
            $controller->languageAction('fr');
        } catch (RedirectException $e) {
            $headers = $e->getHeaders();
            $this->assertInternalType('array', $headers);
            $this->assertArrayHasKey('Location', $headers);
            $this->assertEquals('/url', $headers['Location']);
            return;
        }
        $this->fail();
    }

    public function testWithInvalidRedirect()
    {
        $controller = new UserController();

        $viewModel = new Model();
        $app = ApplicationMock::factory(array('debug' => true));

        \Oft\View\Helper\FlashMessenger::setMessageContainer(new \ArrayObject());

        $app->http->request->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('');

        $app->http->response->shouldReceive('setCookie')
            ->once()
            ->with('lang', 'fr');

        $controller->setApplication($app);
        $controller->setViewModel($viewModel);

        try {
            $_GET['redirect'] = '/<script>alert(\'XSS\');</script>';
            $controller->languageAction('fr');
        } catch (RedirectException $e) {

            $headers = $e->getHeaders();
            $this->assertInternalType('array', $headers);
            $this->assertArrayHasKey('Location', $headers);
            $this->assertEquals('/', $headers['Location']);

            $container = \Oft\View\Helper\FlashMessenger::getMessagesContainer()->getArrayCopy();
            $this->assertCount(1, $container);

            $message = array_shift($container);
            $this->assertContains('warning', $message);

            return;
        }
        $this->fail();
    }
}
