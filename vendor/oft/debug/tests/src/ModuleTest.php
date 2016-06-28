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

namespace Oft\Debug\Test;

use Mockery;
use Monolog\Logger;
use Monolog\Registry;
use Oft\Debug\Module;
use Oft\Debug\Service\Debug;
use Oft\Debug\View\Helper\DebugBar;
use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;

class ModuleTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Module
     */
    protected $module;

    protected function setUp()
    {
        $this->module = new Module();
    }

    public function testGetName()
    {
        $this->assertSame('debug', $this->module->getName());
    }

    public function testGetConfig()
    {
        $config = $this->module->getConfig();
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('assets', $config);
        $this->assertInternalType('array', $config['assets']);
        $this->assertArrayHasKey('services', $config);
        $this->assertInternalType('array', $config['services']);
    }

    public function testGetViewDir()
    {
        $viewDir = $this->module->getDir('views');

        $this->assertTrue(is_string($viewDir));
        $this->assertContains('views', $viewDir);
    }
    
    public function testGetDir()
    {
        $dir = $this->module->getDir();
        
        $this->assertTrue(is_string($dir));
        $this->assertTrue(is_dir($dir));
    }

    public function testInit()
    {
        $app = new Application(array(
            'log' => array(
                'default' => array(
                    'filename' => 'file.log',
                    'format' => array(
                        'filename' => '{date}-{filename}',
                        'date' => 'Y-m-d',
                    ),
                    'level' => Logger::NOTICE,
                ),
            )
        ));

        $logger = new Logger('default');
        Registry::addLogger($logger);
        
        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('getConfiguration')
            ->once()
            ->withAnyArgs()
            ->andReturnSelf();
        $db->shouldReceive('setSQLLogger')
            ->once()
            ->withAnyArgs()
            ->andReturnSelf();
        $app->setService('Db', $db);

        $app->setService('Debug', new Debug());

        $view = Mockery::mock('Oft\View\View');
        $view->shouldReceive('debugBar')
            ->once()
            ->withNoArgs()
            ->andReturn(new DebugBar());
        $app->setService('View', $view);

        $this->module->init($app);
    }

}
