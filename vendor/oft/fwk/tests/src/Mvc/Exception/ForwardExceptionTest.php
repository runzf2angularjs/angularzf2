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

namespace Oft\Test\Mvc\Exception;

class ForwardExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $exception = new \Oft\Mvc\Exception\ForwardException(array('c' => 'c'), array('p' => 'v'));
        
        $this->assertSame(array('c' => 'c'), $exception->getRoute());
        $this->assertSame(array('p' => 'v'), $exception->getParams());
    }
}
