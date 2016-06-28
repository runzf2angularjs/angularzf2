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

namespace Oft\Mvc\Middleware {

    function oft_trace()
    {

    }

}

namespace Oft\Test\Mvc\Middleware {

use Mockery;
use Mockery\MockInterface;
use Oft\Mvc\Application;
use Oft\Mvc\Exception\ForwardException;
use Oft\Mvc\Exception\RedirectException;
use Oft\Mvc\Middleware\Acl;
use Oft\Test\Mock\ApplicationMock;
use PHPUnit_Framework_TestCase;
use stdClass;

    class AclTest extends PHPUnit_Framework_TestCase
    {

        /**
         * @var Application
         */
        protected $app;

        /**
         * @var MockInterface
         */
        protected $nextMiddleware;

        protected function getAcl($isMvcAllowed = true, $config = array(), $identity = array())
        {
            $this->nextMiddleware = Mockery::mock('Oft\Mvc\MiddlewareAbstract');

            // Authorisé ou pas ?
            $aclMock = \Mockery::mock('Oft\Acl\Acl');
            $aclMock->shouldReceive('isMvcAllowed')
                ->andReturn($isMvcAllowed);

            $this->app = ApplicationMock::factory($config, $identity);
            $this->app->setService('Acl', $aclMock);

            $this->stdClass = new stdClass;
            $this->app->http->session->shouldReceive('getContainer')
                ->andReturn($this->stdClass);

            $this->app->http->request->shouldReceive('getBaseUrl')
                ->andReturn('/path/to/docroot');

            $this->app->http->request->shouldReceive('getFromServer')
                ->with('REQUEST_URI')
                ->andReturn('/path/to/page');

            $acl = new Acl();
            $acl->setNextMiddleware($this->nextMiddleware);

            return $acl;
        }

        public function testIsAllowedValid()
        {
            $acl = $this->getAcl(true);

            $this->assertTrue($acl->isAllowed($this->app));
        }

        public function testIsAllowedInValid()
        {
            $acl = $this->getAcl(false);

            $this->assertFalse($acl->isAllowed($this->app));
        }

        public function testAuthRedirect()
        {
            $acl = $this->getAcl();

            try {
                $acl->authRedirect($this->app);
            } catch (RedirectException $e) {
                $headers = $e->getHeaders();
                $this->assertSame('/path/to/docroot/auth/login', $headers['Location']);
                $this->assertSame('/path/to/page', $this->stdClass->authRedirectUrl);
                return;
            }
            $this->fail('an exception should be thrown');
        }

        public function testCallFailWithGuest()
        {
            $this->setExpectedException('Oft\Mvc\Exception\RedirectException');

            $acl = $this->getAcl(false);

            $this->nextMiddleware
                ->shouldReceive('call')
                ->never();

            $acl->call($this->app);
        }

        public function testCallFailWithNotGuest()
        {
            $acl = $this->getAcl(false, array('maxForward' => 10), array('username' => 'notguest'));

            $this->app->route->setCurrent(array('name' => 'dumb'));
            $this->nextMiddleware
                ->shouldReceive('call')
                ->once();

            $acl->call($this->app);

            $this->assertArrayHasKey('type', $this->app->route->params);
            $this->assertSame('noRight', $this->app->route->params['type']);
            $this->assertArrayHasKey('message', $this->app->route->params);
        }

        public function testCallSuccess()
        {
            $acl = $this->getAcl(true, array('maxForward' => 1));

            $this->nextMiddleware
                ->shouldReceive('call')
                ->once();

            $acl->call($this->app);
        }

        public function testCallWithForwardAndTooManyForward()
        {
            $this->setExpectedException('RuntimeException', 'Too many forward');

            $acl = $this->getAcl(true, array('maxForward' => 1));

            $this->app->route->setCurrent(array('name' => 'dumb'));

            $this->nextMiddleware
                ->shouldReceive('call')
                ->andThrow(new ForwardException(array('name' => 'forwarded')))
                ->twice();

            $acl->call($this->app);

            $this->assertArrayHasKey('name', $this->app->route->current);
            $this->assertSame('forwarded', $this->app->route->current['name']);
        }

        public function testWithAssetsRouteNames()
        {
            $acl = $this->getAcl(false);

            $this->app->route->setCurrent(array('name' => 'someName'));
            $this->assertFalse($acl->isAllowed($this->app));

            $this->app->route->setCurrent(array('name' => 'assets'));
            $this->assertTrue($acl->isAllowed($this->app));

            $this->app->route->setCurrent(array('name' => 'assets.file'));
            $this->assertTrue($acl->isAllowed($this->app));
        }
    }


}