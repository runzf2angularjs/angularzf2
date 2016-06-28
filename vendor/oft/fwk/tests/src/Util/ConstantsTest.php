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

namespace Oft\Util;

function definedMock($constant, $result)
{
    $GLOBALS['defined'][$constant] = $result;
}

function defined($constant)
{
    if (!isset($GLOBALS['defined'][$constant])) {
        return false;
    }
    return $GLOBALS['defined'][$constant];
}

function getenvMock($env, $result)
{
    $GLOBALS['getenv'][$env] = $result;
}

function getenv($env)
{
    if (!isset($GLOBALS['getenv'][$env])) {
        return false;
    }
    return $GLOBALS['getenv'][$env];
}

function defineMock($constant, $result)
{
    $GLOBALS['define'][$constant] = $result;
}

function define($constant, $value)
{
    $GLOBALS['define'][$constant] = $value;
}

function constant($constant)
{
    return $GLOBALS['define'][$constant];
}

namespace Oft\Test\Util;

class ConstantsTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $GLOBALS['defined'] = array();
        $GLOBALS['getenv'] = array();
        $GLOBALS['define'] = array();
    }

    protected function tearDown()
    {
        $GLOBALS['defined'] = array();
        $GLOBALS['getenv'] = array();
        $GLOBALS['define'] = array();
    }

    public function testDefineMainDoNothingIfAlreadySet()
    {
        \Oft\Util\definedMock('DATA_DIR', true);
        \Oft\Util\definedMock('APP_ENV', true);

        \Oft\Util\Constants::defineMain();

        $this->assertArrayNotHasKey('DATA_DIR', $GLOBALS['define']);
        $this->assertArrayNotHasKey('APP_ENV', $GLOBALS['define']);
    }

    public function testDefineMainDefineIfEnvIsSet()
    {
        \Oft\Util\definedMock('DATA_DIR', false);
        \Oft\Util\definedMock('APP_ENV', false);
        \Oft\Util\getenvMock('DATA_DIR', 'path/to/data');
        \Oft\Util\getenvMock('APP_ENV', 'someenv');

        \Oft\Util\Constants::defineMain();

        $this->assertArrayHasKey('DATA_DIR', $GLOBALS['define']);
        $this->assertSame('path/to/data', $GLOBALS['define']['DATA_DIR']);
        $this->assertArrayHasKey('APP_ENV', $GLOBALS['define']);
        $this->assertSame('someenv', $GLOBALS['define']['APP_ENV']);
    }

    public function testDefineMainDefineDefaultDataDir()
    {
        \Oft\Util\definedMock('DATA_DIR', false);
        \Oft\Util\definedMock('APP_ENV', true);
        \Oft\Util\getenvMock('DATA_DIR', false);
        if (!\defined('APP_ROOT')) {
            \Oft\Util\defineMock('APP_ROOT', 'path' . DIRECTORY_SEPARATOR . 'to');
        } else {
            \Oft\Util\defineMock('APP_ROOT', APP_ROOT);
        }
        
        \Oft\Util\Constants::defineMain();

        if (!\defined('APP_ROOT')) {
            $this->assertSame('path' . DIRECTORY_SEPARATOR . 'to' . DIRECTORY_SEPARATOR . 'data', $GLOBALS['define']['DATA_DIR']);
        } else {
            $this->assertSame(APP_ROOT . DIRECTORY_SEPARATOR . 'data', $GLOBALS['define']['DATA_DIR']);
        }
    }

    public function testDefineOther()
    {
        \Oft\Util\defineMock('DATA_DIR', 'data');
        if (!\defined('APP_ROOT')) {
            \Oft\Util\defineMock('APP_ROOT', 'approot');
        } else {
            \Oft\Util\defineMock('APP_ROOT', APP_ROOT);
        }
        
        \Oft\Util\Constants::defineOthers();

        $this->assertSame('data/tmp', $GLOBALS['define']['TEMP_DIR']);
        $this->assertSame('data/logs', $GLOBALS['define']['LOG_DIR']);
        $this->assertSame('data/cache', $GLOBALS['define']['CACHE_DIR']);
        $this->assertSame('data/upload', $GLOBALS['define']['UPLOAD_DIR']);

        if (!\defined('APP_ROOT')) {
            $this->assertSame('approot/public', $GLOBALS['define']['PUBLIC_DIR']);
        } else {
            $this->assertSame(APP_ROOT . '/public', $GLOBALS['define']['PUBLIC_DIR']);
        }

        $this->assertSame(DIRECTORY_SEPARATOR, $GLOBALS['define']['DS']);
        $this->assertSame(PATH_SEPARATOR, $GLOBALS['define']['PS']);
    }
}
