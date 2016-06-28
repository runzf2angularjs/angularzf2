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

namespace Oft\Test\Util;

use Oft\Util\Functions;
use PHPUnit_Framework_TestCase;

class FunctionsTest extends PHPUnit_Framework_TestCase
{

    public function testSetGetApp()
    {
       $app = new \Oft\Mvc\Application(array());

       Functions::setApp($app);
       $return = Functions::getApp();

       $this->assertSame($app, $return);
    }

}
