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

class IdentityTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultCreate()
    {
        $identity = new \Oft\Auth\Identity(array());

        $this->assertTrue($identity->isGuest());
        $this->assertTrue($identity->isActive());
        $this->assertFalse($identity->isAdmin());
    }

    public function testSetMixedCaseUsername()
    {
        $identity = new \Oft\Auth\Identity(array('username' => 'mE'));

        $this->assertSame('me', $identity->getUsername());
    }

    public function testSetUsernameAfterCreateSuccessIfGuest()
    {
        $identity = new \Oft\Auth\Identity(array());
        $identity->setUsername('ME');

        $this->assertSame("me", $identity->getUsername());
    }

    public function testSetUsernameAfterCreateFailIfNotGuest()
    {
        $this->setExpectedException("\RuntimeException");

        $identity = new \Oft\Auth\Identity(array('username' => 'me'));
        $identity->setUsername('you');
    }

    public function testIsInactiveIfSetByDefault()
    {
        $identity = new \Oft\Auth\Identity(array('username' => 'me'));
        $identity->setActive(false);
        $this->assertFalse($identity->isActive());
    }

    public function testGuestGroupsAlwaysPresent()
    {
        $identity = new \Oft\Auth\Identity(array('username' => 'me'));

        $this->assertArrayHasKey(\Oft\Auth\Identity::GUEST_GROUP, $identity->getGroups());

        $identity->setGroups(array('testGrp' => 'testGrpName'));

        $this->assertArrayHasKey(\Oft\Auth\Identity::GUEST_GROUP, $identity->getGroups());
        $this->assertArrayHasKey("testGrp", $identity->getGroups());
    }

    public function testCurrentGroupDoesntChange()
    {
        $identity = new \Oft\Auth\Identity(array('username' => 'me'));

        $identity->setGroups(array('testGrp' => 'testGrpName', 'testGrp2' => 'testGrp2Name'));
        $this->assertSame('testGrp', $identity->getCurrentGroup());

        $identity->setCurrentGroup('testGrp2');
        $this->assertSame('testGrp2', $identity->getCurrentGroup());

        $identity->setGroups(array('testGrp' => 'testGrpName', 'testGrp2' => 'testGrp2Name'));
        $this->assertSame('testGrp2', $identity->getCurrentGroup());

        $identity->setGroups(array('testGrp3' => 'testGrp3Name', 'testGrp' => 'testGrpName'));
        $this->assertSame('testGrp3', $identity->getCurrentGroup());
    }

    public function testSetCurrentGroupFailIfDoesNotExists()
    {
        $this->setExpectedException('\RuntimeException');

        $identity = new \Oft\Auth\Identity(array('username' => 'me'));

        $identity->setCurrentGroup('doesNotExists');
    }

    public function testSetGroupsFailIfEmpty()
    {
        $this->setExpectedException('\RuntimeException');

        $identity = new \Oft\Auth\Identity(array('username' => 'me'));

        $identity->setGroups(array());
    }

    public function testMergeUseSet()
    {
        $identityInfo = array(
            'username' => 'ABCD1234',
        );

        $identity = new \Oft\Auth\Identity($identityInfo);

        $this->assertSame($identity->getUsername(), 'abcd1234');
        $this->assertSame($identity->username, 'abcd1234');
    }

    public function testMergeWithNoSet()
    {
        $identityInfo = array(
            'var' => 'value',
        );

        $identity = new \Oft\Auth\Identity($identityInfo);

        $this->assertSame($identity->var, 'value');
    }

    public function testToArray()
    {
        $identityInfo = array(
            'username' => 'ABCD1234',
            'var' => 'value',
        );

        $identity = new \Oft\Auth\Identity($identityInfo);

        $array = $identity->toArray();

        $this->assertInternalType('array', $array);
        $this->assertArrayHasKey('username', $array);
        $this->assertSame('abcd1234', $array['username']);
        $this->assertArrayHasKey('var', $array);
        $this->assertSame('value', $array['var']);
    }

    public function testSetUsername()
    {
        $identity = new \Oft\Auth\Identity(array());

        $this->assertTrue($identity->isGuest());
        $this->assertSame(\Oft\Auth\Identity::GUEST_USERNAME, $identity->getUsername());

        $identity->setUsername('someone');
        $this->assertFalse($identity->isGuest());
        $this->assertEquals('someone', $identity->getUsername());
    }

    public function testSetUsernameThrowsExceptionIfAlreadySet()
    {
        $this->setExpectedException('\RuntimeException');

        $identity = new \Oft\Auth\Identity(array('username' => 'someone'));

        $identity->setUsername('someoneElse');
    }

    public function testSetUsernameDontThrowsExceptionIfIdentical()
    {
        $identity = new \Oft\Auth\Identity(array('username' => 'someone'));

        $identity->setUsername('someone');

        $this->assertEquals('someone', $identity->getUsername());
    }

    public function testGetDisplayNameIsEqualToUsernameIfNotSet()
    {
        $identity = new \Oft\Auth\Identity(array('username' => 'someone'));

        $this->assertSame('Someone', $identity->getDisplayName());
    }

    public function testSetDisplayName()
    {
        $identity = new \Oft\Auth\Identity(array('username' => 'someone'));
        $identity->setDisplayName('Some One');

        $this->assertSame('Some One', $identity->getDisplayName());
    }

    public function testSetLanguageFailWithWrongType()
    {
        $this->setExpectedException('\RuntimeException');

        $identity = new \Oft\Auth\Identity(array());

        $identity->setLanguage(123);
    }

    public function testSetLanguageFailWithWrongLength()
    {
        $this->setExpectedException('\RuntimeException');

        $identity = new \Oft\Auth\Identity(array());

        $identity->setLanguage('123');
    }

    public function testSetLanguageInLowerCase()
    {
        $identity = new \Oft\Auth\Identity(array());

        $identity->setLanguage('FR');

        $this->assertSame('fr', $identity->getLanguage());
    }

    public function testSetWithProtectedIdentity()
    {
        $this->setExpectedException('\RuntimeException');

        $identity = new \Oft\Auth\Identity(array());

        $identity->username = 'me';
    }

    public function testSetWithUnprotectedIdentity()
    {
        $identity = new \Oft\Auth\Identity(array());

        $identity->var = 'val';

        $this->assertSame('val', $identity->var);
    }

    public function testGet()
    {
        $identity = new \Oft\Auth\Identity(array(
            'username' => 'someone',
            'var' => 'val',
        ));

        $this->assertSame('someone', $identity->username);
        $this->assertSame('val', $identity->var);
        $this->assertNull($identity->doesNotExists);
    }

    public function testUnsetWithProtectedIdentity()
    {
        $this->setExpectedException('\RuntimeException');

        $identity = new \Oft\Auth\Identity(array(
            'username' => 'someone',
        ));

        unset($identity->username);
    }

    public function testUnsetWithUnprotectedIdentity()
    {
        $identity = new \Oft\Auth\Identity(array(
            'var' => 'val',
        ));

        unset($identity->var);
    }

    /**
     * Correction du bug 738
     * Oft\Auth\Identity : un return dans un setter
     * 
     */
    public function testSetDisplayNameNoReturn()
    {
        $identity = new \Oft\Auth\Identity(array());
        $result = $identity->setDisplayName('test');
        
        $this->assertEquals(null, $result);
        $this->assertEquals('test', $identity->displayName);
    }

}
