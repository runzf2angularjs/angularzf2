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

namespace Oft\Test\Module;

use Oft\Module\ModuleManager;

class stdMock
{

    public function __toString()
    {
        return 'stdMock';
    }

}

class ModuleManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Oft\Module\ModuleManager
     */
    protected $moduleManager;

    protected function setUp()
    {
        $this->moduleManager = new ModuleManager;
    }

    protected function tearDown()
    {
        $this->moduleManager = null;
    }

    public function testAddModule()
    {
        $this->moduleManager->addModule('Oft\Test\Mock\Module');

        $this->assertArrayHasKey('oft-test', $this->moduleManager->getModules());
        $this->assertInstanceOf('\Oft\Test\Mock\Module\Module', $this->moduleManager->getModule('oft-test'));
    }

    public function testAddModuleInstance()
    {
        $this->moduleManager->addModule(new \Oft\Test\Mock\Module\Module());

        $this->assertArrayHasKey('oft-test', $this->moduleManager->getModules());
        $this->assertInstanceOf('\Oft\Test\Mock\Module\Module', $this->moduleManager->getModule('oft-test'));
    }

    public function testAddModuleInstanceThrowException()
    {
        $this->setExpectedException('RuntimeException');

        $this->moduleManager->addModule(new \Oft\Test\Mock\Module\Module());
        $this->moduleManager->addModule(new \Oft\Test\Mock\Module\Module());
    }

    public function testAddModules()
    {
        $modules = array(
            'Oft\Test\Mock\Module',
            'Oft\Test\Mock\Module2'
        );

        $this->moduleManager->addModules($modules);

        $this->assertArrayHasKey('oft-test', $this->moduleManager->getModules());
        $this->assertInstanceOf('\Oft\Test\Mock\Module\Module', $this->moduleManager->getModule('oft-test'));
        $this->assertArrayHasKey('oft-test2', $this->moduleManager->getModules());
        $this->assertInstanceOf('\Oft\Test\Mock\Module2\Module', $this->moduleManager->getModule('oft-test2'));
    }

    public function testInit()
    {
        $application = new \Oft\Mvc\Application();

        $moduleMock = \Mockery::mock('\Oft\Module\ModuleInterface');
        $moduleMock->shouldReceive('init')->with($application)->once();
        $moduleMock->shouldReceive('getName')->andReturn('oft-test')->once();

        $this->moduleManager->addModule($moduleMock);
        $this->moduleManager->init($application);
    }

    public function testInitOnce()
    {
        $application = new \Oft\Mvc\Application();

        $moduleMock = \Mockery::mock('\Oft\Module\ModuleInterface');
        $moduleMock->shouldReceive('init')->with($application)->once(); // Once
        $moduleMock->shouldReceive('getName')->andReturn('oft-test')->once();

        $this->moduleManager->addModule($moduleMock);
        $this->moduleManager->init($application);
        $this->moduleManager->init($application); // 2nd appel, 1 seule init
    }

    public function testGetNamespace()
    {
        $this->moduleManager->addModule('Oft\Test\Mock\Module');

        $moduleNs = $this->moduleManager->getModuleNamespace('oft-test');

        $this->assertSame('Oft\Test\Mock\Module', $moduleNs);
    }

    public function testGetEmptyNamespace()
    {
        $moduleMock = \Mockery::mock('\Oft\Module\ModuleInterface'); // => a non-namespaced class is generated
        $moduleMock->shouldReceive('getName')->once()->andReturn('oft-test');

        $this->moduleManager->addModule($moduleMock);

        $moduleNs = $this->moduleManager->getModuleNamespace('oft-test');

        $this->assertSame('', $moduleNs);
    }

    protected function getModuleInstanceMockForGetConfig($name, array $config)
    {
        $moduleMock = \Mockery::mock('\Oft\Module\ModuleInterface');
        $moduleMock->shouldReceive('getName')
            ->andReturn($name);
        $moduleMock->shouldReceive('getConfig')
            ->andReturn($config);

        return $moduleMock;
    }

    public function testGetModulesConfig()
    {
        $moduleMock = $this->getModuleInstanceMockForGetConfig('oft-test', array(
            'var' => 'value',
        ));
        $this->moduleManager->addModule($moduleMock);

        $moduleMock = $this->getModuleInstanceMockForGetConfig('oft-test2', array(
            'var2' => 'value2',
        ));
        $this->moduleManager->addModule($moduleMock);

        $cliMode = false;
        $config = $this->moduleManager->getModulesConfig($cliMode);

        $this->assertInternalType('array', $config);
    }

    public function testGetModuleConfigWithDefault()
    {
        $test = $this->getModuleInstanceMockForGetConfig('test', array(
            'first' => 'test'
        ));
        $test2 = $this->getModuleInstanceMockForGetConfig('test2', array(
            'first' => 'test2'
        ));

        $this->moduleManager->addModule($test, true);
        $this->moduleManager->addModule($test2);

        $cliMode = false;
        $config = $this->moduleManager->getModulesConfig($cliMode);

        $this->assertSame('test', $config['first']);
    }

    public function testFailIfNotModuleInterfaceInstanceIsAdded()
    {
        $this->setExpectedException('RuntimeException');

        $this->moduleManager->addModule(new stdMock());
    }

    public function testGetDefaultIsNull()
    {
        $this->assertNull($this->moduleManager->getDefault());

        $this->moduleManager->addModule('Oft\Test\Mock\Module');

        $this->assertNull($this->moduleManager->getDefault());
    }

    public function testGetDefaultIsNotNull()
    {
        $this->assertNull($this->moduleManager->getDefault());

        $this->moduleManager->addModule('Oft\Test\Mock\Module', true);

        $this->assertSame('oft-test', $this->moduleManager->getDefault());
    }

}
