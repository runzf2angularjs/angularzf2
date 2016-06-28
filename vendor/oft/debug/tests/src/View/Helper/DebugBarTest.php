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

namespace Oft\Debug\Test\View\Helper;

use DebugBar\DebugBar as MaximeBfDebugBar;
use Oft\Debug\View\Helper\DebugBar;
use PHPUnit_Framework_TestCase;

class DebugBarTest extends PHPUnit_Framework_TestCase
{

    public function testInvoke()
    {
        $debugBar = new MaximeBfDebugBar();

        $helper = new DebugBar();
        $helper->setDebugBar($debugBar);

        $result = (string)$helper->__invoke();
        
        $this->assertInternalType('string', $result);
    }

}
