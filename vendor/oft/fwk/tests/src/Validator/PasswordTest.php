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

class PasswordTest extends \PHPUnit_Framework_TestCase
{
    private $password;
    
    protected function setUp()
    {
        $this->password = new \Oft\Validator\Password('password', 'password_repeat');
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testWarningNoContext()
    {
        $this->password->isValid('');
    }

    public function testValidIfEmpty()
    {
        $this->assertTrue($this->password->isValid('', array()));
        $this->assertCount(0, $this->password->getMessages());
    }

    public function testInValidIfOneIsntSet()
    {
        $this->assertFalse(
            $this->password->isValid(
                'pass',
                array('password' => 'pass',/* 'password_repeat' => ''*/)
            )
        );
        $this->assertCount(1, $this->password->getMessages());
        $this->assertArrayHasKey(\Oft\Validator\Password::PASSWORD_NOTSET, $this->password->getMessages());

        $this->assertFalse(
            $this->password->isValid(
                'pass',
                array(/*'password' => '',*/ 'password_repeat' => 'pass')
            )
        );
        $this->assertCount(1, $this->password->getMessages());
        $this->assertArrayHasKey(\Oft\Validator\Password::PASSWORD_NOTSET, $this->password->getMessages());
    }

    public function testInValidIfMismatch()
    {
        $this->assertFalse(
            $this->password->isValid(
                'pass',
                array('password' => 'pass', 'password_repeat' => 'notsame')
            )
        );
        $this->assertCount(1, $this->password->getMessages());
        $this->assertArrayHasKey(\Oft\Validator\Password::PASSWORD_MISMATCH, $this->password->getMessages());
    }

    public function testInValidIfLenIsNotValid()
    {
        $this->assertFalse(
            $this->password->isValid(
                'pa',
                array('password' => 'pa', 'password_repeat' => 'pa')
            )
        );
        $this->assertCount(1, $this->password->getMessages());
        $this->assertArrayHasKey(\Oft\Validator\Password::PASSWORD_INVALIDLEN, $this->password->getMessages());

        $password = md5('a') . 'MoreThan32';
        $this->assertFalse(
            $this->password->isValid(
                $password,
                array('password' => $password, 'password_repeat' => $password)
            )
        );
        $this->assertCount(1, $this->password->getMessages());
        $this->assertArrayHasKey(\Oft\Validator\Password::PASSWORD_INVALIDLEN, $this->password->getMessages());
    }


    public function testValidIsPossible()
    {
        $password = md5('a'); // 32
        $this->assertTrue(
            $this->password->isValid(
                $password,
                array('password' => $password, 'password_repeat' => $password)
            )
        );
        $this->assertCount(0, $this->password->getMessages());
    }
    
    public function testInValidIfMismatchMessage()
    {
        $this->assertFalse(
            $this->password->isValid(
                'pass',
                array('password' => 'pass', 'password_repeat' => 'notsame')
            )
        );
        $this->assertCount(1, $this->password->getMessages());
        $message = $this->password->getMessages();
        $this->assertEquals('Passwords do not match', $message[\Oft\Validator\Password::PASSWORD_MISMATCH]);
    }
    
    public function testInValidLengthMessage()
    {
        $this->assertFalse(
            $this->password->isValid(
                '',
                array('password' => '', 'password_repeat' => '')
            )
        );
        $this->assertCount(1, $this->password->getMessages());
        $message = $this->password->getMessages();
        $this->assertEquals('The password length should be between 4 and 32', $message[\Oft\Validator\Password::PASSWORD_INVALIDLEN]);
    }
    
}

