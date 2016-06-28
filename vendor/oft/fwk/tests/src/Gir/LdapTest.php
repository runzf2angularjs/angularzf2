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

namespace Oft\Test\Gir;

include_once __DIR__ . '/../Mock/Functions/Ldap.php';

use InvalidArgumentException;
use Oft\Gir\Ldap;
use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;

class LdapTest extends PHPUnit_Framework_TestCase
{

    protected $ldap;

    public function setUp()
    {
        $config = array(
            'gir' => array(
                'active' => true,
                'ldap' => array(
                    'host' => 'host',
                ),
            ),
        );

        global $extensionLoaded;
        $extensionLoaded = true;

        $app = new Application($config);

        $this->ldap = new Ldap($app);
        $this->ldap->connect();
    }

    /**
     * @expectedException RunTimeException
     */
    public function testGirInactive()
    {
        $config = array(
            'gir' => array(
                'active' => false
            ),
        );

        $app = new Application($config);

        $ldap = new Ldap($app);
    }

    /**
     * @expectedException RunTimeException
     */
    public function testLdapInactive()
    {
        $config = array(
            'gir' => array(
                'active' => true
            ),
        );

        // Variable globale : cf. Oft\Test\Mock\FunctionsLdap
        global $extensionLoaded;
        $extensionLoaded = false;

        $app = new Application($config);

        $ldap = new Ldap($app);
    }

    public function testFindCollaboratorsByUidOrCnOrMail()
    {
        $expected[0] = array(
            'uid' => 'DJLT4010',
            'sn' => 'Roulee',
            'givenname' => 'Arnaud',
            'telephonenumber' => '05',
            'mobile' => '06',
            'othertelephone' => '07',
            'mail' => 'aroulee.ext@orange.com',
            'civility' => 'M',
            'preferredlanguage' => 'Fr',
            'postaladdress' => 'Pessac',
            'ftadmou' => 'Orange/CCPHP',
            'manager' => 'uid=CCVQ5878,ou=people,dc=intrannuaire,dc=orange,dc=com'
        );

        $result = $this->ldap->findCollaboratorsByUidOrCnOrMail('DJLT4010');

        $this->assertEquals($expected, $result);
    }

