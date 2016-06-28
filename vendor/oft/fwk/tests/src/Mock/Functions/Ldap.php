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

namespace Oft\Gir
{

    function extension_loaded($extension)
    {
        global $extensionLoaded;
        
        if(!isset($extensionLoaded)) {
            $extensionLoaded = true;
        }

        return $extensionLoaded;
    }

    function ldap_search($resource, $baseDn, $filter, $attribute)
    {
        if ($filter === '(|(uid=*DJLT4010*)(cn=*DJLT4010*)(mail=*DJLT4010*))'
            || $filter === '(&(uid=*DJLT4010*))'
            || $filter === '(manager=DJLT4010)') {
            return 'DJLT4010';
        }

        if ($filter === '(&(uid=*DJLT4012*))') {
            return 'DJLT4012';
        }

        if ($filter === '(&(uid=*DJLT*))') {
            return 'DJLT';
        }

        if ($filter === '(uid=CLBT0000)') {
            return 'DJLTADMOU';
        }

        if ($filter === '(uid=CLBT0001)') {
            return 'DJLTNULL';
        }

        if ($filter === '(&(ftadmou=o)(|(gircategory=CI)(gircategory=CM)(gircategory=CE)(gircategory=EX)(gircategory=GC_II)))') {
            return 'DJLTTEAM';
        }

        if ($filter === '(uid=CUID1234)') {
            return 'CUID1234';
        }

        if ($filter === '(uid=CUID1234-F)') {
            return 'CUID1234-F';
        }
    }

    function ldap_get_entries($resource, $search)
    {
        $data = array();
        
        if ($search === 'DJLT4010') {
            $data[] = array(
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
        } else if ($search === 'DJLT4012') {
            $data[] = array(
                'uid' => 'DJLT4012',
                'dn' => 'DJLT4012'
            );
        } else if ($search === 'DJLT') {
            $data[] = array(
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
                'ftadmou' => 'ou=CCPHP ou=Orange ou=entities',
                'manager' => 'CCVQ5878'
            );

            $data[] = array(
                'uid' => array('count' => '1', 0 => 'DJLT4011'),
                'sn' => 'Roulee1',
                'givenname' => 'Arnaud1',
                'telephonenumber' => '051',
                'mobile' => '061',
                'othertelephone' => '071',
                'mail' => 'aroulee1.ext@orange.com',
                'civility' => 'M',
                'preferredlanguage' => 'Fr',
                'postaladdress' => 'Pessac1',
                'ftadmou' => 'ou=CCPHP1 ou=Orange1 ou=entities',
                'manager' => 'CCVQ5879'
            );
        } else if ($search === 'DJLTTEAM') {
            $data[] = array(
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
                'ftadmou' => 'ou=CCPHP ou=Orange ou=entities',
                'manager' => 'CCVQ5878'
            );

            $data[] = array(
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
                'ftadmou' => 'ou=CCPHP1 ou=Orange1 ou=entities',
                'manager' => 'CCVQ5879'
            );
        } else if ($search === 'DJLTADMOU') {
            $data[] = array(
                'ftadmou' => 'ou=CCPHP,ou=Orange,ou=entities,ou=test',
            );
        } else if ($search === 'DJLTNULL') {
            // No result
        } else if ($search === 'CUID1234') {
            $data[] = array(
                'employeenumber' => 'LEID1234',
            );
        } else if ($search === 'CUID1234-F') {
            // No result
            // OR
            // partial result :
            // $data[] = array();
        }

        return $data;
    }
}

namespace Oft\Gir\Ldap
{

    function ldap_bind($resource, $username, $password)
    {
        if ($username === "badUsername") {
            return false;
        } else {
            return true;
        }
    }

    function ldap_connect($host, $port = null)
    {
        if ($host === "badhost") {
            return false;
        } else {
            return tmpfile();
        }
    }

    function ldap_set_option($resource, $key, $value)
    {
        return true;
    }

    function ldap_close($resource)
    {
        return true;
    }
}