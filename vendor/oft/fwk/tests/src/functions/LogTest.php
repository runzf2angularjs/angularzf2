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

namespace Oft\Test\functions;

use Oft\Auth\Identity;
use Oft\Mvc\Application;
use Oft\Test\Mock\IdentityContext;
use Oft\Util\Functions;
use PHPUnit_Framework_TestCase;

class LogTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        \Monolog\Registry::clear();
    }

    public function testDebug()
    {        
        $message = 'test';
        $context = array('test');
        $channel = 'channel';

        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')
            ->once()
            ->withArgs(array(
                \Monolog\Logger::DEBUG,
                $message,
                $context
            ))
            ->andReturnNull();

        \Monolog\Registry::addLogger($logger, $channel);

        oft_debug($message, $context, $channel);
    }

    public function testInfo()
    {        
        $message = 'test';
        $context = array('test');
        $channel = 'channel';

        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')
            ->once()
            ->withArgs(array(
                \Monolog\Logger::INFO,
                $message,
                $context
            ))
            ->andReturnNull();

        \Monolog\Registry::addLogger($logger, $channel);

        oft_info($message, $context, $channel);
    }

    public function testNotice()
    {
        $message = 'test';
        $context = array('test');
        $channel = 'channel';

        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')
            ->once()
            ->withArgs(array(
                \Monolog\Logger::NOTICE,
                $message,
                $context
            ))
            ->andReturnNull();

        \Monolog\Registry::addLogger($logger, $channel);

        oft_notice($message, $context, $channel);
    }

    public function testWarning()
    {
        $message = 'test';
        $context = array('test');
        $channel = 'channel';

        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')
            ->once()
            ->withArgs(array(
                \Monolog\Logger::WARNING,
                $message,
                $context
            ))
            ->andReturnNull();

        \Monolog\Registry::addLogger($logger, $channel);

        oft_warning($message, $context, $channel);
    }

    public function testError()
    {
        $message = 'test';
        $context = array('test');
        $channel = 'channel';

        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')
            ->once()
            ->withArgs(array(
                \Monolog\Logger::ERROR,
                $message,
                $context
            ))
            ->andReturnNull();

        \Monolog\Registry::addLogger($logger, $channel);

        oft_error($message, $context, $channel);
    }

    public function testCritical()
    {
        $message = 'test';
        $context = array('test');
        $channel = 'channel';

        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')
            ->once()
            ->withArgs(array(
                \Monolog\Logger::CRITICAL,
                $message,
                $context
            ))
            ->andReturnNull();

        \Monolog\Registry::addLogger($logger, $channel);

        oft_critical($message, $context, $channel);
    }

    public function testEmergency()
    {
        $message = 'test';
        $context = array('test');
        $channel = 'channel';

        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')
            ->once()
            ->withArgs(array(
                \Monolog\Logger::EMERGENCY,
                $message,
                $context
            ))
            ->andReturnNull();

        \Monolog\Registry::addLogger($logger, $channel);

        oft_emergency($message, $context, $channel);
    }

    public function testExceptionNoPrevious()
    {
        $eMessage = 'test';
        $eCode = 475;

        $level = 888;
        $context = array();
        $channel = 'channel';

        $exception = new \Exception($eMessage, $eCode);

        $expectedMessage =
            "Exception '" . get_class($exception) . "' " .
            "with message '" . $exception->getMessage() . "'\n" .
            "Trace : \n" . $exception->getTraceAsString() . "\n";

        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')
            ->once()
            ->withArgs(array(
                $level,
                $expectedMessage,
                $context
            ))
            ->andReturnNull();

        \Monolog\Registry::addLogger($logger, $channel);

        oft_exception($exception, $context, $channel, $level);
    }

    public function testExceptionWithPreviousException()
    {
        $peMessage = 'test-exc-previous';
        $peCode = 1;

        $eMessage = 'test-exc';
        $eCode = 1;

        $level = 888;
        $context = array();
        $channel = 'channel';

        $previousException = new \Exception($peMessage, $peCode);
        $exception = new \Exception($eMessage, $eCode, $previousException);

        $expectedMessage =
            // Exception
            "Exception 'Exception' " .
            "with message '" . $eMessage . "'\n" .
            "Trace : \n" . $exception->getTraceAsString() . "\n" .
            // Previous Exception
            "Previous exception 'Exception' " .
            "with message '" . $peMessage . "'\n" .
            "Trace : \n" . $previousException->getTraceAsString() . "\n";

        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')
            ->once()
            ->withArgs(array(
                $level,
                $expectedMessage,
                $context
            ))
            ->andReturnNull();

        \Monolog\Registry::addLogger($logger, $channel);

        oft_exception($exception, $context, $channel, $level);
    }

    public function testTrace()
    {
        $username = 'ABCD1234';

        $message = 'test';
        $context = array('test');
        $level = 100;
        $channel = 'security'; // forced by oft_trace()

        $logger = \Mockery::mock('Monolog\Logger');
        $logger->shouldReceive('log')
            ->once()
            ->withArgs(array(
                $level,
                '[' . strtolower($username) . '] ' . $message,
                $context
            ))
            ->andReturnNull();

        \Monolog\Registry::addLogger($logger, $channel);
        
        $this->setAppWith($username);

        oft_trace($message, $context, $level);
    }

    protected function setAppWith($username)
    {
        $identityContext = new IdentityContext(
            new Identity(array('username' => $username))
        );

        $app = new Application();
        $app->setService('Identity', $identityContext);

        Functions::setApp($app);

        return $app;
    }

}
