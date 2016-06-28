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

use Oft\Util\String;
use PHPUnit_Framework_TestCase;

class StringTest extends PHPUnit_Framework_TestCase
{
    public function testDashToCamelCase()
    {
        $result = String::dashToCamelCase('oft-fwk_test');
        
        $this->assertSame('OftFwk_Test', $result);
    }

    public function testCamelCaseToDash()
    {
       $result = String::camelCaseToDash('OftFwk\\Test');

       $this->assertSame('oft-fwk_test', $result);
    }

    public function testStringToValidClassName()
    {
       $resultName = String::stringToValidClassName('Some Body');
       $resultMail = String::stringToValidClassName('some.bo_dy@mail.com');
       
       $this->assertSame('Some_Body', $resultName);
       $this->assertSame('Some_Dot_Bo_U_Dy_At_Mail_Dot_Com', $resultMail);
    }

}
