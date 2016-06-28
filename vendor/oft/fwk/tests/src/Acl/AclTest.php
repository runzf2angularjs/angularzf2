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

use Aura\Router\RouteCollection;
use Aura\Router\RouteFactory;
use Oft\Acl\Acl;
use Oft\Auth\Identity;
use PHPUnit_Framework_TestCase;

class AclTest extends PHPUnit_Framework_TestCase
{
    public function getAcl(array $whitelist = array())
    {
        $routeCollection = new RouteCollection(new RouteFactory);
        return new Acl($routeCollection, $whitelist);
    }


    /**
     * @covers \Oft\Acl\Acl::getRouteName
     */
    public function testGetRouteNameWithName()
    {
        $acl = $this->getAcl();
        $route = array('name' => 'test');
        $this->assertSame('test', $acl->getRouteName($route));
    }

    /**
     * @covers \Oft\Acl\Acl::getRouteName
     */
    public function testGetRouteNameWithNoNameButModule()
    {
        $acl = $this->getAcl();
        $route = array('module' => 'test');
        $this->assertSame('modules', $acl->getRouteName($route));
    }

    /**
     * @covers \Oft\Acl\Acl::getRouteName
     */
    public function testGetRouteNameWithNoNameAndNoModule()
    {
        $acl = $this->getAcl();
        $route = array();
        $this->assertSame('default', $acl->getRouteName($route));
    }

    /**
     * @covers Oft\Acl\Acl::getRouteParams
     */
    public function testGetRouteParamsWithoutRoute()
    {
        $acl = $this->getAcl();
        $params = array('a' => 'b');

        $this->assertSame($params, $acl->getRouteParams($params));
    }

    /**
     * @covers Oft\Acl\Acl::getRouteParams
     */
    public function testGetRouteParamsWithRoute()
    {
        $acl = $this->getAcl();

        $routes = $acl->getRoutes();
        $routes->add('test-route', '/some/path');
        $routes['test-route']->setValues(array('a' => 'b'));
        
        $route = array('name' => 'test-route', 'c' => 'd');

        $this->assertSame(array('a' => 'b', 'name' => 'test-route', 'c' => 'd'), $acl->getRouteParams($route));
    }

    /**
     * @covers Oft\Acl\Acl::getInlineRoute
     */
    public function testGetInlineRoute()
    {
        $route = array(
            'module' => 'test-module',
            'controller' => 'test-controller',
            'action' => 'test-action',
        );
        $inlineRoute = array(
            'mvc.test-module.test-controller.test-action',
            'mvc.test-module.test-controller',
            'mvc.test-module',
        );

        $acl = $this->getAcl();

        $this->assertSame($inlineRoute, $acl->getInlineRoute($route));
    }

    /**
     * @covers Oft\Acl\Acl::isMvcAllowed
     */
    public function testIsMvcAllowedByAdminRole()
    {
        $route = array();
        $acl = $this->getAcl();
        $identity = new Identity(array('groups' => array(Identity::ADMIN_GROUP => 'admins')));
        $this->assertTrue($acl->isMvcAllowed($route, $identity));
    }

    /**
     * @covers Oft\Acl\Acl::isMvcAllowed
     * @covers Oft\Acl\Acl::isAllowedFromWhiteList
     */
    public function testIsMvcAllowedByWhiteList()
    {
        $route = array(
            'module' => 'test-module',
            'controller' => 'test-controller',
            'action' => 'test-action',
        );
        $route2 = array(
            'module' => 'test-module2',
            'controller' => 'test-controller',
            'action' => 'test-action',
        );
        $whitelist = array('mvc.test-module');
        $acl = $this->getAcl($whitelist);
        $identity = new Identity(array());

        $this->assertTrue($acl->isMvcAllowed($route, $identity));
        $this->assertFalse($acl->isMvcAllowed($route2, $identity));
    }
    /**
     * @covers Oft\Acl\Acl::isMvcAllowed
     * @covers Oft\Acl\Acl::isAllowedFromRoute
     */
    public function testIsMvcAllowedByRoute()
    {
        $route = array(
            'module' => 'test-module',
            'controller' => 'test-controller',
            'action' => 'test-action',
        );
        $acl = $this->getAcl();

        $acl->addRole('test-group');
        $acl->addResource('mvc.test-module.test-controller.test-action');
        $acl->allow('test-group', 'mvc.test-module.test-controller.test-action');

        $identity = new Identity(array(
            'groups' => array(
                'test-group' => 'grp'
            )
        ));
        $this->assertTrue($acl->isMvcAllowed($route, $identity));
    }
    /**
     * @covers Oft\Acl\Acl::isMvcAllowed
     */
    public function testIsMvcAllowedNotAllowed()
    {
        $route = array(
            'module' => 'test-module',
            'controller' => 'test-controller',
            'action' => 'test-action',
        );
        $acl = $this->getAcl();
        $identity = new Identity(array());

        $this->assertFalse($acl->isMvcAllowed($route, $identity));
    }
    
    public function testIsAllowedFromWhiteListWithOnlyRoute()
    {
        $route = array(
            'module' => 'test-module',
            'controller' => 'test-controller',
            'action' => 'test-action',
        );

        $whitelist = array('mvc.test-module');
        $acl = $this->getAcl($whitelist);

        $this->assertTrue($acl->isAllowedFromWhiteList($route));
    }
    
    public function testIsAllowedFromRouteWithOnlyRoute()
    {
        $route = array(
            'module' => 'test-module',
            'controller' => 'test-controller',
            'action' => 'test-action',
        );
        
        $route2 = array(
            'module' => 'test-module',
            'controller' => 'test-controller',
            'action' => 'test-action-refused',
        );
        
        $acl = $this->getAcl();

        $acl->addRole('test-group');
        $acl->addResource('mvc.test-module.test-controller.test-action');
        $acl->allow('test-group', 'mvc.test-module.test-controller.test-action');

        $identity = new Identity(array(
            'groups' => array(
                'test-group' => 'grp'
            )
        ));
        
        $this->assertTrue($acl->isAllowedFromRoute($route, $identity));
        $this->assertFalse($acl->isAllowedFromRoute($route2, $identity));
    }
    
    public function testGetRoutes()
    {
        $acl = $this->getAcl();
        
        $this->assertInstanceOf('\Aura\Router\RouteCollection', $acl->getRoutes());
    }
}
