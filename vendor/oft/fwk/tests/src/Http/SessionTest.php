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

namespace Oft\Test\Http;

use Oft\Http\Session;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

class MockSession extends \Symfony\Component\HttpFoundation\Session\Session
{
    public $isStarted;
    public $id;
    public $destroy;
    public $lifetime;

    public function __construct()
    {
        $this->isStarted = false;
        $this->id = 1;
    }

    public function start()
    {
        $this->isStarted = true;
    }

    public function invalidate($lifetime = null)
    {
        $this->isStarted = false;
        $this->id ++;
    }

    public function migrate($destroy = false, $lifetime = null)
    {
        $this->destroy = $destroy;
        $this->lifetime = $lifetime;
    }

}

class SessionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Session
     */
    protected $session;

    public function setUp()
    {
        $sessionStorage = new NativeSessionStorage();
        $session = new SymfonySession($sessionStorage);

        $this->session = new Session($session);
    }

    protected function tearDown() {
        $_SESSION = array();
    }

    public function testGetSessionObject()
    {
        $session = $this->session->getSessionObject();

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Session\Session', $session);
    }

    public function testStart()
    {
        $mockSession = new MockSession();

        $this->session = new Session($mockSession);

        $this->assertTrue(!$this->session->isStarted());

        $session = $this->session->start();

        $this->assertTrue($this->session->isStarted());

        $this->assertInstanceOf('\Oft\Http\Session', $session);
    }

    public function testDestroy()
    {
        $mockSession = new MockSession();

        $this->session = new Session($mockSession);

        $this->session->start();
        $session = $this->session->destroy();

        $this->assertTrue(!$this->session->isStarted());

        $this->assertInstanceOf('\Oft\Http\Session', $session);
    }

    public function testRegenerateId()
    {
        $mockSession = new MockSession();

        $this->session = new Session($mockSession);

        $session = $this->session->regenerateId();

        $this->assertEquals(false, $mockSession->destroy);

        $this->assertInstanceOf('Oft\Http\Session', $session);
    }

    public function testGetContainer()
    {
        $session = \Mockery::mock('Symfony\Component\HttpFoundation\Session\Session');
        $session->shouldReceive('has')
            ->once()
            ->with('test')
            ->andReturn(false);
        $session->shouldReceive('set')
            ->once()
            ->andReturn(true);
        $session->shouldReceive('get')
            ->once()
            ->with('test')
            ->andReturn('expected');

        $this->session = new Session($session);

        $result = $this->session->getContainer('test');

        $this->assertEquals('expected', $result);
    }

    public function testDropContainer()
    {
        $session = \Mockery::mock('Symfony\Component\HttpFoundation\Session\Session');

        $this->session = new Session($session);
        $session->shouldReceive('remove')
            ->once()
            ->with('test')
            ->andReturn(true);

        $this->session->dropContainer('test');
    }

}