    public function testFindCollaboratorsByUidOrCnOrMailTooShort()
    {
        $result = $this->ldap->findCollaboratorsByUidOrCnOrMail('DJL');

        $this->assertFalse($result);

        $result1 = $this->ldap->findCollaboratorsByUidOrCnOrMail(11111111);

        $this->assertFalse($result1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindCollaboratorsExceptionNotParam()
    {
        $this->ldap->findCollaborators(null);
    }

    public function testFindCollaboratorsWithArraySearch()
    {
        $search = array(
            'uid' => 'DJLT4010'
        );

        $expected[0] = array(
            'uid' => 'DJLT4010',
            'sn' => 'Roulee',
            'givenname' => 'Arnaud',
            'telephonenumber' => '05',
            'mobile' => '06',
            'othertelephone' => '07',
            'mail' => 'aroulee.ext@orange.com',
            'civility' => 'M',
            'preferredlanguage' => 'Fr',
            'postaladdress' => 'Pessac',
            'ftadmou' => 'Orange/CCPHP',
            'manager' => 'uid=CCVQ5878,ou=people,dc=intrannuaire,dc=orange,dc=com'
        );

        $result = $this->ldap->findCollaborators($search);

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindCollaboratorsWithBadArraySearch()
    {
        $search = array(
            1 => 'test'
        );

        $this->ldap->findCollaborators($search);
    }

    public function testFindCollaboratorsWithArraySearchNotNormalized()
    {
        $search = array(
            'uid' => 'DJLT4010'
        );

        $expected[0] = array(
            'uid' => array('count' => '1', 0 => 'DJLT4010'),
            'sn' => 'Roulee',
            'givenname' => 'Arnaud',
            'telephonenumber' => '05',
            'mobile' => '06',
            'othertelephone' => '07',
            'mail' => 'aroulee.ext@orange.com',
            'civility' => 'M',
            'preferredlanguage' => 'Fr',
            'postaladdress' => 'Pessac',
            'ftadmou' => 'ou=CCPHP,ou=Orange,ou=entities,ou=test',
            'manager' => 'uid=CCVQ5878,ou=people,dc=intrannuaire,dc=orange,dc=com',
            'jpegphoto' => 'photo',
            'dn' => 'DJLT4010'
        );

        $result = $this->ldap->findCollaborators($search, null, false);

        $this->assertEquals($expected, $result);
    }

    public function testNormalizeCollaboratorIgnore()
    {
        $collaboratorData = array(
            'givenname;normalize' => array('test')
        );

        $result = $this->ldap->normalizeCollaborator($collaboratorData);

        $this->assertEquals(array(), $result);
    }

    public function testNormalizeCollaboratorDataArray()
    {
        $collaboratorData = array(
            'uid' => array(
                'count' => '2',
                'test' => 'test',
                'test1' => 'test1',
            )
        );

        $expected = array(
            'uid' => array(
                'test' => 'test',
                'test1' => 'test1',
            )
        );

        $result = $this->ldap->normalizeCollaborator($collaboratorData);

        $this->assertEquals($expected, $result);
    }
    
    public function testGetCollaboratorBadCuid()
    {
        $result = $this->ldap->getCollaborator('DJLF');
        
        $this->assertEquals(array(), $result);
    }
    
    public function testGetCollaborator()
    {
        $expected = array(
            'uid' => 'DJLT4010',
            'sn' => 'Roulee',
            'givenname' => 'Arnaud',
            'telephonenumber' => '05',
            'mobile' => '06',
            'othertelephone' => '07',
            'mail' => 'aroulee.ext@orange.com',
            'civility' => 'M',
            'preferredlanguage' => 'Fr',
            'postaladdress' => 'Pessac',
            'ftadmou' => 'Orange/CCPHP',
            'manager' => 'uid=CCVQ5878,ou=people,dc=intrannuaire,dc=orange,dc=com'
        );

        $result = $this->ldap->getCollaborator('DJLT4010');

        $this->assertEquals($expected, $result);
    }
    
    public function testGetCollaboratorEmpty()
    {
        $result = $this->ldap->getCollaborator('DJLT4011');

        $this->assertEquals(array(), $result);
    }
    
    public function testGetCollaboratorPhoto()
    {
        $photo = $this->ldap->getCollaboratorPhoto('DJLT4010');
        
        $this->assertEquals('photo', $photo);
        
        $photo1 = $this->ldap->getCollaboratorPhoto('DJLT4011');
        
        $this->assertEquals(null, $photo1);
        
        $photo2 = $this->ldap->getCollaboratorPhoto('DJLT4012');
        
        $this->assertEquals(null, $photo2);
    }
    
    public function testIsManagerNoResult()
    {
        $result = $this->ldap->getIsManager('DJLT4545');
        
        $this->assertEquals(false, $result);
    }
    
    public function testIsManager()
    {
        $resultTrue = $this->ldap->getIsManager('DJLT4010');
        
        $this->assertEquals(true, $resultTrue);
        
        $resultFalse = $this->ldap->getIsManager('DJLT4012');
        
        $this->assertEquals(false, $resultFalse);
    }

    public function testGetCollaboratorTeamWithDefaultAttribs()
    {
        $expected = array(
            array(
                'uid' => 'DJLT4010',
                'sn' => 'Roulee',
                'givenname' => 'Arnaud',
                'telephonenumber' => '05',
                'mobile' => '06',
                'othertelephone' => '07',
                'mail' => 'aroulee.ext@orange.com',
                'civility' => 'M',
                'preferredlanguage' => 'Fr',
                'postaladdress' => 'Pessac',
                'ftadmou' => 'CCPHP Orange entities',
                'manager' => 'CCVQ5878'
            ), array(
                'uid' => 'DJLT4011',
                'sn' => 'Roulee1',
                'givenname' => 'Arnaud1',
                'telephonenumber' => '051',
                'mobile' => '061',
                'othertelephone' => '071',
                'mail' => 'aroulee1.ext@orange.com',
                'civility' => 'M',
                'preferredlanguage' => 'Fr',
                'postaladdress' => 'Pessac1',
                'ftadmou' => 'CCPHP1 Orange1 entities',
                'manager' => 'CCVQ5879'
            ),
        );

        $result = $this->ldap->getCollaboratorTeam('CLBT0000');

        $this->assertEquals($expected, $result);
    }

    public function testGetCollaboratorTeamNull()
    {
        $result = $this->ldap->getCollaboratorTeam('CLBT0001');

        $this->assertNull($result);
    }

    public function testGetLeid()
    {
        $expected = 'LEID1234';

        $result = $this->ldap->getLeid('CUID1234');

        $this->assertEquals($expected, $result);
    }

    public function testGetLeidFalse()
    {
        $result = $this->ldap->getLeid('CUID1234-F');

        $this->assertFalse($result);
    }

}
