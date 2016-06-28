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

namespace Oft\Test\Install\Generate\Repository;

use DomainException;
use InvalidArgumentException;
use Mockery;
use Oft\Install\Generate\Repository\RepositoryGenerator;
use Oft\Install\Tools\MySql\TableDescription;
use Oft\Module\ModuleManager;
use PHPUnit_Framework_TestCase;

class TableDescriptionMockForRepositoryGenerator extends TableDescription
{
    public function __construct()
    {
        $this->columns = array('primary' => array('type' => 'int', 'identity' => true));
        $this->primary = array('primary');
        $this->formElements = array(
            'primary' => array(
                'type' => 'Zend\Form\Element\Hidden',
                'input_filter' => false,
            ),
        );
    }
}

class RepositoryGeneratorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetTableNameExceptionBecauseNull()
    {
        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $moduleManager = new ModuleManager();

        $generator = new RepositoryGenerator($db, $moduleManager);

        $generator->getTableName();
    }

    public function testGetTableName()
    {
        $tableName = 'test';

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $moduleManager = new ModuleManager();

        $generator = new RepositoryGenerator($db, $moduleManager);
        $generator->tableName = $tableName;

        $actualTableName = $generator->getTableName();

        $this->assertEquals($tableName, $actualTableName);
    }

    public function testGetModuleName()
    {
        $moduleName = 'oft-test';
        $module = 'Oft\Test\Mock\Module';

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with 1 module
        $moduleManager = new ModuleManager();
        $moduleManager->addModule($module);

        $generator = new RepositoryGenerator($db, $moduleManager);
        $generator->moduleName = $moduleName;

        $this->assertEquals($moduleName, $generator->getModuleName());
    }

    public function testGetModuleNameDefault()
    {
        $moduleName = 'oft-test';
        $module = 'Oft\Test\Mock\Module';

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with 1 default module
        $moduleManager = new ModuleManager();
        $moduleManager->addModule($module, true);

        $generator = new RepositoryGenerator($db, $moduleManager);

        $this->assertEquals($moduleName, $generator->getModuleName());
    }

    /**
     * @expectedException DomainException
     */
    public function testGetModuleNameException()
    {
        $moduleName = 'oft-test';

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with no module defined
        $moduleManager = new ModuleManager();

        $generator = new RepositoryGenerator($db, $moduleManager);
        $generator->moduleName = $moduleName;

        $this->assertEquals($moduleName, $generator->getModuleName());
    }

    public function testGetClassNameAndTestClassName()
    {
        $tableName = 'test_table';
        $expectedClassName = 'TestTableRepository';
        $expectedBaseClassName = 'TestTableBaseRepository';
        $expectedTestClassName = 'TestTableRepositoryTest';

        $db = Mockery::mock('Doctrine\DBAL\Connection');
        $moduleManager = new ModuleManager();

        $generator = new RepositoryGenerator($db, $moduleManager);
        $generator->tableName = $tableName;

        $this->assertEquals($expectedClassName, $generator->getClassName(false));
        $this->assertEquals($expectedBaseClassName, $generator->getClassName(true));
        $this->assertEquals($expectedTestClassName, $generator->getTestClassName());
    }

    public function testGenerate()
    {
        $tableName = 'test_table';

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with 1 default module
        $moduleManager = new ModuleManager();
        $moduleManager->addModule('Oft\Test\Mock\Module', true);

        RepositoryGenerator::$tableDescClassName = 'Oft\Test\Install\Generate\Repository\TableDescriptionMockForRepositoryGenerator';

        $generator = new RepositoryGenerator($db, $moduleManager);
        $generator->tableName = $tableName;
        $generator->generate();

        $files = $generator->getFiles();

        $this->assertCount(3, $files);
        foreach ($files as $file) {
            $this->assertInstanceOf('Oft\Install\Generate\File', $file);
        }
    }

}
