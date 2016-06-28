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

namespace Oft\Test\Auth\IdentityStore;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function getStore($users = array())
    {
        $config = array(
            'auth' => array(
                'store' => array(
                    'params' => array(
                        'users' => $users
                    )
                )
            )
        );
        
        $app = new \Oft\Mvc\Application($config);

        return new \Oft\Auth\IdentityStore\Config($app);
    }

    public function testConstructDontFailIfEmpty()
    {
        $users = array();
        $store = $this->getStore($users);
        $identities = $store->getIdentityList();

        $this->assertSame($users, $identities);

    }

    public function testGetIdentity()
    {
        $data = array(
            'username' => 'ADMI1234',
            'password' => 'password'
        );

        $users = include __DIR__ . '/../_files/users.php';

        $store = $this->getStore($users);

        $identity = $store->getIdentity('admi1234');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);

        $this->assertEquals(strtoupper($data['username']), strtoupper($identity->getUsername()));
        $this->assertNotEmpty($identity->getGroups());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Utilisateur inconnu
     */
    public function testGetIdentityIfNotExists()
    {
        $users = include __DIR__ . '/../_files/users.php';
        
        $store = $this->getStore($users);
        
        $store->getIdentity('doesnotexists');
    }

    public function testGetIdentityList()
    {
        $users = include __DIR__ . '/../_files/users.php';
        $expected = array();
        foreach ($users as $username => $userdata) {
            $expected[strtolower($username)] = $userdata;
        }

        $store = $this->getStore($users);

        $list = $store->getIdentityList();

        $this->assertEquals($expected, $list);
    }
}
