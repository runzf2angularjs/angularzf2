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

namespace Oft\Test\Acl\Adapter;

use Mockery;
use Oft\Acl\Adapter\Db;
use PHPUnit_Framework_TestCase;

class DbTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Db
     */
    protected $adapterDb;

    protected $expectedConfig = array(
        'roles' => array(
            'users', 'moderators'
        ),
        'allow' => array(
            'users' => array(
                'roles' => array('users'),
                'resources' => array('mvc.res1', 'mvc.res2'),
            ),
            'moderators' => array(
                'roles' => array('moderators'),
                'resources' => array('mvc.res3'),
            ),
        ),
    );
    
    protected function setUp()
    {
        $rules = array(
            array(
                'role' => 'users',
                'resource' => 'mvc.res1',
            ),
            array(
                'role' => 'users',
                'resource' => 'mvc.res2',
            ),
            array(
                'role' => 'moderators',
                'resource' => 'mvc.res3',
            ),
        );

        $queryBuilder = Mockery::mock('\Doctrine\DBAL\Query\QueryBuilder');
        $queryBuilder->shouldReceive('select')
            ->once()
            ->withAnyArgs()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('from')
            ->once()
            ->withAnyArgs()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('leftJoin')
            ->twice()
            ->withAnyArgs()
            ->andReturn($queryBuilder);
        $queryBuilder->shouldReceive('execute')
            ->once()
            ->andReturn($rules);

        $connection = Mockery::mock('\Doctrine\DBAL\Connection');
        $connection->shouldReceive('createQueryBuilder')
            ->withNoArgs()
            ->once()
            ->andReturn($queryBuilder);

        $app = new \Oft\Mvc\Application();
        $app->setService('Db', $connection);

        $this->adapterDb = new Db($app);
    }

    public function testGetRoles()
    {
        $roles = $this->adapterDb->getRoles();

        $this->assertEquals($this->expectedConfig['roles'], $roles);
    }

    public function testGetAllowed()
    {
        $allowed = $this->adapterDb->getAllowed();

        $this->assertEquals($this->expectedConfig['allow'], $allowed);
    }
}
