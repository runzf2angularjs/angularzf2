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

class IdentityTest extends \PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $identity = new \Oft\Auth\Identity(array('username' => 'test'));

        $app = new \Oft\Mvc\Application();
        $app->setService('Identity', new \Oft\Test\Mock\IdentityContext($identity));

        $view = new \Oft\View\View();
        $view->setApplication($app);
        
        $identityHelper = new \Oft\View\Helper\Identity();
        $identityHelper->setView($view);
        
        $this->assertSame($identity, $identityHelper->__invoke());
    }

}
