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

use Oft\Util\Acl;
use PHPUnit_Framework_TestCase;

class AclTest extends PHPUnit_Framework_TestCase
{

    public function testGetMvcResourceFromString()
    {
        $mvcResource = array(
            'type' => 'mvc',
            'module' => 'module',
            'controller' => 'controller',
            'action' => 'action',
        );

        $result = array(
            '',
            'mvc.module.controller',
            'mvc.module',
        );

        $data = Acl::getMvcResourceFromString('mvc.module.controller.action');
        $this->assertEquals($mvcResource, $data);

        $mvcResource['action'] = null;
        $data = Acl::getMvcResourceFromString('mvc.module.controller');
        $this->assertEquals($mvcResource, $data);

        $mvcResource['controller'] = null;
        $data = Acl::getMvcResourceFromString('mvc.module');
        $this->assertEquals($mvcResource, $data);

        $data = Acl::getMvcResourceFromString('mvc');
        $this->assertEquals(null, $data);

        $data = Acl::getMvcResourceFromString('');
        $this->assertEquals(null, $data);
    }

}
