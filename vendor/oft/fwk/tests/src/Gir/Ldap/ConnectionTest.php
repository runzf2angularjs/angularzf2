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

namespace Oft\Test\Ldap;

include_once __DIR__ . '/../../Mock/Functions/Ldap.php';

use Oft\Gir\Ldap;
use Oft\Gir\Ldap\Connection;
use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class ConnectionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException RuntimeException
     */
    public function testConnectExceptionNoHost()
    {
        $ldap = new Connection();
        $ldap->connect();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testConnectExceptionFailConnect()
    {
        $options = array(
            'host' => 'badhost'
        );

        $ldap = new Connection();
        $ldap->setOptions($options);

        $ldap->connect();
    }

    public function testConnectSsl()
    {
        $options = array(
            'host' => 'test',
            'useSsl' => true,
        );

        $ldap = new Connection();
        $ldap->setOptions($options);

        $resource = $ldap->connect();

        $this->assertTrue(is_resource($resource));
    }

    public function testGetResource()
    {
        $options = array(
            'host' => 'host',
        );

        $ldap = new Connection();
        $ldap->setOptions($options);

        $resource = $ldap->getResource();

        $this->assertTrue(is_resource($resource));
    }

    public function testBind()
    {
        $options = array(
            'host' => 'host',
        );

        $ldap = new Connection();
        $ldap->setOptions($options);

        $result = $ldap->bind();

        $this->assertTrue($result);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testBindException()
    {
        $options = array(
            'host' => 'host',
            'username' => 'badUsername',
        );

        $ldap = new Connection();
        $ldap->setOptions($options);

        $result = $ldap->bind();
        $this->assertTrue($result);
    }

    public function testSetOptions()
    {
        $config = array(
            'gir' => array(
                'active' => true,
                'ldap' => array(
                    'host' => 'host',
                    'port' => 3333,
                    'useSsl' => true,
                    'username' => 'username',
                    'password' => 'password',
                    'baseDn' => 'basedn',
                ),
            ),
        );

        $app = new Application($config);
        $ldap = new Ldap($app);

        $options = $ldap->getOptions();

        $this->assertEquals($options, $config['gir']['ldap']);
    }

    public function testSetOptionsEmpty()
    {
        $options = array();

        $ldap = new Connection();
        $ldap->setOptions($options);

        $optionsLdap = $ldap->getOptions();

        $this->assertEquals($optionsLdap['host'], null);
        $this->assertEquals($optionsLdap['port'], 0);
        $this->assertEquals($optionsLdap['useSsl'], false);
        $this->assertEquals($optionsLdap['username'], null);
        $this->assertEquals($optionsLdap['password'], null);
        $this->assertEquals($optionsLdap['baseDn'], null);
    }

    public function testDisconnect()
    {
        $options = array(
            'host' => 'host',
        );

        $ldap = new Connection();
        $ldap->setOptions($options);
        $ldap->connect();

        $result = $ldap->disconnect();

        $this->assertTrue($result);
    }

    public function testDisconnectNotARessource()
    {
        $ldap = new Connection();

        $result = $ldap->disconnect();

        $this->assertTrue($result);
    }

}
