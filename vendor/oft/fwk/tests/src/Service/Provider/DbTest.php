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

namespace Oft\Test\Service\Provider;

class DbTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $app = new \Oft\Mvc\Application(array(
            'db' => array(
                'driver' => 'pdo_sqlite',
                'memory' => true,
            )
        ));

        $dbProvider = new \Oft\Service\Provider\Db();

        $db = $dbProvider->create($app);

        $this->assertInstanceOf('\Doctrine\DBAL\Connection', $db);
    }

    public function testEnumTypeDefined()
    {
        $app = new \Oft\Mvc\Application(array(
            'db' => array(
                'driver' => 'pdo_sqlite',
            ),
            'dbal' => array(
                'types' => array(
                    'mapping' => array(
                        'enum' => 'string',
                    )
                )
            )
        ));
        $dbProvider = new \Oft\Service\Provider\Db();

        $db = $dbProvider->create($app);
        $db->connect();
        
        $platform = $db->getDatabasePlatform();

        $this->assertTrue($platform->hasDoctrineTypeMappingFor('enum'));
        $this->assertSame('string', $platform->getDoctrineTypeMapping('enum'));
    }
}
