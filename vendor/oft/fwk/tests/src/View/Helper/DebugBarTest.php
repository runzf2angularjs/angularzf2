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

namespace Oft\Test\View\Helper;

class DebugBarTest extends \PHPUnit_Framework_TestCase
{
    protected $helper;
    
    protected function setUp()
    {
        $this->helper = new \Oft\View\Helper\DebugBar();
    }
    
    public function testInvoke()
    {
        $result = $this->helper->__invoke();
        
        $this->assertInstanceOf('\Oft\View\Helper\DebugBar', $result);
    }
    
    public function testToString()
    {
        $result = $this->helper->__toString();
        
        $this->assertEquals('', $result);
    }
}