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

namespace Oft\Test\Mock {

    interface MockServiceInterface
    {

    }

    class MockService implements MockServiceInterface
    {

    }

    class MockServiceFactory implements \Oft\Service\FactoryInterface
    {

        public function create(\Oft\Service\ServiceLocatorInterface $app)
        {
            return new MockService();
        }

    }

    class MockServiceInvalidFactory implements \Oft\Service\FactoryInterface
    {

        public function create(\Oft\Service\ServiceLocatorInterface $app)
        {
            // No return is invalid
        }

    }

    class MockServiceWoIface
    {

    }

    class MockServiceFactoryBug484ExceptionInConstruct implements \Oft\Service\FactoryInterface
    {
        public function __construct()
        {
            throw new \RuntimeException('test-bug484-exception');
        }

        public function create(\Oft\Service\ServiceLocatorInterface $app)
        {
            return new stdClass;
        }
    }

    class MockServiceFactoryBug484ExceptionInCreate implements \Oft\Service\FactoryInterface
    {
        public function create(\Oft\Service\ServiceLocatorInterface $app)
        {
            throw new \RuntimeException('test-bug484-exception');
        }
    }

    class MockServiceBug484ExceptionInConstruct
    {
        public function __construct()
        {
            throw new \RuntimeException('test-bug484-exception');
        }
    }

}

namespace Oft\Test\Service {

    use Exception;
    use Oft\Mvc\Application;
    use Oft\Service\ServiceManager;
    use Oft\Test\Mock\MockService;
    use Oft\Test\Mock\MockServiceWoIface;
    use PHPUnit_Framework_TestCase;

    class ServiceManagerTest extends PHPUnit_Framework_TestCase
    {

        /** @var ServiceManager */
        protected $manager;

        protected function setUp()
        {
            $this->manager = new ServiceManager();
        }

        public function testAddDefinition()
        {
            $this->manager->addServiceDefinition('simple', 'Oft\Test\Mock\MockService');

            $this->assertTrue($this->manager->has('simple'));
            $this->assertFalse($this->manager->has('SIMPLE'));
        }

        public function testAddDefinitions()
        {
            $interfaces = array(
                'iface' => 'Oft\Test\Mock\MockServiceInterface'
            );

            $definitions = array(
                'simple' => 'Oft\Test\Mock\MockService',
                'iface' => 'Oft\Test\Mock\MockService'
            );

            $this->manager->addServicesInterfaces($interfaces);
            $this->manager->addServicesDefinitions($definitions);

            $this->assertTrue($this->manager->has('simple'));
            $this->assertFalse($this->manager->has('SIMPLE'));
            $this->assertTrue($this->manager->has('iface'));
        }

        public function testSetServiceSimple()
        {
            $this->manager->setService('test', new MockService());

            $this->manager->has('test');
            $this->manager->has('TEST');
        }

        public function testSetServiceOverwriteThrowException()
        {
            $this->setExpectedException('RuntimeException');

            $this->manager->setService('test', new MockService());
            $this->manager->setService('test', new MockService());
        }

        public function testSetServiceOverwriteOkIfSpecified()
        {
            $this->manager->setService('test', new MockService());
            $this->manager->setService('test', new MockService(), true);

            $this->manager->has('test');
            $this->manager->has('TEST');
        }

        public function testSetServiceWithInterfaceSetThrowException()
        {
            $this->setExpectedException('RuntimeException');

            $this->manager->addServiceDefinition('test', 'Oft\Test\Mock\MockService', 'Oft\Test\Mock\MockServiceInterface');

            $this->manager->setService('test', new MockServiceWoIface());
        }

        public function testSetServiceWithInterfaceSetDoesntThrowException()
        {
            $this->manager->addServiceDefinition('test', 'Oft\Test\Mock\MockService', 'Oft\Test\Mock\MockServiceInterface');

            $this->manager->setService('test', new MockService());

            $this->manager->has('test');
            $this->manager->has('TEST');
        }

        public function testAddInterfaceWithAlreadySetIface()
        {
            $this->setExpectedException('RuntimeException', 'That service\'s interface has already been defined');

            $this->manager->addServiceInterface('test', 'Oft\Test\Mock\MockServiceInterface');
            $this->manager->addServiceInterface('test', 'Oft\Test\Mock\MockServiceInterface');
        }

        public function testGet()
        {
            $this->manager->addServiceDefinition('test', 'Oft\Test\Mock\MockService');

            $instance1 = $this->manager->get('test');
            $instance2 = $this->manager->get('test');

            $this->assertSame($instance1, $instance2);
            $this->assertInstanceOf('Oft\Test\Mock\MockService', $instance1);
        }

