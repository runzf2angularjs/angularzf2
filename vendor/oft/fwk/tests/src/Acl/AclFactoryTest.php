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

namespace Oft\Test\Acl;

class AclFactoryTest extends \PHPUnit_Framework_TestCase
{

    protected $aclConfig;

    protected function setUp()
    {
        $this->aclConfig = array(
            'roles' => array(
                'users',
                'moderators',
                // 'moderators' en double
                'moderators',
            ),
            'allow' => array(
                array(
                    'resources' => array(
                        'resource1',
                        'resource2',
                    ),
                    'roles' => array(
                        'moderators'
                    ),
                )
            )
        );
    }

    public function getApp()
    {
        $config = array(
            'acl' => array(
                'whitelist' => array()
            )
        );
        $app = new \Oft\Mvc\Application($config);

        $aclAdapterInterface = \Mockery::mock('Oft\Acl\Adapter\AdapterInterface');

        $aclAdapterInterface->shouldReceive('getRoles')
            ->once()
            ->withNoArgs()
            ->andReturn($this->aclConfig['roles']);

        $aclAdapterInterface->shouldReceive('getAllowed')
            ->once()
            ->withNoArgs()
            ->andReturn($this->aclConfig['allow']);
        
        $app->setService('AclStore', $aclAdapterInterface);

        $routerFactory = new \Aura\Router\RouterFactory();
        $router = $routerFactory->newInstance();
        $app->setService('Router', $router);

        return $app;
    }

    public function testCreateServiceDefaults()
    {
        $app = $this->getApp();

        $aclProvider = new \Oft\Acl\AclFactory($app);

        /* @var $acl \Zend\Permissions\Acl\Acl */
        $acl = $aclProvider->doCreate($app);

        $this->assertInstanceOf('Zend\Permissions\Acl\Acl', $acl);
        
        $this->assertTrue($acl->hasRole(\Oft\Auth\Identity::GUEST_GROUP));
        $this->assertTrue($acl->hasRole(\Oft\Auth\Identity::ADMIN_GROUP));
        $this->assertTrue($acl->hasRole('users'));
        $this->assertTrue($acl->hasRole('moderators'));
        
        $this->assertTrue($acl->hasResource('resource1'));
        $this->assertTrue($acl->hasResource('resource2'));
        
        $this->assertFalse($acl->isAllowed('users', 'resource1'));
        $this->assertFalse($acl->isAllowed('users', 'resource2'));
        $this->assertTrue($acl->isAllowed('moderators', 'resource1'));
        $this->assertTrue($acl->isAllowed('moderators', 'resource2'));
        
        $this->assertTrue($acl->isAllowed(\Oft\Auth\Identity::ADMIN_GROUP, 'resource1'));
        $this->assertTrue($acl->isAllowed(\Oft\Auth\Identity::ADMIN_GROUP, 'resource2'));
        
        $this->assertFalse($acl->isAllowed(\Oft\Auth\Identity::GUEST_GROUP, 'resource1'));
        $this->assertFalse($acl->isAllowed(\Oft\Auth\Identity::GUEST_GROUP, 'resource2'));
    }

}
