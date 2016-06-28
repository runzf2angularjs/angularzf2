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

namespace Oft\Test\Install\Generate\Crud;

use DomainException;
use InvalidArgumentException;
use Mockery;
use Oft\Install\Generate\Crud\CrudGenerator;
use Oft\Install\Tools\MySql\TableDescription;
use Oft\Module\ModuleManager;
use PHPUnit_Framework_TestCase;

class RepositoryMockForCrudGenerator
{

    public static $table = 'repository_mock';
    public static $primary = array('primary_key');
    public static $metadata = array(
        'primary_key' => array(
            'type' => 'int',
            'default' => NULL,
            'nullable' => false,
            'identity' => true,
            'foreign_key_table' => NULL,
            'foreign_key_column' => NULL,
            'unsigned' => true,
        ),
    );

}

class TableDescriptionMockForCrudGenerator extends TableDescription
{
    public function __construct()
    {
        $this->columns = array('primary' => array('type' => 'int'));
        $this->primary = array('primary');
        $this->formElements = array(
            'primary' => array(
                'type' => 'Zend\Form\Element\Hidden',
                'required' => false,
                'input_filter' => array(
                    'validators' => array(),
                    'filters' => array(),
                ),
            ),
        );
    }
}

class CrudGeneratorTest extends PHPUnit_Framework_TestCase
{

    public function testGetModuleNameDefault()
    {
        $moduleName = 'oft-test';
        $module = 'Oft\Test\Mock\Module';

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with 1 default module
        $moduleManager = new ModuleManager();
        $moduleManager->addModule($module, true);

        $generator = new CrudGenerator($db, $moduleManager);

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

        $generator = new CrudGenerator($db, $moduleManager);
        $generator->moduleName = $moduleName;

        $this->assertEquals($moduleName, $generator->getModuleName());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetRepositoryClassNameExceptionNull()
    {
        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with no module defined
        $moduleManager = new ModuleManager();

        $generator = new CrudGenerator($db, $moduleManager);

        $generator->getRepositoryClassName();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetRepositoryClassNameExceptionInvalid()
    {
        $repositoryClassName = 'TestRepository';

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with no module defined
        $moduleManager = new ModuleManager();

        $generator = new CrudGenerator($db, $moduleManager);
        $generator->repositoryClassName = $repositoryClassName;

        $generator->getRepositoryClassName();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetRepositoryClassNameExceptionNotExistingClass()
    {
        $repositoryClassName = 'App\Repository\TestRepository';

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with no module defined
        $moduleManager = new ModuleManager();

        $generator = new CrudGenerator($db, $moduleManager);
        $generator->repositoryClassName = $repositoryClassName;

        $generator->getRepositoryClassName();
    }

    public function testGetRepositoryClassName()
    {
        $repositoryClassName = 'RepositoryMockForCrudGenerator';
        $fullRepositoryClassName = 'Oft\Test\Install\Generate\Crud\\' . $repositoryClassName;

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with no module defined
        $moduleManager = new ModuleManager();

        $generator = new CrudGenerator($db, $moduleManager);
        $generator->repositoryClassName = $fullRepositoryClassName;

        $fullClassname = $generator->getRepositoryClassName(true);
        $classname = $generator->getRepositoryClassName(false);

        $this->assertEquals($fullRepositoryClassName, $fullClassname);
        $this->assertEquals($repositoryClassName, $classname);
    }

    public function testGetClassName()
    {
        $repositoryClassName = 'RepositoryMockForCrudGenerator';
        $fullRepositoryClassName = 'Oft\Test\Install\Generate\Crud\\' . $repositoryClassName;

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with no module defined
        $moduleManager = new ModuleManager();

        $generator = new CrudGenerator($db, $moduleManager);
        $generator->repositoryClassName = $fullRepositoryClassName;

        $className = $generator->getClassName();
        $classNameWithSuffix = $generator->getClassName('Repository');

        $this->assertEquals('RepositoryMock', $className); // Cf. RepositoryMockForCrudGenerator::$table
        $this->assertEquals('RepositoryMockRepository', $classNameWithSuffix); // Cf. RepositoryMockForCrudGenerator::$table
    }
    
        public function testGetClassNameWithInitialValue()
    {
        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with no module defined
        $moduleManager = new ModuleManager();

        $generator = new CrudGenerator($db, $moduleManager);
        $generator->className = 'repository';

        $className = $generator->getClassName();
        $classNameWithSuffix = $generator->getClassName('Repository');

        $this->assertEquals('Repository', $className); // Cf. RepositoryMockForCrudGenerator::$table
        $this->assertEquals('RepositoryRepository', $classNameWithSuffix); // Cf. RepositoryMockForCrudGenerator::$table
    }

    public function testGetTestClassName()
    {
        $repositoryClassName = 'RepositoryMockForCrudGenerator';
        $fullRepositoryClassName = 'Oft\Test\Install\Generate\Crud\\' . $repositoryClassName;

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with no module defined
        $moduleManager = new ModuleManager();

        $generator = new CrudGenerator($db, $moduleManager);
        $generator->repositoryClassName = $fullRepositoryClassName;

        $testClassName = $generator->getTestClassName();

        $this->assertStringEndsWith('Test', $testClassName);
    }

    public function testGenerate()
    {
        $repositoryClassName = 'RepositoryMockForCrudGenerator';
        $fullRepositoryClassName = 'Oft\Test\Install\Generate\Crud\\' . $repositoryClassName;

        // DB mock
        $db = Mockery::mock('Doctrine\DBAL\Connection');

        // ModuleManager with 1 default module
        $moduleManager = new ModuleManager();
        $moduleManager->addModule('Oft\Test\Mock\Module', true);

        CrudGenerator::$tableDescClassName = 'Oft\Test\Install\Generate\Crud\TableDescriptionMockForCrudGenerator';
        
        $generator = new CrudGenerator($db, $moduleManager);
        $generator->repositoryClassName = $fullRepositoryClassName;
        $generator->generate();
        
        $files = $generator->getFiles();

        $this->assertCount(7, $files);
        foreach ($files as $file) {
            $this->assertInstanceOf('Oft\Install\Generate\File', $file);
        }
    }

}
