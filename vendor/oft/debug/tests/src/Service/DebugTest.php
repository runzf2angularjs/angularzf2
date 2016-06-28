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

namespace Oft\Test\Debug\Service;

use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DebugBar;
use Exception;
use Oft\Debug\Service\Debug;
use PHPUnit_Framework_TestCase;

class DebugTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Debug
     */
    protected $service;

    protected function setUp()
    {
        $this->service = new Debug;
    }

    protected function tearDown()
    {
        $this->service = null;
    }

    public function testAddException()
    {
        $debugBar = new DebugBar();
        $debugBar->addCollector(new ExceptionsCollector());

        $exception = new Exception('message-test');

        $this->service->setDebugBar($debugBar);
        $this->service->addException($exception);

        $exceptions = $debugBar['exceptions']->getExceptions();

        $this->assertEquals($exceptions[0], $exception);
    }

    public function testAddMessage()
    {
        $debugBar = new DebugBar();

        $this->service->setDebugBar($debugBar);
        $this->service->addMessage('message-test');

        $messages = $debugBar['messages']->getMessages();

        $this->assertEquals($messages[0]['message'], 'message-test');
    }

    public function testIsDebug()
    {
        $this->assertTrue($this->service->isDebug());
    }

    public function testDump()
    {
        $var = 'my-var-to-dump';
        $title = 'dump-title';
        $return = true;

        $dump = $this->service->dump($var, $title, $return);

        $this->assertStringStartsWith('<strong>' . $title . '</strong>', $dump);
        $this->assertTrue(strpos($dump, $title) !== false);
    }

    public function testDumpEcho()
    {
        $var = 'my-var-to-dump';
        $title = 'dump-title';
        $return = false;

        ob_start();
        $dump = $this->service->dump($var, $title, $return);
        $buffer = ob_get_contents();
        ob_end_clean();

        $this->assertNull($dump);
        $this->assertStringStartsWith('<strong>' . $title . '</strong>', $buffer);
        $this->assertTrue(strpos($buffer, $title) !== false);
    }

}
