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

namespace Oft\Test\Install\Generate\Admin;

use DomainException;
use InvalidArgumentException;
use Oft\Install\Generate\Admin\AdminGenerator;
use Oft\Module\ModuleManager;
use PHPUnit_Framework_TestCase;

class AdminGeneratorTest extends PHPUnit_Framework_TestCase
{

    public function testGetModuleNameDefault()
    {
        $moduleName = 'oft-test';
        $module = 'Oft\Test\Mock\Module';

        // ModuleManager with 1 default module
        $moduleManager = new ModuleManager();
        $moduleManager->addModule($module, true);

        $generator = new AdminGenerator($moduleManager);

        $this->assertEquals($moduleName, $generator->getModuleName());
    }

    /**
     * @expectedException DomainException
     */
    public function testGetModuleNameException()
    {
        $moduleName = 'oft-test';

        // ModuleManager with no module defined
        $moduleManager = new ModuleManager();

        $generator = new AdminGenerator($moduleManager);
        $generator->moduleName = $moduleName;

        $this->assertEquals($moduleName, $generator->getModuleName());
    }

    public function testGetUsername()
    {
        $username = 'ABCD1234';

        $moduleManager = new ModuleManager();
        $generator = new AdminGenerator($moduleManager);

        $generator->username = $username;

        $this->assertEquals($username, $generator->getUsername());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetUsernameException()
    {
        $username = 'invalid-username';

        $moduleManager = new ModuleManager();
        $generator = new AdminGenerator($moduleManager);

        $generator->username = $username;

        $this->assertEquals($username, $generator->getUsername());
    }

    public function testGetPassword()
    {
        $password = '12345678';

        $moduleManager = new ModuleManager();
        $generator = new AdminGenerator($moduleManager);

        $generator->password = $password;

        $this->assertEquals($password, $generator->getPassword());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetPasswordException()
    {
        $password = '1';

        $moduleManager = new ModuleManager();
        $generator = new AdminGenerator($moduleManager);

        $generator->password = $password;

        $this->assertEquals($password, $generator->getPassword());
    }

    public function testGetMigrationFile()
    {
        $username = 'ABCD1234';
        $password = '12345678';

        $moduleName = 'oft-test';
        $module = 'Oft\Test\Mock\Module';

        // ModuleManager with 1 default module
        $moduleManager = new ModuleManager();
        $moduleManager->addModule($module, true);

        $generator = new AdminGenerator($moduleManager);

        $generator->username = $username;
        $generator->password = $password;

        $generator->generate();

        $files = $generator->getFiles();

        $this->assertCount(1, $files);
        $this->assertInstanceOf('Oft\Install\Generate\File', $files[0]);
    }

}
