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

class AclResourceTest extends \PHPUnit_Framework_TestCase
{
    private $aclRessource;
    
    public function setUp()
    {
        $this->aclRessource = new \Oft\Validator\AclResource();
    }

    public function testNullIsValid()
    {
        $this->assertTrue($this->aclRessource->isValid(null));
        $this->assertCount(0, $this->aclRessource->getMessages());
    }

    public function testInvalidInput()
    {
        $this->assertFalse($this->aclRessource->isValid(1));
        $this->assertArrayHasKey(\Oft\Validator\AclResource::INVALID, $this->aclRessource->getMessages());
    }

    public function testEmptyStringIsInvalid()
    {
        $this->assertFalse($this->aclRessource->isValid(''));
        $this->assertCount(1, $this->aclRessource->getMessages());
        $this->assertArrayHasKey(\Oft\Validator\AclResource::STRING_EMPTY, $this->aclRessource->getMessages());
    }

    public function testValidString()
    {
        $this->assertTrue($this->aclRessource->isValid('abc.def'));
        $this->assertCount(0, $this->aclRessource->getMessages());
    }

    public function testInvalidString()
    {
        $this->assertFalse($this->aclRessource->isValid('.def'));
        $this->assertCount(1, $this->aclRessource->getMessages());
        $this->assertArrayHasKey(\Oft\Validator\AclResource::NOT_RESOURCE, $this->aclRessource->getMessages());
    }
}