        public function testGetThrowExceptionIfNotDefined()
        {
            $this->setExpectedException('RuntimeException', "No service defined with name 'DO NOT EXISTS'");

            $this->manager->get('DO NOT EXISTS');
        }

        public function testGetCheckIface()
        {
            $this->manager->addServiceDefinition('test', 'Oft\Test\Mock\MockService', 'Oft\Test\Mock\MockServiceInterface');

            $instance = $this->manager->get('test');

            $this->assertInstanceOf('Oft\Test\Mock\MockServiceInterface', $instance);
            $this->assertInstanceOf('Oft\Test\Mock\MockService', $instance);
        }

        public function testGetCheckIfaceThrowException()
        {
            $this->setExpectedException('RuntimeException', 'The service instance does not comply to the interface specified');

            $this->manager->addServiceDefinition('test', 'Oft\Test\Mock\MockServiceWoIface', 'Oft\Test\Mock\MockServiceInterface');

            $this->manager->get('test');
        }

        public function testGetThrowExceptionIfClassDoesntExists()
        {
            $this->setExpectedException('RuntimeException', "The service class 'NoNs\Class' does not exists");

            $this->manager->addServiceDefinition('test', 'NoNs\Class');

            $this->manager->get('test');
        }

        public function testGetWithFactory()
        {
            $this->manager->addServiceDefinition('test', 'Oft\Test\Mock\MockServiceFactory');

            $instance = $this->manager->get('test');

            $this->assertInstanceOf('Oft\Test\Mock\MockService', $instance);
        }

        public function testGetWithInvokable()
        {
            $this->manager->addServiceDefinition('test', 'Oft\Test\Mock\MockService');

            $instance = $this->manager->get('test');

            $this->assertInstanceOf('Oft\Test\Mock\MockService', $instance);
        }

        public function testGetWithInvalidFactory()
        {
            $this->setExpectedException('RuntimeException', 'Service instance is null');

            $this->manager->addServiceDefinition('test', 'Oft\Test\Mock\MockServiceInvalidFactory');

            $this->manager->get('test');
        }

        public function testHasFromDefinition()
        {
            $this->assertFalse($this->manager->has('test'));

            $this->manager->addServiceDefinition('test', 'Oft\Test\Mock\MockService');

            $this->assertTrue($this->manager->has('test'));
        }

        public function testHasFromInstances()
        {
            $this->assertFalse($this->manager->has('test'));

            $this->manager->addServiceDefinition('test', 'Oft\Test\Mock\MockService');

            $this->manager->get('test');

            $this->assertTrue($this->manager->has('test'));
        }

        public function testBug484CyclicDependencyFactoryWithExceptionInConstruct()
        {
            $this->manager->addServiceDefinition('cyclic-test', 'Oft\Test\Mock\MockServiceFactoryBug484ExceptionInConstruct');

            try {
                $exceptionCatched = false;
                $this->manager->get('cyclic-test');
            } catch (Exception $e) {
                $exceptionCatched = true;
            }

            $this->assertTrue($exceptionCatched, 'An exception must have been thrown');

            try {
                $this->manager->get('cyclic-test');
            } catch (\RuntimeException $e) {
                $this->assertSame('test-bug484-exception', $e->getMessage());

                return;
            }
            $this->fail('incorrect exception thrown');
        }

        public function testBug484CyclicDependencyFactoryWithExceptionInCreate()
        {
            $this->manager->addServiceDefinition('cyclic-test', 'Oft\Test\Mock\MockServiceFactoryBug484ExceptionInCreate');

            try {
                $exceptionCatched = false;
                $this->manager->get('cyclic-test');
            } catch (Exception $e) {
                $exceptionCatched = true;
            }

            $this->assertTrue($exceptionCatched, 'An exception must have been thrown');

            try {
                $this->manager->get('cyclic-test');
            } catch (\RuntimeException $e) {
                $this->assertSame('test-bug484-exception', $e->getMessage());

                return;
            }
            $this->fail('incorrect exception thrown');
        }

        public function testBug484CyclicDependencyWithExceptionInConstruct()
        {
            $this->manager->addServiceDefinition('cyclic-test', 'Oft\Test\Mock\MockServiceBug484ExceptionInConstruct');

            try {
                $exceptionCatched = false;
                $this->manager->get('cyclic-test');
            } catch (Exception $e) {
                $exceptionCatched = true;
            }

            $this->assertTrue($exceptionCatched, 'An exception must have been thrown');

            try {
                $this->manager->get('cyclic-test');
            } catch (\RuntimeException $e) {
                $this->assertSame('test-bug484-exception', $e->getMessage());

                return;
            }
            $this->fail('incorrect exception thrown');
        }

    }

}