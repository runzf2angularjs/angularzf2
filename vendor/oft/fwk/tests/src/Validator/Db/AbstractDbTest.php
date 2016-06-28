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

namespace Oft\Test\Validator\Db;

use InvalidArgumentException;
use Mockery;
use Oft\Mvc\Application;
use Oft\Test\Validator\Db\ValidatorMock;
use Oft\Util\Functions;
use Oft\Validator\Db\AbstractDb;
use PHPUnit_Framework_TestCase;

class ValidatorMock extends AbstractDb
{
    public function isValid($value)
    {
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getExclude()
    {
        return $this->exclude;
    }

    public function getQueryBuilderAttribute()
    {
        return $this->queryBuilder;
    }

    public function queryAccess($value)
    {
        return $this->query($value);
    }
}

class AbstractDbTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException InvalidArgumentException
     * @expectedMessage Table option is missing
     */
    public function testConstructNoTable()
    {
        $options = array(
            /* miss table option */
        );

        $validator = new ValidatorMock($options);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedMessage Field option is missing
     */
    public function testConstructNoField()
    {
        $options = array(
            'table' => 'table',
            /* miss field option */
        );

        $validator = new ValidatorMock($options);
    }

    public function testConstructWithOptionsAndExclude()
    {
        $options = array(
            'table' => 'table',
            'field' => 'field',
            'exclude' => 'exclude',
        );

        $validator = new ValidatorMock($options);

        $this->assertEquals($options['exclude'], $validator->getExclude());
        $this->assertEquals($options['table'], $validator->getTable());
        $this->assertEquals($options['field'], $validator->getField());
        $this->assertNull($validator->getQueryBuilderAttribute());
    }

    public function testSetQueryBuilder()
    {
        $options = array(
            'table' => 'table',
            'field' => 'field',
            'exclude' => 'exclude',
        );

        $queryBuilder = Mockery::mock('Doctrine\DBAL\Query\QueryBuilder');

        $validator = new ValidatorMock($options);

        $validator->setQueryBuilder($queryBuilder);

        $this->assertEquals($queryBuilder, $validator->getQueryBuilder());
    }

    public function testGetQueryBuilderNoExclude()
    {
        $options = array(
            'table' => 'table',
            'field' => 'field',
//            'exclude' => 'exclude',
        );

        $qb = $this->getQueryBuilderMock($options);

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($qb);

        $app = new Application();
        $app->setService('Db', $db);

        Functions::setApp($app);

        $validator = new ValidatorMock($options);

        $validator->getQueryBuilder();
    }

    public function testGetQueryBuilderWithExcludeString()
    {
        $options = array(
            'table' => 'table',
            'field' => 'field',
            'exclude' => 'exclude',
        );

        $qb = $this->getQueryBuilderMock($options);

        $qb->shouldReceive('where')
            ->once()
            ->with($options['exclude'])
            ->andReturnSelf();

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($qb);

        $app = new Application();
        $app->setService('Db', $db);

        Functions::setApp($app);

        $validator = new ValidatorMock($options);

        $validator->getQueryBuilder();
    }

    public function testGetQueryBuilderWithExcludeArray()
    {
        $options = array(
            'table' => 'table',
            'field' => 'field',
            'exclude' => array(
                'field' => 'field',
                'value' => 'value',
            ),
        );

        $qb = $this->getQueryBuilderMock($options);

        $qb->shouldReceive('expr')
            ->once()
            ->withNoArgs()
            ->andReturnSelf();

        $qb->shouldReceive('neq')
            ->once()
            ->with($options['exclude']['field'], $options['exclude']['value'])
            ->andReturn('Doctrine\DBAL\Query\Expression');

        $qb->shouldReceive('where')
            ->once()
            ->with('Doctrine\DBAL\Query\Expression')
            ->andReturnSelf();

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $db->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($qb);
        $db->shouldReceive('quote')
            ->once()
            ->with($options['exclude']['value'])
            ->andReturn($options['exclude']['value']);

        $app = new Application();
        $app->setService('Db', $db);

        Functions::setApp($app);

        $validator = new ValidatorMock($options);

        $validator->getQueryBuilder();
    }

    public function testQuery()
    {
        $options = array(
            'table' => 'table',
            'field' => 'field',
        );

        $value = 'value';

        $qb = $this->getQueryBuilderMock($options);

        $qb->shouldReceive('setParameter')
            ->once()
            ->with(':value', $value)
            ->andReturnSelf();

        $qb->shouldReceive('execute')
            ->once()
            ->withNoArgs()
            ->andReturnSelf();

        $qb->shouldReceive('fetch')
            ->once()
            ->withNoArgs()
            ->andReturn(array());
        
        $db = Mockery::mock('Doctrine\DBAL\Connection');
        
        $db->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($qb);

        $app = new Application();
        $app->setService('Db', $db);

        Functions::setApp($app);

        $validator = new ValidatorMock($options);

        $validator->queryAccess($value);
    }

    protected function getQueryBuilderMock(array $options)
    {
        $qb = Mockery::mock('Doctrine\DBAL\Query\QueryBuilder');

        $qb->shouldReceive('select')
            ->once()
            ->with(array($options['field']))
            ->andReturnSelf();
        $qb->shouldReceive('from')
            ->once()
            ->with($options['table'])
            ->andReturnSelf();
        $qb->shouldReceive('where')
            ->once()
            ->with($options['field'] . ' = :value')
            ->andReturnSelf();

        return $qb;
    }

}
