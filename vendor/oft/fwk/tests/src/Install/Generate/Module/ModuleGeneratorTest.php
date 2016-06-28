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

namespace Oft\Test\Install\Generate\Module;

use InvalidArgumentException;
use Oft\Install\Generate\Module\ModuleGenerator;
use Oft\Module\ModuleManager;
use PHPUnit_Framework_TestCase;

class ModuleGeneratorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetModuleNameExceptionBecauseNull()
    {
        $moduleName = null;

        $moduleManager = new ModuleManager();

        $generator = new ModuleGenerator($moduleManager);
        $generator->moduleName = $moduleName;

        $generator->getModuleName();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetModuleNameExceptionBecauseAlreadyExists()
    {
        $moduleName = 'oft-test';
        $module = 'Oft\Test\Mock\Module';

        // ModuleManager with 1 default module
        $moduleManager = new ModuleManager();
        $moduleManager->addModule($module, true);

        $generator = new ModuleGenerator($moduleManager);
        $generator->moduleName = $moduleName;

        $generator->getModuleName();
    }

    public function testGetModuleName()
    {
        $moduleName = 'Oft\Test';
        $expectedModuleName = 'oft-test';

        // ModuleManager with 1 default module
        $moduleManager = new ModuleManager();

        $generator = new ModuleGenerator($moduleManager);
        $generator->moduleName = $moduleName;

        $actualModuleName = $generator->getModuleName();

        $this->assertEquals($expectedModuleName, $actualModuleName);
    }

    public function testGetModuleNamespace()
    {
        $moduleName = 'Oft\Test';
        $expectedModuleNamespace = 'OftTest';

        // ModuleManager with 1 default module
        $moduleManager = new ModuleManager();

        $generator = new ModuleGenerator($moduleManager);
        $generator->moduleName = $moduleName;

        $actualModuleNamespace = $generator->getModuleNamespace();

        $this->assertEquals($expectedModuleNamespace, $actualModuleNamespace);
    }

    public function testGetModuleRoot()
    {
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', '');
        }

        $moduleName = 'Oft\Test';
        $expectedModuleName = 'oft-test';
        $expectedModuleRoot = APP_ROOT . '/modules/' . $expectedModuleName;

        // ModuleManager with 1 default module
        $moduleManager = new ModuleManager();

        $generator = new ModuleGenerator($moduleManager);
        $generator->moduleName = $moduleName;

        $actualModuleRoot = $generator->getModuleRoot();

        $this->assertEquals($expectedModuleRoot, $actualModuleRoot);
    }

    public function testGenerate()
    {
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', '');
        }

        $moduleName = 'Oft\Test';

        $moduleManager = new ModuleManager();

        $generator = new ModuleGenerator($moduleManager);
        $generator->moduleName = $moduleName;

        $generator->generate();

        $files = $generator->getFiles();

        $this->assertCount(8, $files);
        foreach ($files as $file) {
            $this->assertInstanceOf('Oft\Install\Generate\File', $file);
        }
    }

}
