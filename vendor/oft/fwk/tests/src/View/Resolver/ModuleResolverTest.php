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

namespace Oft\Test\View\Resolver;

use Mockery;
use Oft\View\Resolver\ModuleResolver;
use PHPUnit_Framework_TestCase;

class ModuleResolverTest extends PHPUnit_Framework_TestCase
{

    public function testResolve()
    {
        $module = Mockery::mock('\Oft\Module\ModuleInterface');
        $module->shouldReceive('getDir')
            ->with('views')
            ->andReturn('/path/to/module/views');
        
        $moduleManager = Mockery::mock('\Oft\Module\ModuleManager');
        $moduleManager->shouldReceive('getModule')
            ->with('m')
            ->once()
            ->andReturn($module);
        
        $resolver = new ModuleResolver($moduleManager);
        
        $this->assertSame('/path/to/module/views/c/a.phtml', $resolver->resolve('m/c/a'));
    }
    
    public function testResolveThrowsExceptionWithInvalidTemplate()
    {
        $this->setExpectedException('\RuntimeException');
        
        $moduleManager = Mockery::mock('\Oft\Module\ModuleManager');
        
        $resolver = new ModuleResolver($moduleManager);
        
        $resolver->resolve('a');
    }
    
    

    public function testResolveTrimSuffix()
    {
        $module = Mockery::mock('\Oft\Module\ModuleInterface');
        $module->shouldReceive('getDir')
            ->with('views')
            ->andReturn('/path/to/module/views');
        
        $moduleManager = Mockery::mock('\Oft\Module\ModuleManager');
        $moduleManager->shouldReceive('getModule')
            ->with('m')
            ->once()
            ->andReturn($module);
        
        $resolver = new ModuleResolver($moduleManager, '.ext');
        
        $this->assertSame('/path/to/module/views/c/a.ext', $resolver->resolve('m/c/a'));
    }
    
    
    public function testResolveTrimViewDir()
    {
        $module = Mockery::mock('\Oft\Module\ModuleInterface');
        $module->shouldReceive('getDir')
            ->with('views')
            ->andReturn('/path/to/module/views\\');
        
        $moduleManager = Mockery::mock('\Oft\Module\ModuleManager');
        $moduleManager->shouldReceive('getModule')
            ->with('m')
            ->once()
            ->andReturn($module);
        
        $resolver = new ModuleResolver($moduleManager);
        
        $this->assertSame('/path/to/module/views/c/a.phtml', $resolver->resolve('m/c/a'));
    }
}
