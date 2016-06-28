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

use Oft\Gassi\Auth\IdentityStore\GassiDb;
use Oft\Http\Request as OftRequest;
use Oft\Mvc\Application;
use Oft\Mvc\Context\HttpContext;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request as SfRequest;

class MockEntityUserForTestGetIdentityDb
{

    public function __construct($db)
    {
    }

    public function loadByUserName($username)
    {
    }

    public function getGroups()
    {
        return array(
            'group1' => 'group1',
            'group2' => 'group2'
        );
    }

}

class GassiDbTest extends PHPUnit_Framework_TestCase
{

    public function testGetIdentity()
    {
        // Entêtes GASSI
        $_SERVER['HTTP_FTAPPLICATIONROLES'] = 'WOO-01DEV WOOACC01,WOO-01DEV WOOPRF01';

        // Application
        $app = new Application();
        $app->setService('Db', \Mockery::mock('Doctrine\DBAL\Connection'));
        $app->setService('Http', new HttpContext(array(
            'request' => new OftRequest(SfRequest::createFromGlobals())
        )));

        // IdentityStore
        $store = new GassiDb($app);
        $store->setUserEntityClassName('Oft\Gassi\Test\Auth\IdentityStore\MockEntityUserForTestGetIdentityDb');

        $identity = $store->getIdentity('ABCD1234');

        $this->assertInstanceOf('Oft\Auth\Identity', $identity);

        $this->assertEquals('abcd1234', $identity->getUsername());
        $this->assertEquals(
            array(
                // Groupe par défaut
                'guests' => 'Invité',

                // Groupes BdD
                'group1' => 'group1',
                'group2' => 'group2',

                // Groupes GASSI
                'WOOACC01' => 'WOOACC01',
                'WOOPRF01' => 'WOOPRF01',
            ),
            $identity->getGroups()
        );
    }

}
