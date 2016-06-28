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

namespace Oft\Test\Service\Provider;

use Oft\Http\Session;
use Oft\Mvc\Application;
use Oft\Mvc\Context\HttpContext;
use Oft\Service\Provider\IdentityContext;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

class IdentityContextTest extends PHPUnit_Framework_TestCase {

    public function testCreate()
    {
        $config = array(
            'auth' => array(
                'expiration' => array(
                    'seconds' => ''
                )
            )
        );

        $realSession = new SymfonySession();

        $session = new Session($realSession);

        $app = new Application($config);

        $httpContext = new HttpContext();
        $httpContext->setSession($session);

        $app->setService('Http', $httpContext);

        $identityContext = new IdentityContext();

        $service = $identityContext->create($app);

        $this->assertInstanceOf('Oft\Mvc\Context\IdentityContext', $service);
    }
}
 