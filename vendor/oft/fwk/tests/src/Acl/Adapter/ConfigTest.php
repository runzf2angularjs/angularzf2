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

namespace Oft\Test\Acl\Adapter;

use Oft\Acl\Adapter\Config;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Config
     */
    protected $adapterConfig;
    protected $config;

    protected function setUp()
    {
        $this->config = array(
            'roles' => array(
                'users', 'moderators',
            ),
            'allow' => array(
                'mvc.res1', 'mvc.res2'
            )
        );

        $appConfig = array('acl' => array('adapter' => array('params' => array('permissions' => $this->config))));
        $app = new \Oft\Mvc\Application($appConfig);

        $this->adapterConfig = new Config($app);
    }

    public function testGetRoles()
    {
        $roles = $this->adapterConfig->getRoles();

        $this->assertEquals($this->config['roles'], $roles);
    }

    public function testGetAllowed()
    {
        $allowed = $this->adapterConfig->getAllowed();

        $this->assertEquals($this->config['allow'], $allowed);
    }

}
