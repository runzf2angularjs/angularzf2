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

namespace Oft\Gassi\Test\Auth\IdentityStore;

use Mockery;
use Oft\Gassi\Auth\IdentityStore\Gassi;
use Oft\Mvc\Application;
use Oft\Mvc\Context\HttpContext;
use PHPUnit_Framework_TestCase;

class MockEntityUserForTestGetIdentity
{

    public function getWhere()
    {
        return array(
            array('id_user' => 'user1')
        );
    }

    public function load()
    {
    }

    public function getArrayForIdentity()
    {
        return array(
            'salt' => '7760da',
            'password' => '0debb409118c154480433b8e082fe1ea'
        );
    }

}

class GassiTest extends PHPUnit_Framework_TestCase
{

    protected function getGassiStore($headers = array())
    {
        $headersList = array(
            'HTTP_SM_AUTHTYPE',
            'HTTP_SM_UNIVERSALID',
            'HTTP_FTAPPLICATIONROLES',
            'HTTP_FTUSERGIVENNAME',
            'HTTP_FTUSERSN',
            'HTTP_FTUSERMAIL',
            'HTTP_FTUSERTELEPHONENUMBER',
            'HTTP_FTUSERCREDENTIALS'
        );

        $request = Mockery::mock('Oft\Http\Request');

        foreach ($headersList as $header) {
            $return = false;
            if (\array_key_exists($header, $headers)) {
                $return = $headers[$header];
            }

            $request->shouldReceive('getFromServer')
                ->withArgs(array($header, false))
                ->andReturn($return);
        }

        $app = new Application();
        $app->setService('Http', new HttpContext(array(
            'request' => $request
        )));

        $store = new Gassi($app);

        return $store;
    }

    public function testGetIdentity()
    {
        $headers = array(
            'HTTP_SM_AUTHTYPE' => 'form',
            'HTTP_SM_UNIVERSALID' => 'ABCD1234',
        );

        $gassi = $this->getGassiStore($headers);

        $identity = $gassi->getIdentity('ABCD1234');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);
        $this->assertEquals('abcd1234', $identity->getUsername());
    }

    public function testGetIdentityWithGroups()
    {
        $headers = array(
            'HTTP_SM_AUTHTYPE' => 'form',
            'HTTP_SM_UNIVERSALID' => 'ABCD1234',
            'HTTP_FTAPPLICATIONROLES' => 'WOO-01DEV WOOACC01,WOO-01DEV WOOPRF01, WOO-01DEV WOOCPINV01',
        );

        $gassi = $this->getGassiStore($headers);

        $identity = $gassi->getIdentity('ABCD1234');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);

        $expectedGroups = array(
            'WOOACC01' => 'WOOACC01',
            'WOOPRF01' => 'WOOPRF01',
            'WOOCPINV01' => 'WOOCPINV01',
            'guests' => 'Invité',
        );
        $this->assertEquals($expectedGroups, $identity->getGroups());
    }

    public function testGetIdentityWithDisplayName()
    {
        $headers = array(
            'HTTP_SM_AUTHTYPE' => 'form',
            'HTTP_SM_UNIVERSALID' => 'ABCD1234',
            'HTTP_FTUSERGIVENNAME' => 'Jean',
            'HTTP_FTUSERSN' => 'Dupont',
        );

        $gassi = $this->getGassiStore($headers);

        $identity = $gassi->getIdentity('ABCD1234');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);

        $expectedDisplayName = 'Jean Dupont';
        $this->assertEquals($expectedDisplayName, $identity->getDisplayName());
    }

    public function testGetIdentityWithPartialDisplayNameSn()
    {
        $headers = array(
            'HTTP_SM_AUTHTYPE' => 'form',
            'HTTP_SM_UNIVERSALID' => 'ABCD1234',
            'HTTP_FTUSERSN' => 'Dupont',
        );

        $gassi = $this->getGassiStore($headers);

        $identity = $gassi->getIdentity('ABCD1234');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);

        $expectedDisplayName = 'Dupont';
        $this->assertEquals($expectedDisplayName, $identity->getDisplayName());
    }

    public function testGetIdentityWithPartialDisplayNameGiven()
    {
        $headers = array(
            'HTTP_SM_AUTHTYPE' => 'form',
            'HTTP_SM_UNIVERSALID' => 'ABCD1234',
            'HTTP_FTUSERGIVENNAME' => 'Jean',
        );

        $gassi = $this->getGassiStore($headers);

        $identity = $gassi->getIdentity('ABCD1234');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);

        $expectedDisplayName = 'Jean';
        $this->assertEquals($expectedDisplayName, $identity->getDisplayName());
    }

    public function testGetIdentityWithMail()
    {
        $headers = array(
            'HTTP_SM_AUTHTYPE' => 'form',
            'HTTP_SM_UNIVERSALID' => 'ABCD1234',
            'HTTP_FTUSERMAIL' => 'mail@mail.fr',
        );

        $gassi = $this->getGassiStore($headers);

        $identity = $gassi->getIdentity('ABCD1234');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);

        $expectedMail = 'mail@mail.fr';
        $this->assertEquals($expectedMail, $identity->mail);
    }

    public function testGetIdentityWithTelNumber()
    {
        $headers = array(
            'HTTP_SM_AUTHTYPE' => 'form',
            'HTTP_SM_UNIVERSALID' => 'ABCD1234',
            'HTTP_FTUSERTELEPHONENUMBER' => '0123456789',
        );

        $gassi = $this->getGassiStore($headers);

        $identity = $gassi->getIdentity('ABCD1234');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);

        $expectedTelNb = '0123456789';
        $this->assertEquals($expectedTelNb, $identity->phoneNumber);
    }

    public function testGetIdentityWithCredentials()
    {
        $headers = array(
            'HTTP_SM_AUTHTYPE' => 'form',
            'HTTP_SM_UNIVERSALID' => 'ABCD1234',
            'HTTP_FTUSERCREDENTIALS' => 'test',
        );

        $gassi = $this->getGassiStore($headers);

        $identity = $gassi->getIdentity('ABCD1234');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);

        $expectedCred = 'test';
        $this->assertEquals($expectedCred, $identity->credentials);
    }
}
