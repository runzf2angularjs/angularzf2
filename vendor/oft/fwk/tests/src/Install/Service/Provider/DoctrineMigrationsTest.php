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

namespace Oft\Test\Install\Service\Provider;

use Mockery;
use Oft\Install\Service\Provider\DoctrineMigrations;
use Oft\Module\ModuleManager;
use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;

class DoctrineMigrationsTest extends PHPUnit_Framework_TestCase
{

    public function testCreate()
    {        
        $config = array(
            'migrations' => array(
                'table' => 'migrations',
            ),
        );
        
        // Application
        $app = new Application($config, $this->getModuleManagerMock());

        // Service DB
        $db = \Mockery::mock('Doctrine\DBAL\Connection');
        $app->setService('Db', $db);

        $service = new DoctrineMigrations();
        $configuration = $service->create($app);

        $this->assertInstanceOf(
            'Doctrine\DBAL\Migrations\Configuration\Configuration',
            $configuration
        );
    }
    
    protected function getModuleManagerMock()
    {
        // Default Module
        $defaultModule = Mockery::mock('Oft\Module\ModuleInterface');
        $defaultModule->shouldReceive('getName')
            ->twice() // Default module
            ->withNoArgs()
            ->andReturn('app');
        $defaultModule->shouldReceive('getDir')
            ->once()
            ->with('sql')
            ->andReturn('/');

        // Other Module
        $otherModule = Mockery::mock('Oft\Module\ModuleInterface');
        $otherModule->shouldReceive('getName')
            ->once() // Not default module
            ->withNoArgs()
            ->andReturn('other');
        $otherModule->shouldReceive('getDir')
            ->once()
            ->with('sql')
            ->andReturn('/');

        // ModuleManager
        $moduleManager = new ModuleManager();
        $moduleManager->addModule($defaultModule, true);
        $moduleManager->addModule($otherModule);

        return $moduleManager;
    }

}
