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

namespace Oft\Test\Install\Tools\DbMigrate;

use Oft\Install\Tools\DbMigrate\Configuration;
use PHPUnit_Framework_TestCase;

class ConfigurationTest extends PHPUnit_Framework_TestCase
{

    public function testRegisterMigrationsFromDirectory()
    {
        $schemaManager = \Mockery::mock('Doctrine\DBAL\Schema\MySqlSchemaManager');
        $platform = \Mockery::mock('Doctrine\DBAL\Platforms\AbstractPlatform');

        $connection = \Mockery::mock('Doctrine\DBAL\Connection');
        $connection->shouldReceive('getSchemaManager')->andReturn($schemaManager);
        $connection->shouldReceive('getDatabasePlatform')->andReturn($platform);
        
        $outputWriter = \Mockery::mock('Doctrine\DBAL\Migrations\OutputWriter');

        $path = __DIR__ . '/_files';
        $namespace = 'Oft\Test\Install\Tools\DbMigrate';

        $configuration = new Configuration($connection, $outputWriter);
        $configuration->setMigrationsNamespace($namespace);

        $versions = $configuration->registerMigrationsFromDirectory($path);

        $this->assertCount(1, $versions);
        $this->assertInstanceOf('Doctrine\DBAL\Migrations\Version', $versions[0]);
    }

}
