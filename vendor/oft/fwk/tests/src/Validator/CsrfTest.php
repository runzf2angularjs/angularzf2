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

namespace Oft\Test\Validator;

use ArrayObject;
use Oft\Mvc\Application;
use Oft\Test\Mock\HttpContext;
use Oft\Util\Functions;
use Oft\Validator\Csrf;
use PHPUnit_Framework_TestCase;

class CsrfTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Functions::setApp(null);
    }

    public function testIsValid()
    {
        $token = '055cb6e7bd877d2a0b223bf60dd274a0-56f478b6e02e596d5b19d42a24a82f1e';

        $app = new Application();
        $app->setService('Http', new HttpContext());

        $session = new ArrayObject();
        $session->expiration = time() + 150;
        $session->tokenList = array(
            '56f478b6e02e596d5b19d42a24a82f1e' => '055cb6e7bd877d2a0b223bf60dd274a0'
        );

        $app->http->session
            ->shouldReceive('getContainer')
            ->andReturn($session);

        Functions::setApp($app);

        $validator = new Csrf();

        $this->assertTrue($validator->isValid($token));
    }

    public function testIsNotValid()
    {
        $badToken = 'badcb6e7bd877d2a0b223bf60dd274a0-56f478b6e02e596d5b19d42a24a82f1e';

        $app = new Application();
        $app->setService('Http', new HttpContext());

        $session = new ArrayObject();
        $session->expiration = time() + 150;
        $session->tokenList = array(
            '56f478b6e02e596d5b19d42a24a82f1e' => '055cb6e7bd877d2a0b223bf60dd274a0'
        );

        $app->http->session
            ->shouldReceive('getContainer')
            ->andReturn($session);

        Functions::setApp($app);

        $validator = new Csrf();

        $this->assertFalse($validator->isValid($badToken));

        $messages = $validator->getMessages();
        $templates = $validator->getMessageTemplates();

        $this->assertInternalType('array', $messages);
        $this->assertEquals(1, count($messages));
        $this->assertArrayHasKey(Csrf::NOT_SAME, $messages);

        $this->assertArrayHasKey(Csrf::NOT_SAME, $templates);
        $this->assertEquals(
            $messages[Csrf::NOT_SAME],
            $templates[Csrf::NOT_SAME]
        );
    }

    public function testIsValidWithoutSession()
    {
        $token = '055cb6e7bd877d2a0b223bf60dd274a0-56f478b6e02e596d5b19d42a24a82f1e';

        $app = new Application();
        $app->setService('Http', new HttpContext());

        $session = new ArrayObject();

        $app->http->session
            ->shouldReceive('getContainer')
            ->andReturn($session);

        Functions::setApp($app);

        $validator = new Csrf();

        $this->assertFalse($validator->isValid($token));
    }

    public function testIsValidWithExpiredTokenList()
    {
        $token = '055cb6e7bd877d2a0b223bf60dd274a0-56f478b6e02e596d5b19d42a24a82f1e';

        $app = new Application();
        $app->setService('Http', new HttpContext());

        $session = new ArrayObject();
        $session->expiration = time() - 150;
        $session->tokenList = array(
            '56f478b6e02e596d5b19d42a24a82f1e' => '055cb6e7bd877d2a0b223bf60dd274a0'
        );

        $app->http->session
            ->shouldReceive('getContainer')
            ->once()
            ->with('Oft_Validator_Csrf_salt_csrf')
            ->andReturn($session);
        $app->http->session
            ->shouldReceive('dropContainer')
            ->once()
            ->andReturn(true);
        $app->http->session
            ->shouldReceive('getContainer')
            ->once()
            ->with('Oft_Validator_Csrf_salt_csrf')
            ->andReturn(new ArrayObject());

        Functions::setApp($app);

        $validator = new Csrf();

        $this->assertFalse($validator->isValid($token));
    }

    /**
     * Teste que le hash sous la forme rand1-rand2
     * est ajouté dans la liste de token en session
     * sous la forme d'un tableau rand2 => rand1
     */
    public function testGetHash()
    {
        $app = new Application();
        $app->setService('Http', new HttpContext());

        $session = new ArrayObject();

        $app->http->session
            ->shouldReceive('getContainer')
            ->once()
            ->andReturn($session);

        Functions::setApp($app);

        $validator = new Csrf();

        $hash = $validator->getHash();

        $tokenList = $session->tokenList;

        $exploded = explode('-', $hash);

        $this->assertEquals($tokenList[$exploded[1]], $exploded[0]);

        $this->assertEquals($hash, $validator->__get('value'));
    }

    public function testOptionContructor()
    {
        $option = array(
            'name' => 'testName',
            'salt' => 'testSalt',
            'timeout' => '12',
            'test' => 'testUnused',
        );

        $validator = new Csrf($option);

        $this->assertEquals('testName', $validator->getName());
        $this->assertEquals('testSalt', $validator->getSalt());
        $this->assertEquals('12', $validator->getTimeout());
    }
}

