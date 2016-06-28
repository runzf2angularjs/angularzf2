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

namespace Oft\Gassi\Test\Auth;

use DomainException;
use Mockery;
use Oft\Gassi\Auth\GassiAuth;
use Oft\Mvc\Application;
use Oft\Mvc\Context\HttpContext;
use PHPUnit_Framework_TestCase;

class GassiAuthTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException DomainException
     * @expectedExceptionMessage Authentification impossible
     */
    public function testGetIdentityWithNoHeaders()
    {
        $request = Mockery::mock('Oft\Http\Request');
        $request->shouldReceive('getFromServer')
            ->withArgs(array('HTTP_SM_AUTHTYPE', false))
            ->andReturn(false);

        $app = new Application();
        $app->setService('Http', new HttpContext(array(
            'request' => $request
        )));

        $identityStore = Mockery::mock('Oft\Auth\IdentityStore\IdentityStoreInterface');
        $app->setService('IdentityStore', $identityStore);

        $gassi = new GassiAuth($app);

        $gassi->authenticate();
    }

    /**
     * @expectedException DomainException
     * @expectedExceptionMessage Authentification impossible
     */
    public function testGetIdentityWithNoUsername()
    {
        $data = array();

        $request = Mockery::mock('Oft\Http\Request');
        $request->shouldReceive('getFromServer')
            ->withArgs(array('HTTP_SM_AUTHTYPE', false))
            ->andReturn('form');
        $request->shouldReceive('getFromServer')
            ->withArgs(array('HTTP_SM_UNIVERSALID', false))
            ->andReturn(false);

        $app = new Application();
        $app->setService('Http', new HttpContext(array(
            'request' => $request
        )));

        $identityStore = Mockery::mock('Oft\Auth\IdentityStore\IdentityStoreInterface');
        $app->setService('IdentityStore', $identityStore);

        $gassi = new GassiAuth($app);

        $gassi->authenticate();
    }

    public function testGetFormNull()
    {
        $request = Mockery::mock('Oft\Http\Request');

        $app = new Application();
        $app->setService('Http', new HttpContext(array(
            'request' => $request
        )));

        $identityStore = Mockery::mock('Oft\Auth\IdentityStore\IdentityStoreInterface');
        $app->setService('IdentityStore', $identityStore);

        $auth = new GassiAuth($app);

        $form = $auth->getForm();

        $this->assertNull($form);
    }

    public function testAuthenticateSuccess()
    {
        $request = Mockery::mock('Oft\Http\Request');
        $request->shouldReceive('getFromServer')
            ->withArgs(array('HTTP_SM_AUTHTYPE', false))
            ->andReturn('form');
        $request->shouldReceive('getFromServer')
            ->withArgs(array('HTTP_SM_UNIVERSALID', false))
            ->andReturn('ABCD1234');

        $app = new Application();
        $app->setService('Http', new HttpContext(array(
            'request' => $request
        )));

        $identityStore = Mockery::mock('Oft\Auth\IdentityStore\IdentityStoreInterface');
        $identityStore->shouldReceive('getIdentity')
            ->with('ABCD1234')
            ->once()
            ->andReturn(null);
        $app->setService('IdentityStore', $identityStore);

        $auth = new GassiAuth($app);

        $auth->authenticate();
    }

}
