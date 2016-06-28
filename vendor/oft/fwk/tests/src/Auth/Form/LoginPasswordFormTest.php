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

namespace Oft\Test\Auth\Form;

class LoginPasswordFormTest extends \PHPUnit_Framework_TestCase
{

    protected $form;

    protected function setUp()
    {
        $this->form = new \Oft\Auth\Form\LoginPasswordForm();
    }

    public function testForm()
    {
        $this->assertTrue($this->form->hasAttribute('role'));
        $this->assertSame('form', $this->form->getAttribute('role'));
        
        $this->assertTrue($this->form->has('login_csrf'));
        $this->assertTrue($this->form->has('username'));
        $this->assertTrue($this->form->has('password'));
        $this->assertTrue($this->form->has('submit'));
    }
    

}
