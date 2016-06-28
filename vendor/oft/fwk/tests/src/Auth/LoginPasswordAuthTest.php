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

namespace Oft\Test\Auth;

class LoginPasswordAuthTest extends \PHPUnit_Framework_TestCase
{

    protected function getAuth($username, $result)
    {
        $store = \Mockery::mock('Oft\Auth\IdentityStore\IdentityStoreInterface');

        if ($username && $result) {
            $ex = $store->shouldReceive('getIdentity')
                ->with($username);

            if ($result instanceof \Exception) {
                $ex->andThrow($result);
            } else {
                $ex->andReturn(new \Oft\Auth\Identity($result));
            }
        }

        $app = new \Oft\Mvc\Application();
        $app->setService('IdentityStore', $store);

        return new \Oft\Auth\LoginPasswordAuth($app);
    }

    public function testGetForm()
    {
        $auth = $this->getAuth(null, null);
        $form = $auth->getForm();

        $this->assertInstanceOf('Oft\Auth\Form\LoginPasswordForm', $form);
        $this->assertInstanceOf('Oft\Entity\BaseEntity', $form->getObject());
        $this->assertSame(array('username' => null, 'password' => null), $form->getObject()->getArrayCopy());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage someMessage
     */
    public function testAuthenticateErrorWithUnknownUser()
    {
        $data = array(
            'username' => 'doesnotexists',
            'password' => 'password',
        );

        $auth = $this->getAuth('doesnotexists', new \DomainException('someMessage'));

        $form = $auth->getForm();
        $form->getObject()
            ->exchangeArray($data);

        $auth->authenticate();
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Mot de passe incorrect
     */
    public function testAuthenticateErrorBadPassword()
    {
         $data = array(
            'username' => 'admi1234',
            'password' => 'paSSwoRD',
        );

        $identityData = array(
            'username' => 'admi1234',
            'password' => 'password',
            'salt' => 'abcd',
            'password' => md5('abcdpassword'),
            'active' => 1
        );

        $auth = $this->getAuth('admi1234', $identityData);

        $form = $auth->getForm();
        $form->getObject()
            ->exchangeArray($data);

        $identity = $auth->authenticate();
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage L'utilisateur est désactivé
     */
    public function testAuthenticateErrorInactive()
    {
         $data = array(
            'username' => 'admi1234',
            'password' => 'password',
        );

        $identityData = array(
            'username' => 'admi1234',
            'password' => 'password',
            'salt' => 'abcd',
            'password' => md5('abcdpassword'),
            'active' => 0
        );

        $auth = $this->getAuth('admi1234', $identityData);

        $form = $auth->getForm();
        $form->getObject()
            ->exchangeArray($data);

        $identity = $auth->authenticate();
    }

    public function testAuthenticateSuccess()
    {
        $data = array(
            'username' => 'admi1234',
            'password' => 'password',
        );

        $identityData = array(
            'username' => 'admi1234',
            'password' => 'password',
            'salt' => 'abcd',
            'password' => md5('abcdpassword'),
            'active' => 1
        );

        $auth = $this->getAuth('admi1234', $identityData);

        $form = $auth->getForm();
        $form->getObject()
            ->exchangeArray($data);

        $identity = $auth->authenticate();

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);
        $this->assertSame('admi1234', $identity->username);
    }

}
