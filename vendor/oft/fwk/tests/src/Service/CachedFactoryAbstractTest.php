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

namespace Oft\Test\Service;

use Mockery;
use Oft\Service\CachedFactoryAbstract;
use Oft\Service\ServiceLocatorInterface;
use PHPUnit_Framework_TestCase;

class CachedFactory extends CachedFactoryAbstract
{

    protected $doCreateReturn;

    public function __construct($doCreateReturn)
    {
        $this->doCreateReturn = $doCreateReturn;
    }

    public function doCreate(ServiceLocatorInterface $app)
    {
        return $this->doCreateReturn;
    }

    public function __call($methodName, $parameters)
    {
        $reflection = new \ReflectionClass(get_class($this));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this, $parameters);
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }

}

class Unserializable
{

    public function __sleep()
    {
        throw new \ErrorException('cant be serialized !');
    }

}

class CachedFactoryAbstractTest extends PHPUnit_Framework_TestCase
{

    protected $file;

    protected function setUp()
    {
        $this->file = __DIR__ . '/_files/' . str_replace('\\', '-', 'Oft\Test\Service\CachedFactory') . '.ser';

        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    protected function tearDown()
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /** @return \Oft\Mvc\Application */
    protected function getApp($isDebug)
    {
        $config = array(
            'debug' => $isDebug,
            'cache' => array(
                'dir' => __DIR__ . '/_files'
            )
        );
        return new \Oft\Mvc\Application($config);
    }

    protected function getCachedFactory($isDebug, $doCreateReturn = null)
    {
        $cachedFactory = new CachedFactory($doCreateReturn);

        $cachedFactory->isDebug = $isDebug;
        $cachedFactory->cacheFileName = $this->file;

        return $cachedFactory;
    }

    public function testGetCacheDataReturnFalseIfDebug()
    {
        $cachedFactory = $this->getCachedFactory(true);
        
        $result = $cachedFactory->getCacheData();

        $this->assertFalse($result);
    }

    public function testGetCacheDataReturnFalseIfFileDoesNotExists()
    {
        $cachedFactory = $this->getCachedFactory(false);
        $result = $cachedFactory->getCacheData(false);

        $this->assertFalse($result);
    }

    public function testGetCacheDataReturnFalseIfUnserializeFail()
    {
        file_put_contents($this->file, 'bad serialized data');

        $cachedFactory = $this->getCachedFactory(false);
        $result = $cachedFactory->getCacheData();

        $this->assertFalse($result);
        $this->assertFalse(file_exists($this->file), "Cache file may have been unlinked");
    }

    public function testGetCacheDataReturnData()
    {
        $data = array('key' => 'value');
        file_put_contents($this->file, serialize($data));

        $cachedFactory = $this->getCachedFactory(false);
        $result = $cachedFactory->getCacheData();

        $this->assertSame($data, $result);
    }

    public function testPutCacheDataReturnFalseIfDebug()
    {
        $cachedFactory = $this->getCachedFactory(true);

        $result = $cachedFactory->putCacheData('somethiiing');

        $this->assertFalse($result);
    }

    public function testPutCacheDataReturnFalseIfException()
    {
        $data = new Unserializable;
        $cachedFactory = $this->getCachedFactory(false);

        $result = $cachedFactory->putCacheData($data);

        $this->assertFalse($result);
    }

    public function testPutCacheDataReturnTrue()
    {
        $data = array('key' => 'value');
        $cachedFactory = $this->getCachedFactory(false);
        $result = $cachedFactory->putCacheData($data);

        $this->assertTrue($result);
        $this->assertSame(serialize($data), file_get_contents($this->file));
    }

    public function testCreateCallDoCreate()
    {
        $app  = $this->getApp(true);

        $cachedFactory = new CachedFactory('something');

        $result = $cachedFactory->create($app);

        $this->assertSame('something', $result);
    }

    public function testCreateReturnCache()
    {
        $app = $this->getApp(false);

        file_put_contents($this->file, serialize('something'));


        $cachedFactory = new CachedFactory(null);
        $cachedFactory->cacheFileName = $this->file;

        $result = $cachedFactory->create($app);

        $this->assertSame('something', $result);
    }

}
