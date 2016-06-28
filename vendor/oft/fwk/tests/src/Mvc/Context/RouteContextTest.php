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

namespace Oft\Test\Mvc\Context;

use Oft\Mvc\Context\RouteContext;

class RouteContextTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var RouteContext
     */
    protected $route;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->route = new RouteContext;
    }


    public function testConstruct()
    {
        $this->assertSame(array(), $this->route->current);
        $this->assertSame(array(), $this->route->params);
        $this->assertSame(array(), $this->route->previous);
        $this->assertSame(array(), $this->route->previousParams);
    }

    public function testSetCurrent()
    {
        $this->route
            ->setCurrent(array('name' => 'firstRoute'), array('p1' => 'v1'))
            ->setCurrent(array('name' => 'secondRoute'), array('p2' => 'v2'));

        $this->assertSame(array('name' => 'secondRoute'), $this->route->current);
        $this->assertSame(array('p2' => 'v2'), $this->route->params);
        $this->assertSame(array('name' => 'firstRoute'), $this->route->previous);
        $this->assertSame(array('p1' => 'v1'), $this->route->previousParams);
    }

    public function testGetParam()
    {
        $this->route->setParams(array(
            'p' => 'v',
        ));

        $this->assertSame('v', $this->route->getParam('p'));
        $this->assertSame('default', $this->route->getParam('p2', 'default'));
        $this->assertNull($this->route->getParam('p3'));
    }

}
